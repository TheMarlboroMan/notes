<?php
namespace notes\orm\factory;

class entity_factory implements \sorm\interfaces\entity_factory {

	public function build_entity(
		string $_entity_name
	) :\sorm\interfaces\entity {

		return new $_entity_name();
	}
}