<?php
namespace notes\router;

class path_mapper implements \srouter\interfaces\path_mapper {

	public function __construct(
		string $_filename
	) {

		$this->matcher=\tools\pattern_matcher\matcher::from_empty();

		$contents=file_get_contents($_filename);
		if(false===$contents) {

			throw new \Exception("could not read contents of $_filename");
		}

		$path_info=json_decode($contents);
		if(JSON_ERROR_NONE !== json_last_error()) {

			throw new \Exception("failed to decode path index file: ".json_last_error_msg());
		}

		foreach($path_info as $value) {

			$this->matcher->add_pattern(
				new \tools\pattern_matcher\pattern($value->path, count($this->path_map))
			);
			$this->path_map[]=$value->methods;
		}
	}

	public function map(
		string $_method,
		string $_uri
	) : ?\srouter\route {


		$match=$this->matcher->match($_uri);
		if(!$match->is_match()) {

			return null;
		}

		$family=$this->path_map[$match->get_name()];
		if(!property_exists($family, $_method)) {

			return null;
		}

		$path=$family->$_method;

		$optional_val=function(\stdClass $_item, string $_prop, $default_val) {

			return property_exists($_item, $_prop)
				? $_item->$_prop
				: $default_val;
		};

		//check these properties exist.
		foreach(["controller", "method", "auth", "out", "arguments"] as $prop) {

			if(!property_exists($path, $prop)) {

				throw new \Exception("missing '$prop' property when mapping paths");
			}
		}

		$arguments=array_map(
			function(\stdClass $_item) use ($optional_val): \srouter\parameter {

				$argtype=$optional_val($_item, "type", "any");
				switch($argtype) {

					case "any":
						$type=\srouter\parameter::type_any; break;
					case "int":
						$type=\srouter\parameter::type_int; break;
					case "string":
						$type=\srouter\parameter::type_string; break;
					case "array":
						$type=\srouter\parameter::type_array; break;
					case "double":
						$type=\srouter\parameter::type_double; break;
					case "bool":
						$type=\srouter\parameter::type_bool; break;
					default:
						throw new \Exception("unkown argument type '$argtype'");
				}

				return new \srouter\parameter(
					$_item->name,
					$_item->source,
					$type,
					$optional_val($_item, "nullable", false),
					$optional_val($_item, "optional", false),
					$optional_val($_item, "notrim", false),
					$optional_val($_item, "default", null)
				);
			},
			$path->arguments
		);

		return new \srouter\route(
			$path->controller,
			$path->method,
			$arguments,
			$path->out,
			array_map(
				function(\tools\pattern_matcher\parameter $_item) {

					return new \srouter\uri_argument($_item->get_name(), $_item->get_value());
				},
				$match->get_parameters()
			),
			$path->auth,
			null,
			"json",
			"underscore",
			["error_handler"]
		);
	}

	private \tools\pattern_matcher\matcher  $matcher;
	private array                           $path_map=[];
}
