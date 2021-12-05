<?php
namespace notes\api;

class dependency_container {

	public function __construct(
		\notes\api\config $_config
	) {

		$this->config=$_config;
	}

	public function get_config() :\notes\api\config {

		return $this->config;
	}

	public function get_entity_manager() :\sorm\entity_manager {

		if(null===$this->entity_manager) {

			$this->entity_manager=new \sorm\entity_manager(
				$this->config->get_sorm_map_file(),
				$this->get_logger(),
				new \sorm\pdo_storage_interface($this->get_pdo()),
				new \notes\orm\factory\entity_factory(),
				new \notes\orm\entity_property_mapper(),
				null,
				null,
				new \notes\orm\entity_name_mapper()
			);
		}

		return $this->entity_manager;
	}

	public function get_logger() : \log\logger_interface {

		if(null===$this->logger) {

			$logfile=$this->config->get_app_log_file();
			$this->logger=!strlen($logfile)
				? new \log\void_logger()
				: new \log\file_logger(
					new \log\default_formatter(),
					$logfile
				);
		}

		return $this->logger;
	}

	public function get_user_session() : \sorm\entities\user_session {

	}

	public function get_logged_in_user() : \sorm\entities\user {

	}

	private function get_pdo() :\PDO {

		if(null===$this->pdo) {

			$dsn="mysql:dbname=".$this->config->get_db_schema().";host=".$this->config->get_db_host().";charset=utf8";

			$this->pdo=new \PDO(
				$dsn,
				$this->config->get_db_login(),
				$this->config->get_db_pass()
			);

			$this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
			$this->pdo->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
		}

		return $this->pdo;
	}

	private ?\PDO                           $pdo=null;
	private \notes\api\config               $config;
	private ?\log\logger_interface          $logger=null;
	private ?\sorm\entity_manager           $entity_manager=null;
	private ?\notes\entities\user_session   $current_session=null;
	private ?\notes\entities\user           $logged_in_user=null;
}