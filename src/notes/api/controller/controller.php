<?php
namespace notes\api\controller;

class controller {

	public function __construct(
		\api\dependency_container $_dc
	) {

		$this->dc=$_dc;
	}

	protected \api\dependency_container $dc;
}