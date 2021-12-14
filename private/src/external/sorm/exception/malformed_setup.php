<?php
declare(strict_types=1);
namespace sorm\exception;

/**
*thrown when the map file is incorrectly mapped to the entity, review the
*map file and look for inconsistencies.
*/
class malformed_setup extends exception {}
