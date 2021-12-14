<?php
declare(strict_types=1);
namespace sorm\exception;

/**
*thrown when a value_mapper does not return a scalar value. All value mappers
*must return scalar values.
*/
class value_map extends exception {};
