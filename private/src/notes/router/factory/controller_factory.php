<?php
namespace notes\router\factory;

class controller_factory implements \srouter\interfaces\controller_factory {

	public function __construct(
		\notes\api\dependency_container $_dc
	) {

		$this->dc=$_dc;
	}

	public function build_controller(string $_classname) {

		$classname=str_replace("::", "\\", $_classname);
		if(!class_exists($classname)) {

			return null;
		}

		return new $classname($this->dc);
	}

	private \notes\api\dependency_container $dc;
}