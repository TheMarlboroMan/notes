<?php
namespace notes\entities;

class user_session implements \sorm\interfaces\entity {

	public function get_id() :int {

		return $this->id;
	}

	public function get_user_id() :int {

		return $this->user_id;
	}

	public function get_created_at() :\DateTime {

		return $this->created_at;
	}

	public function get_last_activity_at() :\DateTime {

		return $this->last_activity_at;
	}

	public function get_token() :string {

		return $this->token;
	}

	public function set_id(int $_value) : \notes\entities\user_session {

		$this->id=$_value;
		return $this;
	}

	public function set_user_id(int $_value) : \notes\entities\user_session {

		$this->user_id=$_value;
		return $this;
	}

	public function set_created_at(\DateTime $_value) : \notes\entities\user_session {

		$this->created_at=$_value;
		return $this;
	}

	public function set_last_activity_at(\DateTime $_value) : \notes\entities\user_session {

		$this->last_activity_at=$_value;
		return $this;
	}

	public function set_token(string $_value) : \notes\entities\user_session {

		$this->token=$_value;
		return $this;
	}

	private int                 $id;
	private int                 $user_id;
	private \DateTime           $created_at;
	private \DateTime           $last_activity_at;
	private string              $token;
}