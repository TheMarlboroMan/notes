<?php
require_once("src/autoload.php");

try {

	$config=new \notes\api\config(__DIR__."/conf/config.json");


	$router_log_file=$config->get_router_log_file();
	$router_logger=!strlen($router_log_file)
		? new \log\void_logger()
		: new \log\file_logger(new \log\default_formatter(), $router_log_file);

	$dependency_container=new \notes\api\dependency_container($config);

	//Setting up the router...
	$request_factory=new \notes\router\factory\request_factory();
	$uri_transformer=new \notes\router\uri_transformer($config->get_uri_transformer_lead());
	$path_mapper=new \notes\router\path_mapper(__DIR__."/conf/routes.json");
	$in_transformer_factory=new \notes\router\factory\in_transformer_factory();
	$authorizer_factory=new \notes\router\factory\authorizer_factory($dependency_container);
	$argument_extractor_factory=new \notes\router\factory\argument_extractor_factory();
	$parameter_mapper_factory=new \notes\router\factory\parameter_mapper_factory();
	$controller_factory=new \notes\router\factory\controller_factory($dependency_container);
	$out_transformer_factory=new \notes\router\factory\out_transformer_factory();

	//not really needed at all.
	$exception_handler_factory=new \notes\router\factory\exception_handler_factory(
		$dependency_container->get_logger(),
		$config->is_verbose_errors()
	);

	$router=new \srouter\router(
		$router_logger,
		$request_factory,
		$uri_transformer,
		$path_mapper,
		$in_transformer_factory,
		$authorizer_factory,
		$argument_extractor_factory,
		$parameter_mapper_factory,
		$controller_factory,
		$out_transformer_factory,
		$exception_handler_factory
	);

	$router->add_exception_handler(
		new \notes\router\exception_handler(
			$dependency_container->get_logger(),
			$config->is_verbose_errors()
		)
	);

	$router->route()->out();
}
catch(\Exception $e) {

	//TODO:yeah, not really
	var_dump($e);
	die();
}
catch(\Error $e) {

	//TODO:yeah, not really
	var_dump($e);
	die();
}