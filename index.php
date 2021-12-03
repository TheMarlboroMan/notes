<?php
require_once("src/autoload.php");

try {

	$config=new \notes\api\config(
		"/notes/"
	);

	$logger=new \log\out_logger(
		new \log\default_formatter()
	);

	$dependency_container=new \notes\api\dependency_container();

	$request_factory=new \notes\router\factory\request_factory();
	$uri_transformer=new \notes\router\uri_transformer($config->get_uri_transformer_lead());
	$path_mapper=new \notes\router\path_mapper(__DIR__."/conf/routes.json");
	$in_transformer_factory=new \notes\router\factory\in_transformer_factory();
	$authorizer_factory=new \notes\router\factory\authorizer_factory($dependency_container);
	$argument_extractor_factory=new \notes\router\factory\argument_extractor_factory();
	$parameter_mapper_factory=new \notes\router\factory\parameter_mapper_factory();
	$controller_factory=new \notes\router\factory\controller_factory($dependency_container);
	$out_transformer_factory=new \notes\router\factory\out_transformer_factory();
	$exception_handler_factory=new \notes\router\factory\exception_handler_factory();

	$router=new \srouter\router(
		$logger,
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

	$router->route()->out();
}
catch(\Exception $e) {

	var_dump($e);
	die();
}
catch(\Error $e) {

	var_dump($e);
	die();
}