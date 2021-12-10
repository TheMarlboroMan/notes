<?php
namespace notes\api\controller;

class logout extends controller {

	private const log_module="logout";

	public function post(
	) : \srouter\controller_response {

		$em=$this->dc->get_entity_manager();
		$user=$this->dc->get_logged_in_user();
		$em->delete($this->dc->get_user_session());

		$this->dc->get_logger()->info("user '".$user->get_username()."' logged out", self::log_module);

		return new \srouter\controller_response(
			\srouter\http_response::code_200_ok,
			[],
			"ok"
		);
	}
}
