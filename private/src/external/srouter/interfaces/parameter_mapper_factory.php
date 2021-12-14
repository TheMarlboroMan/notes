<?php
declare(strict_types=1);
namespace srouter\interfaces;

/**
*describes a class that can create instances that implement the parameter_mapper
*interface.
*/
interface parameter_mapper_factory {

/**
*must return an parameter mapper with the given string. Accepts null as a return
*value to indicate that the router must make attempts to transform the name
*of an parameter class to match a method's parameter.
*/
	public function build_parameter_mapper(string $_name) : ?\srouter\interfaces\parameter_mapper;
}
