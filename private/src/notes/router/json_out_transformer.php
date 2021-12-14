<?php
namespace notes\router;

class json_out_transformer implements \srouter\interfaces\out_transformer {

	public function transform(
		\srouter\controller_response $_response
	) : \srouter\http_response {

		return new \srouter\http_response(
			$_response->get_status_code(),
			array_merge(
				[new \srouter\http_response_header("content-type", "application/json")],
				$_response->get_headers()
			),
			json_encode($_response->get_body())
		);
	}
}
