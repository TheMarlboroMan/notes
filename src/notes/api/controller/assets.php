<?php
namespace notes\api\controller;

class assets extends controller {

	private const log_module="assets";

	public function get_js(
		string $_tok
	) {

		return $this->get($_tok, "app.js");
	}

	public function get_css(
		string $_tok
	) {

		return $this->get($_tok, "app.css");
	}

	public function get_html(
		string $_tok
	) {

		return $this->get($_tok, "app.html");
	}

	private function get(
		string $_tok,
		string $_asset
	) : \srouter\controller_response {

		$entity_manager=$this->dc->get_entity_manager();
		$session=$entity_manager->fetch_one(
			\notes\entities\user_session::class,
			$entity_manager->get_fetch_builder()->str_begins_by_cs("token", $_tok)
		);

		if(null===$session) {

			$this->dc->get_logger()->notice("token starting by $_tok not found", self::log_module);
			return new \srouter\controller_response(401, [], "unauthorized");
		}

		if(null!==$session->get_last_activity_at()) {

			$threshold=clone $session->get_last_activity_at();
			$threshold->modify("+1 hour");

			$now=new \DateTime();
			if($threshold < $now) {

				$this->dc->get_logger()->notice("token starting by $_tok expired", self::log_module);
				return new \srouter\controller_response(401, [], "unauthorized");
			}
		}

		$filepath=$this->dc->get_config()->get_secure_assets_dir()."/".$_asset;
		$contents=file_get_contents($filepath);
		if(false===$contents) {

			$this->dc->get_logger()->error("unable to locate $_asset file in $filepath", self::log_module);
			return new \srouter\controller_response(500, [], "internal server error");

		}

		return new \srouter\controller_response(200, [], $contents);
	}
}
