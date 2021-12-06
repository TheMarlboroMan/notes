<?php
namespace notes\router\factory;

class authorizer_factory implements \srouter\interfaces\authorizer_factory {

	public function __construct(
		\notes\api\dependency_container $_dc
	) {

		$this->dc=$_dc;
	}

	public function build_authorizer(
		string $_key
	):?\srouter\interfaces\authorizer {

		switch($_key) {
			case "logged_in":
				return new \notes\router\logged_in_authorizer(
					$this->dc,
					$this->dc->get_logger(),
					$this->dc->get_entity_manager()
				);
			case "user_owns_note":
				return new \notes\router\user_owns_note_authorizer();
		}

		return null;
	}

	private \notes\api\dependency_container $dc;
}