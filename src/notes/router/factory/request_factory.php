<?php
namespace notes\router\factory;

class request_factory implements \srouter\interfaces\request_factory {

	public function build_request():\srouter\interfaces\request {

		if(null===$this->request) {

			$this->request=new \notes\router\request(
				\request\request_factory::from_apache_request()
			);
		}

		return $this->request;
	}

	private ?\notes\router\request $request=null;
}