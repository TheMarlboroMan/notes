<?php
namespace sorm\traits;

trait strict {

	function __get($_key) {

		throw new \sorm\exception\internal("unknown property $_key in ".get_class($this));
	}

	function __set($_key, $_val) {

		throw new \sorm\exception\internal("unknown property $_key in ".get_class($this));
	}
}
