<?php
declare(strict_types=1);
namespace sorm;

/**
*the result of a fetch operation allowing access (not neccesarily containing)
*the resulting entities.
*/
class fetch_collection implements \sorm\interfaces\fetch_collection {

	use \sorm\traits\strict;

	public function         __construct(
		\PDOStatement $_statement,
		\sorm\internal\entity_definition $_definition,
		\sorm\internal\entity_inflator $_inflator,
		int $_total=-1
	) {
		$this->statement=$_statement;
		$this->unlimited_count=-1 === $_total
			? $this->statement->rowCount()
			: $_total;

		$this->count=$this->statement->rowCount();
		$this->definition=$_definition;
		$this->inflator=$_inflator;
	}

/**
*implementation of fetch_collection
*/
	public function         get_count() : int {

		return $this->count;
	}

/**
*implementation of fetch_collection
*/
	public function         get_unlimited_count() : int {

		return $this->unlimited_count;
	}

/**
*implementation of fetch_collection
*/
	public function         next() : ?\sorm\interfaces\entity {

		$data=$this->statement->fetch(\PDO::FETCH_ASSOC);
		if(false===$data) {

			return null;
		}

		$entity=$this->inflator->build_entity($this->definition);
		return $this->inflator->inflate($entity, $this->definition, $data);
	}

/**
*implementation of fetch_collection
*/
	public function         all() : array {

		$result=[];
		while(true) {

			$data=$this->next();
			if(null===$data) {
				break;
			}
			$result[]=$data;
		}

		return $result;
	}

	private int             $count;
	private int             $unlimited_count;
	private \PDOStatement   $statement;
	private \sorm\internal\entity_definition $definition;
	private \sorm\internal\entity_inflator $inflator;

}
