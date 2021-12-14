<?php
namespace tools\pattern_matcher;

//!A result encapsulates the pattern information and its parameters in a single
//!object. This will be always returned when using matcher::match. To test if
//!a match was found, is_match is used. Only the name of the path will be
//!returned, along with any parameters.
class result {

	private	$success;
	private $name;
	private $metadata;
	private $params=[];


	//!$_s is a boolean, $_n is a string, $_md is the metadata object, which
	//!may be null. $_m is an array of parameter.
	public function __construct($_s, $_n, $_md, array $_m) {
		$this->success=$_s;
		$this->name=$_n;
		$this->metadata=$_md;
		$this->params=$_m;
		//TODO: Add meta...
	}

	//!returns true if a match was found, false if not.
	public function is_match() {
		return $this->success;
	}

	//!returns the name of the pattern matched, if any.
	public function get_name() {
		return $this->name;
	}

	//!returns the resolved parameters of the pattern matched, if any.
	public function get_parameters() {
		return $this->params;
	}

	//!returns the stdObject metadata object.
	public function get_metadata() {
		return $this->metadata;
	}

	//!returns true if a parameter with the given name is found.
	public function has_parameter($_name) {

		return count(array_filter($this->params, function($_item) use ($_name) {return $_name==$_item->get_name();}));
	}

	//returns the parameter with the given name. Throws if not found.
	public function get_parameter($_name) {

		if(!$this->has_parameter($_name)) {
			throw new pattern_matcher_exception("unable to find parameter '$_name'");
		}

		$data=array_filter($this->params, function($_item) use ($_name) {return $_name==$_item->get_name();});
		if(!count($data)) {
			throw new pattern_matcher_exception("unable to find and filter parameter '$_name'");
		}

		return array_shift($data);
	}
}
