<?php
namespace notes\api\controller;

class notes extends controller {

/**
*retrieves all notes corresponding to the currently logged in user.
*/
	public function get() : \srouter\controller_response {

		$entity_manager=$this->dc->get_entity_manager();
		$fb=$entity_manager->get_fetch_builder();
		$notes=$entity_manager->fetch(
			\notes\entities\note::class,
			$fb->equals(
				"user_id", $this->dc->get_logged_in_user()->get_id()
			),
			$fb->order_by(
				$fb->order("last_updated_at", \sorm\fetch::order_desc)
			)
		)->all();

		//mangle the text length, for fun...
//		$notes=array_map(
//			function(\notes\entities\note $_note) :\notes\entities\note {
//				return $_note->set_contents(substr($_note->get_contents(), 0, 40));
//			},
//			$notes
//		);

		return new \srouter\controller_response(
			200,
			[],
			$notes
		);
	}

/**
*creates a new note
*/
	public function post(
		string $_contents,
		int $_color_id
	) :\srouter\controller_response {

		if(!strlen($_contents)) {

			return new \srouter\controller_response(
				400,
				[],
				"contents cannot be empty"
			);
		}

		$entity_manager=$this->dc->get_entity_manager();
		$note=$entity_manager->create(
			$entity_manager->build(
				\notes\entities\note::class
			)->set_user_id($this->dc->get_logged_in_user()->get_id())
			->set_created_at(new \DateTime())
			->set_contents($_contents)
			->set_color_id($_color_id)
		);

		return new \srouter\controller_response(
			201,
			//TODO: actually, should be the full app path, right??
			[new \srouter\http_response_header("location", "api/notes/".$note->get_id())],
			"created"
		);
	}

/**
*provides a complete, non-mangled representation of a given note. The note is
*checked against the user for authorization.
*/
	public function info(
		int $_id
	) :\srouter\controller_response {

		//We know the note exists: this passed the user_owns_note_authorizer,
		//which checks for that!
		$note=$this->dc->get_entity_manager()->fetch_by_id(
			\notes\entities\note::class,
			$_id
		);

		return new \srouter\controller_response(
			200,
			[],
			$note
		);
	}

/**
*updates a note. The note is checked against the user for authorization
*/
	public function patch(
		int $_id,
		string $_contents,
		int $_color_id
	) :\srouter\controller_response {

		if(!strlen($_contents)) {

			return new \srouter\controller_response(
				400,
				[],
				"contents cannot be empty"
			);
		}

		//we know the note exists because of the ownership authorizer.
		$entity_manager=$this->dc->get_entity_manager();
		$note=$entity_manager->fetch_by_id(
			\notes\entities\note::class,
			$_id
		);

		$entity_manager->update(
			$note->set_contents($_contents)
				->set_color_id($_color_id)
				->set_last_updated_at(new \DateTime())
		);

		return new \srouter\controller_response(
			200,
			[],
			"patched"
		);
	}

/**
*irrevocably deletes a note. The nete is checked agains the user for
*authorization
*/
	public function delete(
		int $_id
	) :\srouter\controller_response {

		//we know the note exists because of the ownership authorizer, let
		//us just delete it...
		$entity_manager=$this->dc->get_entity_manager();
		$note=$entity_manager->fetch_by_id(
			\notes\entities\note::class,
			$_id
		);

		$entity_manager->delete($note);

		return new \srouter\controller_response(
			200,
			[],
			"deleted"
		);
	}
}
