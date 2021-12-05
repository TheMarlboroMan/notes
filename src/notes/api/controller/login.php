<?php
namespace notes\api\controller;

class login extends controller {

	private const log_module="login";

	public function post(
		string $_username,
		string $_pass
	) : \srouter\controller_response {

		$em=$this->dc->get_entity_manager();
		$fb=$em->get_fetch_builder();

		//locate the user...
		$user=$em->fetch_one(
			\notes\entities\user::class,
			$fb->str_equals_cs("username", $_username)
		);

		if(null===$user) {

			$this->dc->get_logger()->info("invalid username $_username", self::log_module);

			return new \srouter\controller_response(
				\srouter\http_response::code_400_bad_request,
				[],
				"invalid username or pass"
			);
		}

		if(!password_verify($_pass, $user->get_pass())) {

			$this->dc->get_logger()->info("invalid password for username $_username", self::log_module);

			return new \srouter\controller_response(
				\srouter\http_response::code_400_bad_request,
				[],
				"invalid username or pass"
			);
		}

		$token=base64_encode(random_bytes(64));
		$em->create->user_session(
			$em->build(\notes\entities\user_session::class)
				->set_user_id($user->get_id())
				->set_create_at(new \DateTime())
				->set_last_activity_at(null)
				->set_token($token)
		);

		$em->update(
			$user->set_last_login_at($user_session->get_created_at())
		);

		$this->dc->get_logger()->info("user $_username has logged in", self::log_module);

		return new \srouter\controller_response(
			\srouter\http_response::code_200_ok,
			[
				new \srouter\http_response_header("notes-auth-token", $token)
			],
			"ok"
		);
	}
}