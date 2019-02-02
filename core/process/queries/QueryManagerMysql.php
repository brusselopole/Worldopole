<?php

include_once __DIR__ . '/QueryManagerMysqlRocketmap.php';
include_once __DIR__ . '/QueryManagerMysqlMonocleAlternate.php';


abstract class QueryManagerMysql extends QueryManager {

	protected $mysqli;

	protected function __construct() {
		$this->mysqli = new mysqli(SYS_DB_HOST, SYS_DB_USER, SYS_DB_PSWD, SYS_DB_NAME, SYS_DB_PORT);
		if ($this->mysqli->connect_error != '') {
			header('Location:'.HOST_URL.'offline.html');
			exit();
		}
		$this->mysqli->set_charset('utf8');

		if ($this->mysqli->connect_error != '') {
			header('Location:'.HOST_URL.'offline.html');
			exit();
		}
	}

	public function __destruct() {
		$this->mysqli->close();
	}

	/////////
	// Misc
	/////////

	public function getEcapedString($string) {
		return mysqli_real_escape_string($this->mysqli, $string);
	}

}