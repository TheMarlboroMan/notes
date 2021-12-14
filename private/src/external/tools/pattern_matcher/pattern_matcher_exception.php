<?php
namespace tools\pattern_matcher;

//!Exception for everything related to the pattern_matcher.
class pattern_matcher_exception extends \Exception {

	public function __construct($_msg, $_code=0, $_prev=null) {

		parent::__construct($_msg, $_code, $_prev);
	}
}
