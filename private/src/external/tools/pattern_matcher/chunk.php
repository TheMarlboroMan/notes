<?php
namespace tools\pattern_matcher;

//!Base class for a chunk. A chunk is every different part of a pattern. In
//!the pattern hello/this/[name:id]/status, "hello/this/", name:id and "/status"
//!are chunks. The only separation between chunks is the presence of different
//!chunk types (fixed or matched), denoted by the appearance of [].
abstract class chunk {
	//!_s is the full string, _i is the index where the previous match left,
	//!and must be written by each pattern_chunk. $_res is where the params are
	//!written (an array of param object).
	//!Will return true if the string is found to match the chunk, starting
	//!from the string index $_i.
	public abstract function match($_s, &$_i, array &$_res);
}
