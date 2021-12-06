<?php
namespace notes\entities;

class note implements \sorm\interfaces\entity, \JsonSerializable {

	public function get_id() : int {

		return $this->id;
	}

	public function get_user_id() : int {

		return $this->user_id;
	}

	public function get_created_at() : \DateTime {

		return $this->created_at;
	}

	public function get_last_updated_at() : ?\DateTime {

		return $this->last_updated_at;
	}

	public function get_contents() : string {

		return $this->contents;
	}

	public function set_id(int    $_value) : \notes\entities\note {

		$this->id=$_value;
		return $this;
	}

	public function set_user_id(int    $_value) : \notes\entities\note {

		$this->user_id=$_value;
		return $this;
	}

	public function set_created_at(\DateTime $_value) : \notes\entities\note {

		$this->created_at=$_value;
		return $this;
	}

	public function set_last_updated_at(?\DateTime $_value) : \notes\entities\note {

		$this->last_updated_at=$_value;
		return $this;
	}

	public function set_contents(string $_value) : \notes\entities\note {

		$this->contents=$_value;
		return $this;
	}

	public function jsonSerialize() : array {

		return [
			"id" => $this->id,
			"user_id" => $this->user_id,
			"created_at" => $this->created_at,
			"last_updated_at" => $this->last_updated_at,
			"contents" => $this->contents
		];
	}

	private int                 $id;
	private int                 $user_id;
	private \DateTime           $created_at;
	private ?\DateTime          $last_updated_at=null;
	private string              $contents;
}