<?php
namespace notes\router;

class user_owns_note_authorizer implements \srouter\interfaces\authorizer {

	private const log_module="user_owns_note_authorizer";

	public function __construct(
		\notes\api\dependency_container $_dc,
		\log\logger_interface $_logger,
		\sorm\entity_manager $_entity_manager
	) {

		//we actually need the DC here, the user cannot be injected at this
		//moment in the application!
		$this->dc=$_dc;
		$this->logger=$_logger;
		$this->entity_manager=$_entity_manager;
	}

	public function authorize(
		\srouter\interfaces\request $_request,
		\srouter\route $_route
	) : bool {

		//TODO: Extract note id from request...
		$arg=array_filter(
			$_route->get_uri_arguments(),
			function(
				\srouter\uri_argument $_arg
			) {
				return $_arg->get_name()==="id";
			}
		);

		if(1!==count($arg)) {

			$this->logger->notice("missing id in request, rejecting", self::log_module);
			return false;
		}

		$arg=array_shift($arg);
		$note=$this->entity_manager->fetch_by_id(
			\notes\entities\note::class,
			$arg->get_value()
		);

		if(null===$note) {

			$this->logger->notice("note with id ".$arg->get_value()." does not exist, rejecting", self::log_module);
			return false;
		}

		if($note->get_user_id() !== $this->dc->get_logged_in_user()->get_id()) {

			$this->logger->notice("note with id ".$arg->get_value()." does not belong to current user, rejecting", self::log_module);
			return false;
		}

		return true;
	}

	private \notes\api\dependency_container $dc;
	private \log\logger_interface           $logger;
	private \sorm\entity_manager            $entity_manager;
}