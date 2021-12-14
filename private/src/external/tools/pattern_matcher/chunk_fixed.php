<?php
namespace tools\pattern_matcher;

//!Represents a chunk of fixed test. This is the "hello" part in the pattern
//!"hello[id:number].
class chunk_fixed extends chunk{

	private $str;
	private $len;

	//!$_s is a string.
	public function __construct($_s) {

		$this->str=$_s;
		$this->len=strlen($this->str);
	}

	//!See chunk::match.
	//!An interesting case: the fixed pattern might be something like "1234"
	//!and the input might be "12345". Because we will only check for 4 
	//!characters, the test will return a positive. The only protection here
	//!lies in the calling code, that must check that the full input was
	//!consumed so something can be considered a match.
	public function match($_v, &$_i, array &$_res) {

		$part=substr($_v, $_i, $this->len);
		$_i+=$this->len;
		return strlen($part)==$this->len && $part==$this->str;
	}
}
