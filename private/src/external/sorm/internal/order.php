<?php
declare(strict_types=1);
namespace sorm\internal;
/**
*a single order clause, expresses that fetch results must be ordered according
*to the given field name and in the given order.
*/
class order {

	use \sorm\traits\strict;

	public function __construct(
		string $_fieldname,
		int $_order
	) {

		$this->fieldname=$_fieldname;
		$this->order=$_order;
	}

/**
*returns the field name.
*/

	public function         get_fieldname() :string {

		return $this->fieldname;
	}

/**
*returns the order type, which corresponds to one of the fetch class constants.
*/

	public function         get_order() :int {

		return $this->order;
	}

	private string          $fieldname;
	private int             $order;

}
