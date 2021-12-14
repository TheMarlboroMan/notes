<?php
declare(strict_types=1);
namespace srouter;

/**
*the router. this cass will need to be instatiated and fed a series of factories
*at build time. When built, the only two things can it can do is recieve
*more error handlers or start the routing process via "route", which will
*culminate in an http_response being returned to the calling scope.
*/

class router {

	use \srouter\traits\strict;

	private const log_module="srouter";

	public function __construct(
		\log\logger_interface               $_logger,
		interfaces\request_factory          $_request_factory,
		interfaces\uri_transformer          $_uri_transformer,
		interfaces\path_mapper              $_path_mapper,
		interfaces\in_transformer_factory   $_in_transformer_factory,
		interfaces\authorizer_factory       $_authorizer_factory,
		interfaces\argument_extractor_factory $_argument_extractor_factory,
		interfaces\parameter_mapper_factory  $_parameter_mapper_factory,
		interfaces\controller_factory       $_controller_factory,
		interfaces\out_transformer_factory  $_out_transformer_factory,
		interfaces\exception_handler_factory $_exception_handler_factory
	) {

		$this->logger=$_logger;
		$this->path_mapper=$_path_mapper;
		$this->uri_transformer=$_uri_transformer;
		$this->request_factory=$_request_factory;
		$this->in_transformer_factory=$_in_transformer_factory;
		$this->authorizer_factory=$_authorizer_factory;
		$this->argument_extractor_factory=$_argument_extractor_factory;
		$this->parameter_mapper_factory=$_parameter_mapper_factory;
		$this->controller_factory=$_controller_factory;
		$this->out_transformer_factory=$_out_transformer_factory;
		$this->exception_handler_factory=$_exception_handler_factory;
		$this->add_exception_handler(new \srouter\default_exception_handler($this->logger));
	}

/**
*adds an exception handler that will be executed before the defaults, but after any route
*specific ones.
*/

	public function							add_exception_handler(
			\srouter\interfaces\exception_handler $_e
	) :\srouter\router {

		$this->exception_handlers[]=$_e;
		return $this;
	}

/**
*starts the whole routing process and returns the resulting http response.
*/
	public function                         route() : \srouter\http_response {

		try {

			$this->logger->info("will build the request", self::log_module);
			$request=$this->request_factory->build_request();

			$transformed_uri=$this->uri_transformer->transform($request->get_uri());
			$this->logger->info($request->get_uri()." becomes $transformed_uri", self::log_module);

			$this->logger->info("will map ".$request->get_method()." : $transformed_uri to a route", self::log_module);
			$route=$this->path_mapper->map(
				$request->get_method(),
				$transformed_uri
			);

			if(null===$route) {

				$this->logger->notice("failed to map, will throw", self::log_module);
				throw new \srouter\exception\not_found();
			}

			$this->logger->info("will attempt to perform input transformation", self::log_module);
			$request=$this->transform_request($route->get_in_transformer(), $request);

			if(count($route->get_authorizers())) {

				$this->logger->info("will perform authorization", self::log_module);
				$this->authorize($route->get_authorizers(), $request, $route);
			}

			$arguments=[];
			if(null!==$route->get_argument_extractor()) {

				$this->logger->notice("will extract arguments with '".$route->get_argument_extractor()."'", self::log_module);
				$arguments=$this->extract_arguments(
					$route->get_argument_extractor(),
					$route->get_parameters(),
					$request,
					$route->get_uri_arguments()
				);
			}

			//build controller...
			$controller=$this->build_controller($route->get_classname());
			if(!method_exists($controller, $route->get_methodname())) {

				$this->logger->warning("could not find method '".$route->get_methodname()."' in '".$route->get_classname()."'", self::log_module);
				throw new \srouter\exception\bad_dependency("method does not exist for controller");
			}

			//finally, sort parameters as they appear on the controller....
			$sorted_arguments=$this->sort_and_map_parameters($controller, $route, $arguments);

			//execute...
			$methodname=$route->get_methodname();
			$this->logger->info("will perform execution now...", self::log_module);
			$result=$controller->$methodname(...$sorted_arguments);

			if(! ($result instanceof controller_response)) {

				$this->logger->warning("returned value is not a controller_response!", self::log_module);
				throw new \srouter\exception\bad_response("result of '".$route->get_methodname()."' in '".$route->get_classname()."' is not a controller_response");
			}

			//transform output.
			$this->logger->info("will transform and return output...", self::log_module);
			return $this->transform_out($result, $route->get_out_transformer());
		}
		catch(\Exception $e) {

			return $this->handle_error($e, $request, $route);
		}
		catch(\Error $e) {

			return $this->handle_error($e, $request, $route);
		}
	}

/**
*attempts to sort and map the parameters declared in the route to the ones
*present in the PHP controller.
*/
	private function sort_and_map_parameters(
		$controller,
		\srouter\route $_route,
		array $_arguments
	) {

		$mapper_name=$_route->get_parameter_mapper();
		if(null!==$mapper_name) {

			$mapper=$this->parameter_mapper_factory->build_parameter_mapper($mapper_name);
			if(null===$mapper) {

				throw new \srouter\exception\bad_dependency("cannot build parameter mapper '$mapper_name'");
			}

			//remap the argument names to the expected ones...
			$mapped_arguments=[];
			foreach($_arguments as $argname => $argvalue) {

				$mapped_arguments[$mapper->map($argname)]=$argvalue;
			}
		}
		else {

			//No parameter mapping specified, just map them as-is.
			$mapped_arguments=$_arguments;
		}

		$reflector=new \ReflectionMethod($controller, $_route->get_methodname());
		$result=[];
		foreach($reflector->getParameters() as $parameter) {

			if(array_key_exists($parameter->getName(), $mapped_arguments)) {

				$result[]=$mapped_arguments[$parameter->getName()];
				continue;
			}

			$this->logger->info("cannot find argument for '".$parameter->getName()."'", self::log_module);
			throw new \srouter\exception\argument_mapping("cannot find argument for parameter '".$parameter->getName()."'");
		}

		if(count($reflector->getParameters()) !== count($result)) {

			throw new \srouter\exception\bad_dependency("no arguments could be extracted when the prototype demands them");
		}

		return $result;
	}

/**
*attempts to build the controller that matches the request.
*/
	private function                        build_controller(
		string $_classname
	) {

		$controller=$this->controller_factory->build_controller($_classname);

		if(null===$controller) {

			throw new \srouter\exception\bad_dependency("could not build controller '$_classname'");
		}

		if(!is_object($controller)) {

			throw new \srouter\exception\bad_dependency("controller '$_classname' must be an object");
		}

		return $controller;
	}

/**
*attempts to transform the request according to the current route.
*/
	private function                        transform_request(
		?string $_key,
		interfaces\request $_request
	) : interfaces\request {

		if(null===$_key) {

			$this->logger->info("no input transformation specified, skipping...", self::log_module);
			return $_request;
		}

		$factory=$this->in_transformer_factory->build_in_transformer($_key);
		if(null===$factory) {

			throw new \srouter\exception\bad_dependency("fatal error, cannot build request transformer with '$_key'");
		}

		return $factory->transform($_request);
	}

/**
*attempts to authorize the current request. If authorization fails, the
*"unauthorized" exception will be thrown. This exception can be caught by
*custom handlers of the generic one.
*/
	private function                        authorize(
		array $_authorizers,
		interfaces\request $_request,
		\srouter\route $_route
	) {

		foreach($_authorizers as $key) {

			$this->logger->info("will authorize using $key", self::log_module);
			$authorizer=$this->authorizer_factory->build_authorizer($key);

 			if(null===$authorizer) {

				throw new \srouter\exception\bad_dependency("fatal error, cannot build authorizer '$key'");
			}

			if(!$authorizer->authorize($_request, $_route)) {

				$this->logger->notice("$key authorization failed", self::log_module);
				throw new \srouter\exception\unauthorized("failed to authorize using $key");
			}
		}
	}

/**
*attempts to extract method arguments from the request according to the route
*definition.
*/
	private function extract_arguments(
		string $_argument_extractor_name,
		array $_arguments,
		interfaces\request $_request,
		array $_uri_arguments
	) {

		$extractor=$this->argument_extractor_factory->build_argument_extractor($_argument_extractor_name);
		if(null===$extractor) {

			throw new \srouter\exception\bad_dependency("cannot build argument extractor '$_argument_extractor_name'");
		}

		$result=[];

		foreach($_arguments as $argument) {

			$result[$argument->get_name()]=$extractor->extract(
				$argument,
				$_request,
				$_uri_arguments
			);
		}

		return $result;
	}

/**
*transforms the controller_response (what is returned by a controller) into
*an http_response according to the route specification.
*/
	private function transform_out(
		controller_response $_result,
		string $_out_transformer
	) : \srouter\http_response {

		$transformer=$this->out_transformer_factory->build_out_transformer($_out_transformer);
		if(null===$transformer) {

			throw new \srouter\exception\bad_dependency("cannot build out transformer '$_out_transformer''");
		}

		return $transformer->transform($_result);
	}

/**
*handles the error and ultimately returns an error response to the browser.
*let us assume that there will always be a request. So far.
*/
	private function handle_error(
		$_exception,
		\srouter\interfaces\request $_request,
		?\srouter\route $_route
	) {

		if(null!==$_route) {

			//add route exception handlers...
			foreach($_route->get_exception_handlers() as $handler_name) {

				$handler=$this->exception_handler_factory->build_exception_handler($handler_name);
				if(null===$handler) {

					$this->logger->notice("did not build handler with name '$handler_name'", self::log_module);
					continue;
				}

				$this->add_exception_handler($handler);
			}
		}

		while(true) {

			//pop handlers until somebody responds. It is assumed that the default
			//one will always be there.
			$handler=array_pop($this->exception_handlers);

			$this->logger->info("attempting to handle '".get_class($_exception)."' with ".get_class($handler), self::log_module);

			if($_exception instanceof \Error) {

				$response=$handler->handle_error($_exception, $_request, $_route);
				if(null!==$response) {
					return $response;
				}
			}

			if($_exception instanceof \Exception) {

				$response=$handler->handle_exception($_exception, $_request, $_route);
				if(null!==$response) {
					return $response;
				}
			}
		}
	}

	private \log\logger_interface               $logger;
	private interfaces\path_mapper              $path_mapper;
	private interfaces\uri_transformer          $uri_transformer;
	private interfaces\request_factory          $request_factory;
	private interfaces\in_transformer_factory   $in_transformer_factory;
	private interfaces\authorizer_factory       $authorizer_factory;
	private interfaces\argument_extractor_factory $argument_extractor_factory;
	private interfaces\parameter_mapper_factory  $parameter_mapper_factory;
	private interfaces\controller_factory       $controller_factory;
	private interfaces\out_transformer_factory  $out_transformer_factory;
	private interfaces\exception_handler_factory $exception_handler_factory;
	private array                               $exception_handlers=[];
}
