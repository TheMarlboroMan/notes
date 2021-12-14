<?php
namespace tools\pattern_matcher;

//!Represents a chunk of variable data. This is the "id:number" part in the pattern
//!"hello[id:number].
class chunk_match extends chunk {

	const chr_separate=':';
	const type_integer=0;
	const type_alpha=1;
	const type_alphanum=2;
	const type_urllike=3;
	const literal_integer='int';
	const literal_alpha='alpha';
	const literal_alphanum='alnum';
	const literal_urllike='urllike';

	private $type;
	private $name;
	private $last_character;

	//!_s is the string chunk. $_l is a character or null, used to signify when
	//!to stop reading.
	public function __construct($_s, $_l) {

		$this->last_character=$_l;

		if(false===strpos($_s, self::chr_separate)) {
			throw new pattern_matcher_exception("could not find separator in match '".$_s."'");
		}

		$data=explode(self::chr_separate, $_s);
		if(2!=count($data)) {
			throw new pattern_matcher_exception("match must have exactly two parts in '".$_s."'");
		}

		try {
			$this->prepare($data[0], $data[1]);
		}
		catch(\Exception $e) {
			throw new pattern_matcher_exception($e->getMessage()." in '".$_s."'");
		}
	}

	//!Stores the neccesary information for the chunk to work: its name and
	//!its type (string and integer).
	private function prepare($_n, $_t) {

		$this->name=$_n;

		switch($_t) {
			case self::literal_integer: $this->type=self::type_integer; break;
			case self::literal_alpha: $this->type=self::type_alpha; break;
			case self::literal_alphanum: $this->type=self::type_alphanum; break;
			case self::literal_urllike: $this->type=self::type_urllike; break;
			default:
				throw new pattern_matcher_exception("unknown match type"); break;
		}
	}

	//!Check chunk::match.
	public function match($_v, &$_i, array &$_res) {

		$last_index=strlen($_v)-1;
		$val='';

		for($_i; ; $_i++) {

			//We might have arrived at the end of the test string without 
			//failure, which is a match.
			if($_i>$last_index) {
				break;
			}

			$char=$_v[$_i];
			$val.=$char;

			//Or perhaps we are a the end of the current match...
			if($char==$this->last_character) {				
				$val=substr($val, 0, -1);
				break;
			}
			switch($this->type) {
				//TODO: What about negative numbers?
				case self::type_integer: if(!ctype_digit($char)) return false; break;
				case self::type_alpha: if(!ctype_alpha($char)) return false; break;
				case self::type_alphanum: if(!ctype_alnum($char)) return false; break;
				case self::type_urllike: if(!ctype_alnum($char) && !in_array($char, ['.','-','_','+','%'])) return false; break;
			}
		}

		$_res[]=new parameter($this->name, $val);
		return true;
	}
}
