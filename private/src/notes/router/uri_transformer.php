<?php
namespace notes\router;

class uri_transformer implements \srouter\interfaces\uri_transformer {

	public function __construct(
		string $_lead
	) {

		$this->lead=$_lead;
	}

	public function transform(
		string $_uri
	):string {

		$pieces=parse_url($_uri);
		return substr($pieces["path"], strlen($this->lead));
	}

	private string  $lead;
}