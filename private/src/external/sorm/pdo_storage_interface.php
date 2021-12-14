<?php
declare(strict_types=1);
namespace sorm;

/**
*PDO storage implementation.
*/
class pdo_storage_interface implements \sorm\interfaces\storage_interface {

	use \sorm\traits\strict;

/**
*class constructor. Sets the PDO mode to throw exceptions and to not
*emulate prepared statements, no excuses.
*/
	public function __construct(
		\PDO $_pdo
	) {

		$this->pdo=$_pdo;
		$this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
		$this->pdo->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
	}

	public function fetch(
		\sorm\internal\entity_definition $_def,
		\sorm\internal\entity_inflator $_inflator,
		?\sorm\interfaces\value_mapper_factory $_value_mapper_factory,
		\sorm\interfaces\fetch_node $_criteria,
		?\sorm\internal\order_by $_order=null,
		?\sorm\internal\limit_offset $_limit_offset=null
	) : \sorm\interfaces\fetch_collection {

		if(null===$this->fetch_translator) {

			$this->fetch_translator=new \sorm\internal\pdo_fetch_translator($_value_mapper_factory);
		}

		$this->fetch_translator->set_entity_definition($_def);

		$calc="";
		if(
			null !== $_limit_offset
			&&\sorm\internal\limit_offset::no_limit !== $_limit_offset->get_limit()
		) {

			$calc="SQL_CALC_FOUND_ROWS";
		}

		//build query string...
		$qstr="SELECT $calc * FROM `".$_def->get_storage_key()."` WHERE ";

		//criteria
		$_criteria->accept(
			$this->fetch_translator->reset()
		);

		$qstr.=$this->fetch_translator->to_query_string();

		//order by
		if(null !== $_order && $_order->has_order()) {

			$qstr.=" ORDER BY ";

			$orderstr=array_map(
				function(\sorm\internal\order $_order) : string {

					$ordertype=$_order->get_order()===\sorm\fetch::order_asc ? "ASC" : "DESC";
					return "`".$_order->get_fieldname()."` ".$ordertype;
				},
				iterator_to_array($_order)
			);

			$qstr.=implode(", ", $orderstr);
		}

		//limit and offset
		if(null !== $_limit_offset) {

			if(\sorm\internal\limit_offset::no_limit !== $_limit_offset->get_limit()) {

				$qstr.=" LIMIT ".$_limit_offset->get_limit();
			}

			if(0 !== $_limit_offset->get_offset()) {

				$qstr.=" OFFSET ".$_limit_offset->get_offset();
			}
		}

		//get statement and fill up values...
		$hash=md5($qstr);
		$stmt=$this->get_fetch_statement($hash, $qstr);

		$i=0;
		foreach($this->fetch_translator->get_arguments() as $arg) {

			//TODO: and what PDO type would that be??
			$stmt->bindValue(":placeholder_".$i++, $arg);
		}

		if(!$stmt->execute()) {

			throw new \sorm\exception\exception("could not fetch data");
		}

		$total=-1;
		if(strlen($calc)) {

			$total=$this->pdo->query('SELECT FOUND_ROWS();')->fetch(\PDO::FETCH_COLUMN);
		}

		return new \sorm\fetch_collection($stmt, $_def, $_inflator, $total);
	}

/**
*the payload comes with all transformed values already!
*/
	public function create(
		\sorm\internal\payload $_payload
	) : \sorm\internal\value {

		$stmt=$this->get_create_statement($_payload);
		foreach($_payload as $key => $value) {

			$stmt->bindValue(":".$key, $this->to_pdo_value($value), $this->to_pdo_type($value));
		}

		if(!$stmt->execute()) {

			throw new \sorm\exception\exception("could not create entity");
		}

		if(!$stmt->rowCount()) {

			throw new \sorm\exception\exception("entity could not be inserted");
		}

		return new \sorm\internal\value(
			$this->pdo->lastInsertId(),
			\sorm\types::t_any //yep, as any. Actually PDO seems to return a string, according to the docs.
		);
	}

	public function update(
		\sorm\internal\payload $_payload
	) : void {

		$definition=$_payload->get_entity_definition();
		$pk=$_payload[$definition->get_primary_key_name()];

		//before even attempting this, let us see if there'a row here...
		$check_stmt=$this->get_check_statement($_payload);
		$check_stmt->bindValue(":pk", $this->to_pdo_value($pk), $this->to_pdo_type($pk));
		$check_stmt->execute();
		if(!$check_stmt->rowCount()) {

			throw new \sorm\exception\exception("entity could not be found for update");
		}

		$stmt=$this->get_update_statement($_payload);
		$stmt->bindValue(":pk", $this->to_pdo_value($pk), $this->to_pdo_type($pk));
		foreach($_payload as $key => $value) {

			$stmt->bindValue(":".$key, $this->to_pdo_value($value), $this->to_pdo_type($value));
		}

		if(!$stmt->execute()) {

			throw new \sorm\exception\exception("could not update entity");
		}

/*
		this might return zero if no changes were made!!
		if(!$stmt->rowCount()) {


		}
*/
	}

	public function delete(
		\sorm\internal\payload $_payload
	) : void {

		//The entity manager will catch anything that throws...
		$stmt=$this->get_delete_statement($_payload);

		$pk=$_payload->get_primary_key();
		$stmt->bindValue(":pk", $this->to_pdo_value($pk), $this->to_pdo_type($pk));

		if(!$stmt->execute()) {

			throw new \sorm\exception\exception("could not delete entity");
		}

		if(!$stmt->rowCount()) {

			throw new \sorm\exception\exception("entity could not be found for deletion");
		}
	}

	private function            get_create_statement(
		\sorm\internal\payload $_payload
	) : \PDOStatement {

		$definition=$_payload->get_entity_definition();
		$classname=$definition->get_class_name();
		if(!array_key_exists($classname, $this->create_statements_map)) {


			$fields=[];
			$values=[];

			foreach($_payload as $key => $value) {

				$fields[]="`$key`";
				$values[]=":$key";
			}

			$query_string="INSERT INTO `".$definition->get_storage_key()."` (".implode(", ", $fields).") VALUES (".implode(", ", $values).");";
			$this->create_statements_map[$classname]=$this->pdo->prepare($query_string);
		}

		return $this->create_statements_map[$classname];
	}

	private function            get_check_statement(
		\sorm\internal\payload $_payload
	) : \PDOStatement {

		$definition=$_payload->get_entity_definition();
		$classname=$definition->get_class_name();
		if(!array_key_exists($classname, $this->create_statements_map)) {

			$query_string="SELECT `".$definition->get_primary_key_name()."` AS id FROM `".$definition->get_storage_key()."` WHERE `".$definition->get_primary_key_name()."` = :pk";
			$this->check_statements_map[$classname]=$this->pdo->prepare($query_string);
		}

		return $this->check_statements_map[$classname];
	}

	private function            get_update_statement(
		\sorm\internal\payload $_payload
	) : \PDOStatement {

		$definition=$_payload->get_entity_definition();
		$classname=$definition->get_class_name();
		if(!array_key_exists($classname, $this->update_statements_map)) {

			$assignments=[];
			foreach($_payload as $key => $value) {

				$assignments[]="`$key`= :$key";
			}

			$query_string="UPDATE `".$definition->get_storage_key()."` SET ".implode(", ", $assignments)." WHERE `".$definition->get_primary_key_name()."` = :pk";
			$this->update_statements_map[$classname]=$this->pdo->prepare($query_string);
		}

		return $this->update_statements_map[$classname];
	}

	private function            get_delete_statement(
		\sorm\internal\payload $_payload
	) : \PDOStatement {

		$definition=$_payload->get_entity_definition();
		$classname=$definition->get_class_name();
		if(!array_key_exists($classname, $this->delete_statements_map)) {

			$query_string="DELETE FROM `".$definition->get_storage_key()."` WHERE `".$definition->get_primary_key_name()."` = :pk";
			$this->delete_statements_map[$classname]=$this->pdo->prepare($query_string);
		}

		return $this->delete_statements_map[$classname];
	}

	private function            get_fetch_statement(
		string $_hash,
		string $_query_string
	) :\PDOStatement {


		if(!array_key_exists($_hash, $this->fetch_statements)) {

			$this->fetch_statements[$_hash]=$this->pdo->prepare($_query_string);
		}

		return $this->fetch_statements[$_hash];
	}

	private function            to_pdo_type(
		\sorm\internal\value $_value
	) {

		if(null===$_value->get_value()) {

			return \PDO::PARAM_NULL;
		}

		switch($_value->get_type()) {

			case \sorm\types::t_any:
			case \sorm\types::t_string:
			case \sorm\types::t_double: //this never ceases to amuse me.
			case \sorm\types::t_datetime:
				return \PDO::PARAM_STR;
			case \sorm\types::t_bool:
			case \sorm\types::t_int:
				return \PDO::PARAM_INT;
		}
	}

	private function            to_pdo_value(
		\sorm\internal\value $_value
	) {

		if(null===$_value->get_value()) {

			return null;
		}

		switch($_value->get_type()) {

			case \sorm\types::t_any:
			case \sorm\types::t_string:
			case \sorm\types::t_double: //this never ceases to amuse me.
			case \sorm\types::t_int:
				return $_value->get_value();
			case \sorm\types::t_datetime:
				return $_value->get_value()->format("Y-m-d H:i:s");
			case \sorm\types::t_bool:
				return $_value->get_value() ? 1 : 0;
		}
	}

	private \PDO                $pdo;
	private array               $create_statements_map=[];
	private array               $check_statements_map=[];
	private array               $update_statements_map=[];
	private array               $delete_statements_map=[];
	private array               $fetch_statements=[];
	private ?\sorm\internal\pdo_fetch_translator $fetch_translator=null;
}
