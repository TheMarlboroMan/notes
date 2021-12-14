<?php
declare(strict_types=1);
namespace sorm;

use \sorm\internal\fetch\flags as flags;

/**
*allows to construct fetch actions, which is the way in which the entity
*manager can tell the storage layer what to retrieve. Let us not fool ourselves,
*this is clearly based on sql, but lacks many sql specifics.
*/
class fetch {

	use \sorm\traits\strict;

	public const    order_asc=0;
	public const    order_desc=1;

/**
*creates a complete order_by object with the order clauses given as arguments.
*/
	public function order_by(
		...$_orders
	) : \sorm\internal\order_by {

		return new \sorm\internal\order_by($_orders);
	}

/**
*creates a singular order clause.
*/
	public function order(
		string $_fieldname,
		int $_order=1 //desc
	) : \sorm\internal\order {

		return new \sorm\internal\order($_fieldname, $_order);
	}

/**
*creates a limit-offset instruction.
*/
	public const limit_none=-1;
	public function limit_offset(
		int $_limit=-1,
		int $_offset=0
	) : \sorm\internal\limit_offset {

		return new \sorm\internal\limit_offset($_limit, $_offset);
	}

/**
*creates a group of clauses which must all be true for the clause to be
*successful.
*/
	public function and(
		...$_nodes
	) : \sorm\interfaces\fetch_node {

		return new \sorm\internal\fetch\and_clause(flags::none, $_nodes);
	}

/**
*creates a group of clauses which will fail must all be false for the clause to
*be successful.
*/
	public function and_not(
		...$_nodes
	) : \sorm\interfaces\fetch_node {

		return new \sorm\internal\fetch\and_clause(flags::negative, $_nodes);
	}

/**
*creates a group of clauses of which one must be true for the clause to be
*successful.
*/
	public function or(
		...$_nodes
	) : \sorm\interfaces\fetch_node {

		return new \sorm\internal\fetch\or_clause(flags::none, $_nodes);
	}

/**
*creates a group of clauses which one must be false for the clause to be
*successful.
*/
	public function or_not(
		...$_nodes
	) : \sorm\interfaces\fetch_node {

		return new \sorm\internal\fetch\or_clause(flags::negative, $_nodes);
	}

/**
*equality test for numeric values
*/
	public function equals(
		string $_property,
		$_value
	) : \sorm\interfaces\fetch_node {

		return new \sorm\internal\fetch\comparison(flags::case_numeric | flags::equal, $_property, $_value);
	}

/**
*difference test for numeric values
*/
	public function not_equals(
		string $_property,
		$_value
	) : \sorm\interfaces\fetch_node {

		return new \sorm\internal\fetch\comparison(flags::case_numeric | flags::equal, $_property, $_value);
	}

/**
*numeric comparison >
*/
	public function greater_than(
		string $_property,
		$_value
	) : \sorm\interfaces\fetch_node {

		return new \sorm\internal\fetch\comparison(flags::case_numeric | flags::greater_than, $_property, $_value);
	}

/**
*numeric comparison <=
*/
	public function not_greater_than(
		string $_property,
		$_value
	) : \sorm\interfaces\fetch_node {

		return new \sorm\internal\fetch\comparison(flags::case_numeric | flags::greater_than | flags::negative, $_property, $_value);
	}

/**
*numeric comparison >=
*/
	public function larger_or_equal_than(
		string $_property,
		$_value
	) : \sorm\interfaces\fetch_node {

		return new \sorm\internal\fetch\comparison(flags::case_numeric | flags::greater_than | flags::equal, $_property, $_value);
	}

/**
*numeric comparison <
*/
	public function not_larger_or_equal_than(
		string $_property,
		$_value
	) : \sorm\interfaces\fetch_node {

		return new \sorm\internal\fetch\comparison(flags::case_numeric | flags::greater_than | flags::equal | flags::negative, $_property, $_value);
	}

/**
*numeric comparison <
*/
	public function lesser_than(
		string $_property,
		$_value
	) : \sorm\interfaces\fetch_node {

		return new \sorm\internal\fetch\comparison(flags::case_numeric | flags::lesser_than, $_property, $_value);
	}

/**
*numeric comparison >=
*/
	public function not_lesser_than(
		string $_property,
		$_value
	) : \sorm\interfaces\fetch_node {

		return new \sorm\internal\fetch\comparison(flags::case_numeric | flags::lesser_than | flags::negative, $_property, $_value);
	}

/**
*numeric comparison <=
*/
	public function lesser_or_equal_than(
		string $_property,
		$_value
	) : \sorm\interfaces\fetch_node {

		return new \sorm\internal\fetch\comparison(flags::case_numeric | flags::lesser_than | flags::equal, $_property, $_value);
	}

/**
*numeric comparison >
*/
	public function not_lesser_or_equal_than(
		string $_property,
		$_value
	) : \sorm\interfaces\fetch_node {

		return new \sorm\internal\fetch\comparison(flags::case_numeric | flags::lesser_than | flags::equal | flags::negative, $_property, $_value);
	}

/**
*equality test for strings, case insensitive
*/
	public function str_equals_ci(
		string $_property,
		$_value
	) : \sorm\interfaces\fetch_node {

		return new \sorm\internal\fetch\comparison(flags::case_insensitive | flags::equal, $_property, $_value);
	}

/**
*non-equality test for strings, case insensitive
*/
	public function str_not_equals_ci(
		string $_property,
		$_value
	) : \sorm\interfaces\fetch_node {

		return new \sorm\internal\fetch\comparison(flags::case_insensitive | flags::equal | flags::negative, $_property, $_value);
	}

/**
*test that string begins by, case insensitive
*/
	public function str_begins_by_ci(
		string $_property,
		$_value
	) : \sorm\interfaces\fetch_node {

		return new \sorm\internal\fetch\begins_by(flags::case_insensitive, $_property, $_value);
	}

/**
*test that does not begin by, case insensitive
*/
	public function str_not_begins_by_ci(
		string $_property,
		$_value
	) : \sorm\interfaces\fetch_node {

		return new \sorm\internal\fetch\begins_by(flags::case_insensitive | flags::negative, $_property, $_value);
	}

/**
*test that string ends by, case insensitive
*/
	public function str_ends_by_ci(
		string $_property,
		$_value
	) : \sorm\interfaces\fetch_node {

		return new \sorm\internal\fetch\ends_by(flags::case_insensitive, $_property, $_value);
	}

/**
*test that string does not end by, case insensitive
*/
	public function str_not_ends_by_ci(
		string $_property,
		$_value
	) : \sorm\interfaces\fetch_node {

		return new \sorm\internal\fetch\ends_by(flags::case_insensitive | flags::negative, $_property, $_value);
	}

/**
*test that string contains, case insensitive
*/
	public function str_contains_ci(
		string $_property,
		$_value
	) : \sorm\interfaces\fetch_node {

		return new \sorm\internal\fetch\contains(flags::case_insensitive, $_property, $_value);
	}

/**
*test that string does not contain, case insensitive
*/
	public function str_not_contains_ci(
		string $_property,
		$_value
	) : \sorm\interfaces\fetch_node {

		return new \sorm\internal\fetch\contains(flags::case_insensitive | flags::negative, $_property, $_value);
	}

/**
*equality test for strings, case sensitive
*/
	public function str_equals_cs(
		string $_property,
		$_value
	) : \sorm\interfaces\fetch_node {

		return new \sorm\internal\fetch\comparison(flags::case_sensitive | flags::equal, $_property, $_value);
	}

/**
*non-equality test for strings, case sensitive
*/
	public function str_not_equals_cs(
		string $_property,
		$_value
	) : \sorm\interfaces\fetch_node {

		return new \sorm\internal\fetch\comparison(flags::case_sensitive | flags::equal | flags::negative, $_property, $_value);
	}

/**
*case sensitive string check for a string that begins by a value.
*/
	public function str_begins_by_cs(
		string $_property,
		$_value
	) : \sorm\interfaces\fetch_node {

		return new \sorm\internal\fetch\begins_by(flags::case_sensitive, $_property, $_value);
	}

/**
*case sensitive string check for a string that does not begin by a value.
*/
	public function str_not_begins_by_cs(
		string $_property,
		$_value
	) : \sorm\interfaces\fetch_node {

		return new \sorm\internal\fetch\begins_by(flags::case_sensitive | flags::negative, $_property, $_value);
	}

/**
*case sensitive string check for a string that ends by a value.
*/
	public function str_ends_by_cs(
		string $_property,
		$_value
	) : \sorm\interfaces\fetch_node {

		return new \sorm\internal\fetch\ends_by(flags::case_sensitive, $_property, $_value);
	}

/**
*case sensitive string check for a string that does not end by a value.
*/
	public function str_not_ends_by_cs(
		string $_property,
		$_value
	) : \sorm\interfaces\fetch_node {

		return new \sorm\internal\fetch\ends_by(flags::case_sensitive | flags::negative, $_property, $_value);
	}

/**
*case sensitive string check for a string that contains a value.
*/
	public function str_contains_cs(
		string $_property,
		$_value
	) : \sorm\interfaces\fetch_node {

		return new \sorm\internal\fetch\contains(flags::case_sensitive, $_property, $_value);
	}

/**
*case sensitive string check for a string that does not contain a value.
*/
	public function str_not_contains_cs(
		string $_property,
		$_value
	) : \sorm\interfaces\fetch_node {

		return new \sorm\internal\fetch\contains(flags::case_sensitive | flags::negative, $_property, $_value);
	}

/**
*case sensitive string check for a value that can be evaluated as a boolean and
*must be true in order to succeed.
*/
	public function is_true(
		string $_property
	) : \sorm\interfaces\fetch_node {

		return new \sorm\internal\fetch\is_true(flags::none, $_property);
	}

/**
*case sensitive string check for a value that can be evaluated as a boolean and
*must be false in order to succeed.
*/
	public function is_false(
		string $_property
	) : \sorm\interfaces\fetch_node {

		return new \sorm\internal\fetch\is_true(flags::negative, $_property);
	}

/**
*checks for a value to be in a list of values.
*/
	public function in(
		string $_property,
		...$_values
	) : \sorm\interfaces\fetch_node {

		return new \sorm\internal\fetch\in(flags::none, $_property, $_values);
	}

/**
*checks for a value not to be in a list of values.
*/
	public function not_in(
		string $_property,
		...$_values
	) : \sorm\interfaces\fetch_node {

		return new \sorm\internal\fetch\in(flags::negative, $_property, $_values);
	}
}
