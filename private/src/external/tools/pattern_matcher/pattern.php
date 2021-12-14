<?php
namespace tools\pattern_matcher;

//!A pattern is a string that may be composed of fixed and variable chunks.
//!Patterns are expressed with a simple syntax:
//!"user/[userid:int]/emails/[email:alpha]/inbox, in which we split it into
//!chunks. Variable chunks are enclosed by [], having a pair of name-type
//!inside, separated by :.
//!A pattern is identified by its name, which will be returned upon calling
//!matcher::match, along with every variable chunk resolved to a pair name:value
//!in a "parameter".
//!Patterns may include a metadata node, which will be included "as is"
//!in a stdClass object.
class pattern {

	private	$raw_pattern;
	private	$name;
	private $metadata;
	private $chunks=[];

	const chr_open='[';
	const chr_close=']';
	const mode_literal=0;
	const mode_pattern=1;
	const mode_end=-1;

	//!$_p is the string pattern, $_i is the name (string). Will throw if
	//!the pattern cannot be prepared.
	public function		__construct($_p, $_i, $_m=null) {

		$this->raw_pattern=$_p;
		$this->name=$_i;
		$this->metadata=$_m;

		$this->prepare();
		$this->check_integrity();
	}

	//!matches this pattern agains the string. Will return null on failure
	//!ir a result object if successful-
	public function		matches($_input) {

		$results=[];
		$index=0;

		$max_index=strlen($this->raw_pattern);
		foreach($this->chunks as $v) {

			if(false===$v->match($_input, $index, $results)) {
				return null;
			}
		}

		//And yet still, if we didn't consume the full input, we cannot say
		//this is a match...
		if($index < strlen($_input)) {
			return null;
		}

		return new result(true, $this->name, $this->metadata, $results);
	}

	//!Returns the pattern name.
	public function get_name() {
		return $this->name;
	}

	//!Sets the pattern name. Allows modification of the name to adapt the same
	//!template chunks to different needs (postfix, suffix, transformation...).
	public function set_name($_val) {

		$this->name=$_val;
		return $this;
	}

	//!Returns the pattern.
	public function get_pattern() {
		return $this->raw_pattern;
	}

	//!Sets the pattern name. Allows modification of the name to adapt the same
	//!template chunks to different needs (postfix, suffix, transformation...).
	public function set_pattern($_val) {

		$this->raw_pattern=$_val;
		$this->prepare();
		$this->check_integrity();
		return $this;
	}

	//!splits the pattern into its composing chunks.
	private function	prepare() {

		$this->chunks=[];

		$current='';
		$mode=self::mode_literal;
		$last_index=strlen($this->raw_pattern)-1;

		for($index=0; $index < strlen($this->raw_pattern); $index++) {

			$new_mode=$mode;
			$char=$this->raw_pattern[$index];

			if($char==self::chr_open) {
				$new_mode=self::mode_pattern;
			}
			else if($char==self::chr_close) {
				$new_mode=self::mode_literal;
			}
			else if($index==$last_index) {
				$new_mode=self::mode_end;
				$current.=$char; //Stupid hack to add the last character.
			}

			if($new_mode==$mode) {
					$current.=$char;
			}
			else {
				if(strlen($current)) {
					switch($mode) {
						case self::mode_literal:
							$this->chunks[]=new chunk_fixed($current); break;
						case self::mode_pattern:
							$next_character=$index==$last_index ? null : $this->raw_pattern[$index+1];
							$this->chunks[]=new chunk_match($current, $next_character); break;
					}

					$current='';
				}
				$mode=$new_mode;
			}
		}
	}

	//!checks the integrity of the pattern. Throws upon any failure.
	private function check_integrity() {


		if(!count($this->chunks)) {
			throw new pattern_matcher_exception("pattern must have at least one part for '$this->raw_pattern'");
		}

		//TODO: Why is this???. Test it again...
		if($this->chunks[0] instanceof chunk_match) {
			throw new pattern_matcher_exception("the first part of a pattern cannot be a match type for '$this->raw_pattern'");
		}

		//TODO: Check for duplicate names!!!!
	}
}
