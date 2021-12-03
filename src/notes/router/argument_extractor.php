<?php
namespace notes\router;
/**
*in this example everything comes in as json, so this will be easy.
*/
class argument_extractor implements \srouter\interfaces\argument_extractor {

	public function __construct() {

		$this->argument_maker=new \srouter\argument_maker();
	}

	public function extract(
		\srouter\parameter $_parameter,
		\srouter\interfaces\request $_request,
		array $_uri_args
	) {

		$name=$_parameter->get_name();

		//if the argument comes in the URI, just be done with that.
		if($_parameter->get_source()==="uri") {

			$value=$this->argument_maker->find_uri_argument($name, $_uri_args);
			return $this->argument_maker->make_argument($value, $_parameter);
		}

		//same if it comes from the query string...
		if($_parameter->get_source()==="query") {

			$value=$this->argument_maker->find_query_argument($name, $_request);
			return $this->argument_maker->make_argument($value, $_parameter);
		}

		//else, attempt to locate it in the body.
		if($_parameter->get_source()==="json") {

			$value=null;
			if(null===$this->json_doc) {

				if($_request->is_multipart()) {

					throw new \srouter\exception\bad_request("argument extractor refuses multipart requests");
				}

				$this->json_doc=json_decode($_request->get_body());
				if(JSON_ERROR_NONE !== json_last_error()) {

					throw new \srouter\exception\bad_request("could not decode json request body: ".json_last_error_msg());
				}
			}

			$value=property_exists($this->json_doc, $name)
				? $this->json_doc->$name
				: null;

			return $this->argument_maker->make_argument($value, $_parameter);
		}

		throw new \Exception("invalid argument type for argument extractor");
	}

	private \srouter\argument_maker $argument_maker;
	private ?\stdClass               $json_doc=null;

}
