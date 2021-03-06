<?php
spl_autoload_register(function(string $_classname) {

	$classfile=str_replace("\\", "/", $_classname).".php";

	foreach([__DIR__."/external/".$classfile, __DIR__."/".$classfile] as $path) {

		if(file_exists($path)) {

			require_once($path);
			return;
		}

	}

	die("ERROR: could not load $_classname");
});
