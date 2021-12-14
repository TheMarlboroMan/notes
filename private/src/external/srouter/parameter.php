<?php
declare(strict_types=1);
namespace srouter;

/**
*describes the mapping of a controller parameter method to the request via the
*router. each parameter has a name (the parameter name), source (implementation
*dependant), an optional type for type checking safety and can be nullable,
*and optional, with a defaut value.
*all values will be trimmed by default unless "notrim" is specified.
*/

class parameter {

	use \srouter\traits\strict;

	public const    type_any=0;
	public const    type_int=1;
	public const    type_bool=2;
	public const    type_string=3;
	public const    type_double=4;
	public const    type_array=5;

/**
*class constructor.
*/

	public function __construct(
		string  $_name,
		string  $_source,
		int     $_type,
		bool    $_nullable,
		bool    $_optional,
		bool    $_notrim,
		$_default=null
	) {

		$this->name=$_name;
		$this->source=$_source;
		$this->type=$_type;
		$this->nullable=$_nullable;
		$this->optional=$_optional;
		if($this->optional) {

			$this->default=$_default;
		}

		$this->notrim=$_notrim;
	}

/**
*returns the parameter name so it can be retrieved from the request and mapped
*to the PHP parameter. usually would be just the same as the PHP name.
*/
	public function get_name() : string {

		return $this->name;
	}

/**
*returns a string with the "source" name. This is fully implementation dependant
*for the parameter_extractors and should represent something along the lines of
*"this parameter comes from the query string".
*/
	public function get_source() : string {

		return $this->source;
	}

/**
*returns any of the public type constants of this class expressing what the
*type of the parameter is (or any, for "mixed").
*/
	public function get_type() : int {

		return $this->type;
	}

/**
*must return true if the parameter can contain a null value regardless of its
*type. If not, a value inferred as null will be treated as an error.
*/
	public function is_nullable() : bool {

		return $this->nullable;
	}

/**
*must return true if the value must not be trimmed. strings and strings inside
*arrays will be trimmed by the argument_maker class.
*/
	public function is_notrim() : bool {

		return $this->notrim;
	}

/**
*must return true if the parameter is considered optional. When a parameter is
*optional it could have a default value.
*/
	public function is_optional() : bool {

		return $this->optional;
	}

/**
*returns the default value for an optional parameter. Notice that this somewhat
*incompatible with PHP default parameter values because the router will always
*attempt to locate parameters for all parameters.
*/
	public function get_default() {

		return $this->default;
	}

	private string      $name;
	private string      $source;
	private int         $type;
	private bool        $nullable;
	private bool        $notrim;
	private bool        $optional;
	private             $default;
}
