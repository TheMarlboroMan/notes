<?php
declare(strict_types=1);
namespace sorm\exception;

/***
*thrown when there's a failure (bug) in the ORM code and there's nothing the
*implementor can do about it.
*/

class internal extends exception {};
