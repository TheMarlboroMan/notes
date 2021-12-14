<?php
declare(strict_types=1);
namespace sorm\internal;

use \sorm\internal\fetch\flags as flags;

/**
*provides a fetch translator for PDO.
*/
class pdo_fetch_translator implements \sorm\interfaces\fetch_translator {

	use \sorm\traits\strict;

	public function __construct(
		?\sorm\interfaces\value_mapper_factory $_value_mapper_factory
	) {

		$this->value_mapper_factory=$_value_mapper_factory;
	}

/**
*sets the entity definition for the given fetch operation.
*/

	public function set_entity_definition(
		\sorm\internal\entity_definition $_def
	) {

		$this->entity_definition=$_def;
	}

/***
*resets the internal buffer so it can build another query...
*/
	public function reset() : \sorm\internal\pdo_fetch_translator {

		$this->buffer=[];
		$this->arguments=[];
		return $this;
	}

/**
*returns the query string that was built from this translator.
*/

	public function to_query_string() : string {

		return implode(" ", $this->buffer);
	}

/**
*returns any arguments that could be inferred from the query nodes.
*/
	public function get_arguments() : array {

		return $this->arguments;
	}

/**
*implementation of fetch_translator
*/
	public function do_and(\sorm\internal\fetch\and_clause $_node) : void {

		if($_node->get_flags() & flags::negative) {

			$this->buffer[]="NOT";
		}

		$this->buffer[]="(";

		array_map(
			function(\sorm\interfaces\fetch_node $_node) {

				$_node->accept($this);
				$this->buffer[]="AND";
				return null;
			},
			$_node->get_clauses()
		);

		array_pop($this->buffer);
		$this->buffer[]=")";
	}

/**
*implementation of fetch_translator
*/
	public function do_or(\sorm\internal\fetch\or_clause $_node) : void {

		if($_node->get_flags() & flags::negative) {

			$this->buffer[]="NOT";
		}

		$this->buffer[]="(";

		array_map(
			function(\sorm\interfaces\fetch_node $_node) {

				$_node->accept($this);
				$this->buffer[]="OR";
				return null;
			},
			$_node->get_clauses()
		);

		array_pop($this->buffer);
		$this->buffer[]=")";
	}

/**
*implementation of fetch_translator
*/
	public function do_begins_by(\sorm\internal\fetch\begins_by $_node) : void {

		$flags=$_node->get_flags();
		$field=$this->to_storage_name($_node->get_property());
		$placeholder_mark=$this->make_argument($_node->get_value()."%", $_node->get_property());

		$boolean_logic=$flags & flags::negative
			? "NOT"
			: "";

		$this->buffer[]=$flags & flags::case_insensitive
			? "(LOWER(`$field`) $boolean_logic LIKE LOWER($placeholder_mark) )"
			: "(`$field` $boolean_logic LIKE $placeholder_mark)";
	}

/**
*implementation of fetch_translator
*/
	public function do_comparison(\sorm\internal\fetch\comparison $_node) : void {

		if(
			$_node->get_flags() & flags::case_numeric
		) {

			$this->do_numeric_comparison($_node);
			return;
		}

		if(
			$_node->get_flags() & flags::case_insensitive
		) {

			//yep , same operators...
			$this->do_numeric_comparison($_node);
			return;
		}

		if(
			$_node->get_flags() & flags::case_sensitive
		) {

			$this->do_string_comparison($_node);
		}
	}

/**
*implementation of fetch_translator
*/
	public function do_contains(\sorm\internal\fetch\contains $_node) : void {

		$flags=$_node->get_flags();
		$field=$this->to_storage_name($_node->get_property());
		$placeholder_mark=$this->make_argument("%".$_node->get_value()."%", $_node->get_property());

		$boolean_logic=$flags & flags::negative
			? "NOT"
			: "";

		$this->buffer[]=$flags & flags::case_insensitive
			? "(LOWER(`$field`) $boolean_logic LIKE LOWER($placeholder_mark) )"
			: "(`$field` $boolean_logic LIKE $placeholder_mark)";
	}

/**
*implementation of fetch_translator
*/
	public function do_ends_by(\sorm\internal\fetch\ends_by $_node) : void {

		$flags=$_node->get_flags();
		$field=$this->to_storage_name($_node->get_property());
		$placeholder_mark=$this->make_argument("%".$_node->get_value(), $_node->get_property());

		$boolean_logic=$flags & flags::negative
			? "NOT"
			: "";

		$this->buffer[]=$flags & flags::case_insensitive
			? "(LOWER(`$field`) $boolean_logic LIKE LOWER($placeholder_mark) )"
			: "(`$field` $boolean_logic LIKE $placeholder_mark)";
	}

/**
*implementation of fetch_translator
*/
	public function do_in(\sorm\internal\fetch\in $_node) : void {

		$flags=$_node->get_flags();
		$field=$this->to_storage_name($_node->get_property());
		$placeholder_marks=array_map(
			function($_value) use ($_node) {
				return $this->make_argument($_value, $_node->get_property());
			},
			$_node->get_values()
		);

		$boolean_logic=$flags & flags::negative
			? "NOT"
			: "";

		$this->buffer[]="(`$field` $boolean_logic IN (".implode(", ", $placeholder_marks).") )";
	}

/**
*implementation of fetch_translator
*/
	public function do_is_true(\sorm\internal\fetch\is_true $_node) : void {

		$field=$this->to_storage_name($_node->get_property());
		$this->buffer[]=$_node->get_flags() & flags::negative
			? "(NOT `$field`)"
			: "(`$field`)";
	}

/**
*creates a numeric comparison
*/
	private function do_numeric_comparison(
		\sorm\internal\fetch\comparison $_node
	) {

		$flags=$_node->get_flags();
		if( (flags::equal | flags::lesser_than) == ($flags & (flags::equal | flags::lesser_than))) {

			$operator="<=";
		}
		else if( (flags::equal | flags::greater_than) == ($flags & (flags::equal | flags::greater_than))) {

			$operator=">=";
		}
		else if($flags & flags::equal) {

			$operator="=";
		}
		else if($flags & flags::lesser_than) {

			$operator="<";
		}
		else if($flags & flags::greater_than) {

			$operator=">";
		}
		else {

			throw new \sorm\exception\exception("malformed numeric comparison");
		}

		if($flags & flags::negative) {

			switch($operator) {

				case "=": $operator="!="; break;
				case "<": $operator=">="; break;
				case ">": $operator="<="; break;
				case "<=": $operator=">"; break;
				case ">=": $operator="<"; break;
			}
		}

		$field=$this->to_storage_name($_node->get_property());
		$placeholder_mark=$this->make_argument($_node->get_value(), $_node->get_property());
		$this->buffer[]="(`$field` $operator $placeholder_mark)";
	}

/**
*creates a string comparison
*/
	private function do_string_comparison(
		\sorm\internal\fetch\comparison $_node
	) {

		$flags=$_node->get_flags();
		switch(true) {
			case $flags & flags::equal: $operator="="; break;
			default:
				throw new \sorm\exception\exception("malformed string comparison");
		}

		if($flags & flags::negative) {

			switch($operator) {

				case "=": $operator="!="; break;
			}
		}

		$field=$this->to_storage_name($_node->get_property());
		$placeholder_mark=$this->make_argument($_node->get_value(), $_node->get_property());
		$this->buffer[]="(`$field` $operator BINARY $placeholder_mark)";
	}

/**
*adds a new argument to the argument cache and generates its placeholder for
*the prepared statement.
*/
	private function make_argument(
		$_value,
		string $_property
	) {

		$placeholder=":placeholder_".count($this->arguments);
		$def=$this->entity_definition[$_property];
		if(null !== $this->value_mapper_factory && null !== $def->get_transform_key()) {

			$mapper=$this->value_mapper_factory->build_value_mapper($def->get_transform_key());
			$_value=$mapper->to_storage($def->get_transform_method(), $_value);
		}

		$this->arguments[]=$_value;
		return $placeholder;
	}

/**
*transforms a property to its storage name.
*/
	private function to_storage_name(
		string $_propname
	) {

		return $this->entity_definition[$_propname]->get_field();
	}

/*
	public function add_custom_handler($_handler) : \sorm\interfaces\fetch_translator{

		$this->custom_handlers[]=$_handler;
		return $this;
	}

	public function do_custom($_other) : void {

		//TODO: yep. Big pickle here... I guess this we can OVERRIDE this one.
	}

	private array                   $custom_handlers=[];
*/

	private array                                   $buffer=[];
	private array                                   $arguments=[];
	private ?\sorm\internal\entity_definition       $entity_definition=null;
	private ?\sorm\interfaces\value_mapper_factory  $value_mapper_factory;
}
