<?php
namespace notes\router;

class underscore_parameter_mapper implements \srouter\interfaces\parameter_mapper {

	public function map(string $_name) : string {

		return "_$_name";
	}
}
