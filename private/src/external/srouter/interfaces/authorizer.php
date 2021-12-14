<?php
declare(strict_types=1);
namespace srouter\interfaces;

/**
*describes a class that can authorize a request. Only the request is passed:
*any other context must be provided by the implenentor at construction time.
*/

interface authorizer {

/**
*must return true if the request is authorized to proceed. The whole route is
*sent along, because of extra information (like uri arguments) that might
*reside there.
*/
	public function authorize(\srouter\interfaces\request $_request, \srouter\route $_route) : bool;
}
