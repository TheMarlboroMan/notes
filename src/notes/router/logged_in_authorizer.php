<?php
namespace notes\router;

class logged_in_authorizer implements \srouter\interfaces\authorizer {

	private const log_module="logged_in_authorizer";

	public function __construct(
		\notes\api\dependency_container $_dc,
		\log\logger_interface $_logger,
		\sorm\entity_manager $_entity_manager
	) {

		$this->dc=$_dc;
		$this->logger=$_logger;
		$this->entity_manager=$_entity_manager;
	}

	public function authorize(
		\srouter\interfaces\request $_request,
		\srouter\route $_route
	) : bool {

		$this->logger->info("will attempt to authorize request as logged in", self::log_module);

		//does the request have the header?
		if(!$_request->has_header("notes-auth_token")) {

			$this->logger->info("missing auth header, aborting", self::log_module);
			return false;
		}

		//does the header represent an existing session?
		$req_token=$_request->get_header("notes-auth_token");
		$session=$this->entity_manager->fetch_one(
			\notes\entities\user_session::class,
			$this->entity_manager->get_fetch_builder()->str_equals_cs("token", $req_token)
		);

		if(null===$session) {

			$this->logger->info("no session by ".$req_token, self::log_module);
			return false;
		}

		//is the session expired? null is a new session, supposedly.
		if(null!==$session->get_last_activity_at()) {

			$threshold=clone $session->get_last_activity_at();
			$threshold->modify("+1 hour");

			$now=new \DateTime();
			if($threshold < $now) {

				$this->logger->info("session expired, with $req_token", self::log_module);
				return false;
			}
		}

		//give some love to the session.
		$this->logger->info("will update the session", self::log_module);
		$this->entity_manager->update(
			$session->set_last_activity_at(new \DateTime())
		);

		//set the values in the dependency container, to be used in any
		//controller later!
		$this->dc->set_user_session($session);

		return true;
	}

	private \notes\api\dependency_container $dc;
	private \log\logger_interface           $logger;
	private \sorm\entity_manager            $entity_manager;
}
