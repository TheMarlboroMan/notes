<?php
declare(strict_types=1);
namespace srouter\interfaces;

/**
*defines a basic interface for the controller factory.
*/

interface controller_factory {

/**
*must build the controller with the given classname, satisfying its dependencies
*with whatever is passed at construction time. Notice that there's no interface
*for the controller. They will be checked at runtime to implement the called
*methods.
*/
	public function build_controller(string $_classname);

};
