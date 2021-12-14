<?php
declare(strict_types=1);
namespace sorm\interfaces;

/**
*defines a class that contains a result of a fetch query.
*/

interface fetch_collection {

/**
*must return the amount of entities present in the collection.
*/
	public function         get_count() : int;

/**
*must return the amount of entities that would be present in the collection
*if no limit clause was defined. Must return the same as get_count if no
*limit clause was defined.
*/
	public function         get_unlimited_count() : int;

/**
*must return the next entity in the collection, of null if there are no more.
*/
	public function         next() : ?\sorm\interfaces\entity;

/**
*must return an array with all the entities in the collection.
*/
	public function         all() : array;
}
