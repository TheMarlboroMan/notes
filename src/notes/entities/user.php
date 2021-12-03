<?php
namespace notes\entities;

class user implements \sorm\interfaces\entity {

	public function get_id() : int {

		return $this->id;
	}

	public function get_created_at() : \DateTime {

		return $this->created_at;
	}

	public function get_last_login_at() : ?\DateTime {

		return $this->last_login_at;
	}

	public function get_username() : string {

		return $this->username;
	}

	public function get_pass() : string {

		return $this->pass;
	}

	public function set_id(int $_value) : \notes\entities\user {

		$this->id=$_value;
		return $this;
	}

	public function set_created_at(\DateTime $_value) : \notes\entities\user {

		$this->created_at=$_value;
		return $this;
	}

	public function set_last_login_at(?\DateTime $_value) : \notes\entities\user {

		$this->last_login_at=$_value;
		return $this;
	}

	public function set_username(string $_value) : \notes\entities\user {

		$this->username=$_value;
		return $this;
	}

	public function set_pass(string $_value) : \notes\entities\user {

		$this->pass=$_value;
		return $this;
	}


	private int                 $id;
	private \DateTime           $created_at;
	private ?\DateTime          $last_login_at=null;
	private string              $username;
	private string              $pass;
}