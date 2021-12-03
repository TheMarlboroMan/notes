<?php
namespace notes\api\controller;

class controller {

	public function __construct(
		\notes\api\dependency_container $_dc
	) {

		$this->dc=$_dc;
	}

	protected \notes\api\dependency_container $dc;
}