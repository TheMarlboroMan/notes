<?php
spl_autoload_register(function(string $_classname) {

	$classfile=str_replace("\\", "/", $_clasname).".php";

	foreach([__DIR__."/external/".$classfile, $classfile] as $path) {

		if(file_exists($path)) {

			require_once($path);
			return;
		}

	}

	die("ERROR: could not load $_classname");
});
