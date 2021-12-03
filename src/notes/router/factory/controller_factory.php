<?php
namespace notes\router\factory;

class controller_factory implements \srouter\interfaces\controller_factory {

	public function __construct(
		\notes\api\dependency_container $_dc
	) {

		$this->dependency_container=$_dc;
	}

	public function build_controller(string $_classname) {

		return new $_classname($this->dc);
	}

	private \notes\api\dependency_container $dc;
}