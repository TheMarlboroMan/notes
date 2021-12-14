<?php
declare(strict_types=1);
namespace sorm\internal\fetch;

/**
*defines internal flags for the fetch clauses.
*/
abstract class flags {

	const none=0; //<! No flags.

	const case_numeric=1; //<! a numerical comparison
	const case_sensitive=2; //<! a string comparison, case sensitive
	const case_insensitive=4; //<! a string comparison, case insensitive
	const negative=8; //<! negates the given clause.
	const equal=16; //<! A string or numeric equality test.
	const greater_than=32; //<! A string or numeric value test.
	const lesser_than=64; //<! A string or numeric value test.
}
