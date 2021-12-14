<?php
declare(strict_types=1);
namespace sorm\exception;

/**
*exception thrown when the map loader fails: maybe the file does not exist,
*cannot be opened or is malformed.
*/
class map_loader_error extends exception {}
