<?php
namespace notes\router\factory;

class parameter_mapper_factory implements \srouter\interfaces\parameter_mapper_factory {

	public function build_parameter_mapper(
		string $_name
	):\srouter\interfaces\parameter_mapper {

		return new \notes\router\underscore_parameter_mapper();
	}
}