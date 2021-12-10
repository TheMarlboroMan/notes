<?php
namespace notes\api;

class config {

	public function __construct(
		string $_filename
	) {

		if(!file_exists($_filename)) {

			throw new \Exception("cannot open config file $_filename");
		}

		$contents=file_get_contents($_filename);
		if(false===$contents) {

			throw new \Exception("cannot read contents of $_filename");
		}

		$json=json_decode($contents);
		if(JSON_ERROR_NONE !== json_last_error()) {

			throw new \Exception("cannot decode $_filename");
		}

		$this->load($json);
	}

	public function load(
		\stdClass $_json
	) {

		foreach(
			[
				"uri_transformer_lead",
				"sorm_map_file",
				"app_log_file",
				"router_log_file",
				"secure_assets_dir",
				"db_login",
				"db_pass",
				"db_host",
				"db_schema",
				"verbose_errors"
			]
		as $key) {

			if(!property_exists($_json, $key)) {

				throw new \Exception("missing $key property in config file");
			}

			$this->$key=$_json->$key;
		}
	}

	public function get_uri_transformer_lead() : string {

		return $this->uri_transformer_lead;
	}

	public function get_sorm_map_file() : string {

		return $this->sorm_map_file;
	}

	public function get_app_log_file() : string {

		return $this->app_log_file;
	}

	public function get_router_log_file() : string {

		return $this->router_log_file;
	}

	public function get_secure_assets_dir() : string {

		return $this->secure_assets_dir;

	}

	public function get_db_login() : string {

		return $this->db_login;
	}

	public function get_db_pass() : string {

		return $this->db_pass;
	}

	public function get_db_host() : string {

		return $this->db_host;
	}

	public function get_db_schema() : string {

		return $this->db_schema;
	}

	public function is_verbose_errors() : bool {

		return $this->verbose_errors;
	}

	private string $uri_transformer_lead;
	private string $sorm_map_file;
	private string $app_log_file;
	private string $router_log_file;
	private string $secure_assets_dir;
	private string $db_login;
	private string $db_pass;
	private string $db_host;
	private string $db_schema;
	private bool   $verbose_errors;
}
