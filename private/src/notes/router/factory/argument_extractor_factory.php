<?php
namespace notes\router\factory;

class argument_extractor_factory implements \srouter\interfaces\argument_extractor_factory {

	public function build_argument_extractor(
		string $_key
	):\srouter\interfaces\argument_extractor {

		return new \notes\router\argument_extractor();
	}
}