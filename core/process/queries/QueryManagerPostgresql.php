<?php

include_once __DIR__ . '/QueryManagerPostgresqlMonocleAlternate.php';


abstract class QueryManagerPostgresql extends QueryManager {

	protected $db;

	protected function __construct() {

		$this->db = pg_connect("host=".SYS_DB_HOST." port=".SYS_DB_PORT." dbname=".SYS_DB_NAME." user=".SYS_DB_USER." password=".SYS_DB_PSWD);

		if ($this->db === false) {
			header('Location:' . HOST_URL . 'offline.html');
			exit();
		}
	}

	public function __destruct() {
		pg_close($this->db);
	}

	/////////
	// Misc
	/////////

	public function getEcapedString($string) {
		return pg_escape_string($this->db, $string);
	}

}