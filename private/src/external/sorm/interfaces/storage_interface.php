<?php
declare(strict_types=1);
namespace sorm\interfaces;

/**
*defines a class that is able to interact with a storage layer.
*/

interface storage_interface {

/**
*must retrieve records from the storage layer. Records must reside in a fetch
*collection.
*/
	public function fetch(
		\sorm\internal\entity_definition $_def,
		\sorm\internal\entity_inflator $_inflator,
		?\sorm\interfaces\value_mapper_factory $_value_mapper_factory,
		\sorm\interfaces\fetch_node $_criteria,
		?\sorm\internal\order_by $_order=null,
		?\sorm\internal\limit_offset $_offset=null
	) : \sorm\interfaces\fetch_collection;

/**
*must create the entity defined in the payload and return its primary key,
*of any type. Must throw if the entity cannot be persisted (for example, was
*already persisted before).
*/
	public function create(\sorm\internal\payload $_payload) : \sorm\internal\value;

/**
*must update the entity defined in the payload. Must throw if the entity cannot
*be updated (for example, because it does not exist).
*/
	public function update(\sorm\internal\payload $_payload) : void;

/**
*must delete the entity defined in the payload. Must throw if the entity
*cannot be deleted (for example, because it does not exist).
*/
	public function delete(\sorm\internal\payload $_payload) : void;
}
