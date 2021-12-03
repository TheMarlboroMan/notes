<?php
namespace notes\api;

class config {

	public function __construct(
		string $_uri_transformer_lead
	) {

		$this->uri_transformer_lead=$_uri_transformer_lead;
	}

	public function get_uri_transformer_lead() : string {

		return $this->uri_transformer_lead;
	}

	private string $uri_transformer_lead;
}