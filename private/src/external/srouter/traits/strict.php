<?php
declare(strict_types=1);
namespace srouter\traits;

trait strict {

	function __get($_key) {

		throw new \srouter\exception\internal("unknown property $_key in ".get_class($this));
	}

	function __set($_key, $_val) {

		throw new \srouter\exception\internal("unknown property $_key in ".get_class($this));
	}
}
