<?php
declare(strict_types=1);
namespace srouter\exception;

/**
*classic 400 bad request. I don't think the router itself throws it, but it is
*provided anyhow for the argument extractors.
*/

class bad_request extends exception {};
