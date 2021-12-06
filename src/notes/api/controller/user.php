<?php
namespace notes\api\controller;

class user extends \notes\api\controller\controller {

	private const log_module="user";

	public function get() :\srouter\controller_response {

		//retrieve current user...
		$current_user=$this->dc->get_logged_in_user();
		return new \srouter\controller_response(
			\srouter\http_response::code_200_ok,
			[],
			$current_user
		);
	}

	public function patch(
		?string $_username=null,
		?string $_pass=null
	) :\srouter\controller_response {

		$changes=0;

		$current_user=$this->dc->get_logged_in_user();
		$entity_manager=$this->dc->get_entity_manager();
		if(null!==$_username) {

			if(!strlen($_username)) {

				return new \srouter\controller_response(
					400,
					[],
					"username cannot be empty"
				);
			}

			$another_user=$entity_manager->fetch_one(
				\notes\entities\user::class,
				$entity_manager->get_fetch_builder()->str_equals_cs("username", $_username)
			);

			if(null!==$another_user) {

				return new \srouter\controller_response(
					400,
					[],
					"cannot choose that username"
				);
			}

			$current_user->set_username($_username);
			++$changes;
		}

		if(null!==$_pass) {

			if(!strlen($_pass)) {

				return new \srouter\controller_response(
					400,
					[],
					"password cannot be empty"
				);
			}

			$current_user->set_pass(
				password_hash($_pass, PASSWORD_DEFAULT)
			);
			++$changes;
		}

		$this->dc->get_logger()->info("user with id ".$current_user->get_id()." will commit $changes changes", self::log_module);
		if($changes) {

			$this->dc->get_entity_manager()->update($current_user);
		}

		return new \srouter\controller_response(
			200,
			[],
			"ok"
		);
	}
}