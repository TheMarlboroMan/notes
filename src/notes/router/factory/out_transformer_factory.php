<?php
namespace notes\router\factory;

class out_transformer_factory implements \srouter\interfaces\out_transformer_factory {

	public function build_out_transformer(
		string $_name
	):\srouter\interfaces\out_transformer {

		switch($_name) {

			case "json":
				return new \notes\router\json_out_transformer();
		}

		return null;
	}
}