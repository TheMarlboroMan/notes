<?php
namespace notes\api\controller;

class session extends controller {

	const log_module="session";

/**
*checks if the token that is posted exists and is vale.
*/
	public function post(
		$_token
	) : \srouter\controller_response {

		$entity_manager=$this->dc->get_entity_manager();
		$session=$entity_manager->fetch_one(
			\notes\entities\user_session::class,
			$entity_manager->get_fetch_builder()->str_equals_cs("token", $_token)
		);

		if(null===$session) {

			$this->dc->get_logger()->notice("token $_token not found", self::log_module);
			return new \srouter\controller_response(404, [], "session not found");
		}

		if(null!==$session->get_last_activity_at()) {

			$threshold=clone $session->get_last_activity_at();
			$threshold->modify("+1 hour");

			$now=new \DateTime();
			if($threshold < $now) {

				$this->dc->get_logger()->notice("token $_token expired", self::log_module);
				return new \srouter\controller_response(404, [], "session expired");
			}
		}

		return new \srouter\controller_response(200, [], "ok");
	}
}
