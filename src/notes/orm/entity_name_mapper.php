<?php
namespace notes\orm;

class entity_name_mapper implements \sorm\interfaces\entity_name_mapper {

	public function map_name(
		string $_name
	) : string {

		return str_replace("::", "\\", $_name);
	}
}