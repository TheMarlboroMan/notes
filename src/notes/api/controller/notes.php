<?php
namespace notes\api\controller;

class notes extends controller {

/**
*retrieves all notes corresponding to the currently logged in user.
*/
	public function get() : \srouter\controller_response {

	}

/**
*creates a new note
*/
	public function post(
		string $_contents
	) :\srouter\controller_response {

	}

/**
*provides a complete, non-mangled representation of a given note. The note is
*checked against the user for authorization.
*/
	public function info(
		int $_id
	) :\srouter\controller_response {

	}

/**
*updates a note. The note is checked against the user for authorization
*/
	public function patch(
		int $_id,
		string $_contents
	) :\srouter\controller_response {

	}

/**
*irrevocably deletes a note. The nete is checked agains the user for
*authorization
*/
	public function delete(
		int $_id
	) :\srouter\controller_response {

	}
}