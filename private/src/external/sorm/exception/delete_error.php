<?php
declare(strict_types=1);
namespace sorm\exception;

/**
*thrown when anything inside the deletion process of the entity manager fails,
*with the sole exception of the "malformed_setup" exception, which would not
*be solvable without tikering with the code.
*/
class delete_error extends exception {};
