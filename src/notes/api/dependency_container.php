<?php
namespace notes\api;

class dependency_container {

	public function get_entity_manager() :\sorm\entity_manager {

	}

	public function get_user_session() : \sorm\entities\user_session {

	}

	public function get_logged_in_user() : \sorm\entities\user {

	}

	private ?\sorm\entity_manager           $entity_manager=null;
	private ?\notes\entities\user_session   $current_session=null;
	private ?\notes\entities\user           $logged_in_user=null;
}