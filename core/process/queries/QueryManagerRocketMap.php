<?php

final class QueryManagerRocketMap extends QueryManagerMysql
{

	///////////
	// Tester
	///////////
	
	function testTotalPokemon() {
		$req = "SELECT COUNT(*) as total FROM pokemon";
		$result = $this->mysqli->query($req);
		if (!is_object($result)) {
			return 1;
		} else {
			$data = $result->fetch_object();
			$total = $data->total;
	
			if ($total == 0) {
				return 2;
			}
		}
		return 0;
	}
	
	function testTotalGyms() {
		$req = "SELECT COUNT(*) as total FROM gym";
		$result = $this->mysqli->query($req);
		if (!is_object($result)) {
			return 1;
		} else {
			$data = $result->fetch_object();
			$total = $data->total;
	
			if ($total == 0) {
				return 2;
			}
		}
		return 0;
	}
	
	function testTotalPokestops() {
		$req = "SELECT COUNT(*) as total FROM pokestop";
		$result = $this->mysqli->query($req);
		if (!is_object($result)) {
			return 1;
		} else {
			$data = $result->fetch_object();
			$total = $data->total;
	
			if ($total == 0) {
				return 2;
			}
		}
		return 0;
	}
	
	
	/////////////
	// Homepage
	/////////////
	
	function getTotalPokemon() {
		$req = "SELECT COUNT(*) AS total FROM pokemon WHERE disappear_time >= UTC_TIMESTAMP()";
		$result = $this->mysqli->query($req);
		$data = $result->fetch_object();
		return $data;
	}
	
	function getTotalLures() {
		$req = "SELECT COUNT(*) AS total FROM pokestop WHERE lure_expiration >= UTC_TIMESTAMP()";
		$result = $this->mysqli->query($req);
		$data = $result->fetch_object();
		return $data;
	}
	
	function getTotalGyms() {
		$req = "SELECT COUNT(DISTINCT(gym_id)) AS total FROM gym";
		$result = $this->mysqli->query($req);
		$data = $result->fetch_object();
		return $data;
	}
	
	function getTotalRaids() {
		$req = "SELECT COUNT(*) AS total FROM raid WHERE start <= UTC_TIMESTAMP AND  end >= UTC_TIMESTAMP()";
		$result = $this->mysqli->query($req);
		$data = $result->fetch_object();
		return $data;
	}
	
	function getTotalGymsForTeam($team_id) {
		$req = "SELECT COUNT(DISTINCT(gym_id)) AS total FROM gym WHERE team_id = '".$team_id."'";
		$result = $this->mysqli->query($req);
		$data = $result->fetch_object();
		return $data;
	}
	
	function getRecentAll() {
		$req = "SELECT DISTINCT pokemon_id, encounter_id, disappear_time, last_modified, (CONVERT_TZ(disappear_time, '+00:00', '".$this->time_offset."')) AS disappear_time_real,
					latitude, longitude, cp, individual_attack, individual_defense, individual_stamina
					FROM pokemon
					ORDER BY last_modified DESC
					LIMIT 0,12";
		$result = $this->mysqli->query($req);
		$data = array();
		if ($result->num_rows > 0) {
			while ($row = $result->fetch_object()) {
				$data[] = $row;
			}
		}
		return $data;
	}
	
	function getRecentMythic($mythic_pokemons) {
		$req = "SELECT DISTINCT pokemon_id, encounter_id, disappear_time, last_modified, (CONVERT_TZ(disappear_time, '+00:00', '".$this->time_offset."')) AS disappear_time_real,
					latitude, longitude, cp, individual_attack, individual_defense, individual_stamina
					FROM pokemon
					WHERE pokemon_id IN (".implode(",", $mythic_pokemons).")
					ORDER BY last_modified DESC
					LIMIT 0,12";
		$result = $this->mysqli->query($req);
		$data = array();
		if ($result->num_rows > 0) {
			while ($row = $result->fetch_object()) {
				$data[] = $row;
			}
		}
		return $data;
	}
	
	///////////////////
	// Single Pokemon
	///////////////////
	
	function getGymsProtectedByPokemon($pokemon_id) {
		$req = "SELECT COUNT(DISTINCT(gym_id)) AS total FROM gym WHERE guard_pokemon_id = '".$pokemon_id."'";
		$result = $this->mysqli->query($req);
		$data = $result->fetch_object();
		return $data;
	}
	
	function getPokemonLastSeen($pokemon_id) {
		$req = "SELECT disappear_time, (CONVERT_TZ(disappear_time, '+00:00', '".self::$time_offset."')) AS disappear_time_real, latitude, longitude
							FROM pokemon
							WHERE pokemon_id = '".$pokemon_id."'
							ORDER BY disappear_time DESC
							LIMIT 0,1";
		$result = $this->mysqli->query($req);
		$data = $result->fetch_object();
		return $data;
	}

	function getTop50Pokemon($pokemon_id, $best_order, $best_direction) {
		// Make it sortable; default sort: cp DESC
		$top_possible_sort = array('IV', 'cp', 'individual_attack', 'individual_defense', 'individual_stamina', 'move_1', 'move_2', 'disappear_time');
		$top_order = isset($_GET['order']) ? $_GET['order'] : '';
		$top_order_by = in_array($top_order, $top_possible_sort) ? $_GET['order'] : 'cp';
		$top_direction = isset($_GET['direction']) ? 'ASC' : 'DESC';
		$top_direction = !isset($_GET['order']) && !isset($_GET['direction']) ? 'DESC' : $top_direction;

		$req = "SELECT (CONVERT_TZ(disappear_time, '+00:00', '".$this->time_offset."')) AS distime, pokemon_id, disappear_time, latitude, longitude,
							cp, individual_attack, individual_defense, individual_stamina,
							ROUND(100*(individual_attack+individual_defense+individual_stamina)/45,1) AS IV, move_1, move_2, form
							FROM pokemon
							WHERE pokemon_id = '".$pokemon_id."' AND move_1 IS NOT NULL AND move_1 <> '0'
							GROUP BY encounter_id
							ORDER BY $top_order_by $top_direction, disappear_time DESC
							LIMIT 0,50";

		$result = $this->mysqli->query($req);
		$top = array();
		while ($data = $result->fetch_object()) {
			$top[] = $data;
		}
		return $top;
	}

	function getTop50Trainers($pokemon_id, $best_order, $best_direction) {
		$best_possible_sort = array('trainer_name', 'IV', 'cp', 'move_1', 'move_2', 'last_seen');
		$best_order = isset($_GET['order']) ? $_GET['order'] : '';
		$best_order_by = in_array($best_order, $best_possible_sort) ? $_GET['order'] : 'cp';
		$best_direction = isset($_GET['direction']) ? 'ASC' : 'DESC';
		$best_direction = !isset($_GET['order']) && !isset($_GET['direction']) ? 'DESC' : $best_direction;

		$trainer_blacklist = "";
		if (!empty(self::$config->system->trainer_blacklist)) {
			$trainer_blacklist = " AND trainer_name NOT IN ('".implode("','", self::$config->system->trainer_blacklist)."')";
		}

		$req = "SELECT trainer_name, ROUND(SUM(100*(iv_attack+iv_defense+iv_stamina)/45),1) AS IV, move_1, move_2, cp,
						DATE_FORMAT(last_seen, '%Y-%m-%d') AS lasttime, last_seen
						FROM gympokemon
						WHERE pokemon_id = '".$pokemon_id."'".$trainer_blacklist."
						GROUP BY pokemon_uid
						ORDER BY $best_order_by $best_direction, trainer_name ASC
						LIMIT 0,50";

		$result = $this->mysqli->query($req);
		$toptrainer = array();
		while ($data = $result->fetch_object()) {
			$toptrainer[] = $data;
		}
		return $toptrainer;
	}

	///////////////
	// Pokestops
	//////////////

	function getTotalPokestops() {
		$req = "SELECT COUNT(*) as total FROM pokestop";
		$result = $this->mysqli->query($req);
		$data = $result->fetch_object();
		return $data;
	}

	public  function getAllPokestops()
	{
		$req = "SELECT latitude, longitude, lure_expiration, UTC_TIMESTAMP() AS now, (CONVERT_TZ(lure_expiration, '+00:00', '".self::$time_offset."')) AS lure_expiration_real FROM pokestop";
		$result = $this->mysqli->query($req);
		$pokestops = array();
		while ($data = $result->fetch_object()) {
			$pokestops[] = $data;
		}
		return $pokestops;
	}


	/////////
	// Gyms
	/////////

	function getTeamGuardians($team_id) {
		$req = "SELECT COUNT(*) AS total, guard_pokemon_id FROM gym WHERE team_id = '".$team_id ."' GROUP BY guard_pokemon_id ORDER BY total DESC LIMIT 0,3";
		$result = $this->mysqli->query($req);
		$datas = array();
		while ($data = $result->fetch_object()) {
			$datas[] = $data;
		}
		return $datas;
	}

	function getOwnedAndPoints($team_id) {
		$req 	= "SELECT COUNT(DISTINCT(gym_id)) AS total, ROUND(AVG(total_cp),0) AS average_points FROM gym WHERE team_id = '".$team_id."'";
		$result = $this->mysqli->query($req);
		$data = $result->fetch_object();
		return $data;
	}

	function getAllGyms() {
		$req = "SELECT gym_id, team_id, latitude, longitude, (CONVERT_TZ(last_scanned, '+00:00', '".self::$time_offset."')) AS last_scanned, (6 - slots_available) AS level FROM gym";
		$result = $this->mysqli->query($req);
		$gyms = array();
		while ($data = $result->fetch_object()) {
			$gyms[] = $data;
		}
		return $gyms;
	}

	public function getGymData($gym_id) {
		$gym_id = $this->mysqli->real_escape_string($_GET['gym_id']);
		$req = "SELECT gymdetails.name AS name, gymdetails.description AS description, gymdetails.url AS url, gym.team_id AS team,
					(CONVERT_TZ(gym.last_scanned, '+00:00', '".self::$time_offset."')) AS last_scanned, gym.guard_pokemon_id AS guard_pokemon_id, gym.total_cp AS total_cp, (6 - gym.slots_available) AS level
					FROM gymdetails
					LEFT JOIN gym ON gym.gym_id = gymdetails.gym_id
					WHERE gym.gym_id='".$gym_id."'";
		$result = $this->mysqli->query($req);
		$data = $result->fetch_object();
		return $data;
	}

	public function getGymDefenders($gym_id) {
		$req = "SELECT DISTINCT gympokemon.pokemon_uid, pokemon_id, iv_attack, iv_defense, iv_stamina, MAX(cp) AS cp, gymmember.gym_id
					FROM gympokemon INNER JOIN gymmember ON gympokemon.pokemon_uid=gymmember.pokemon_uid
					GROUP BY gympokemon.pokemon_uid, pokemon_id, iv_attack, iv_defense, iv_stamina, gym_id
					HAVING gymmember.gym_id='".$gym_id."'
					ORDER BY cp DESC";
		$result = $this->mysqli->query($req);
		$defenders = array();
		while ($data = $result->fetch_object()) {
			$defenders[] = $data;
		}
		return $defenders;
	}


	///////////
	// Raids
	///////////

	public function getAllRaids($page) {
		$limit = " LIMIT ".($page * 10).",10";
		$req = "SELECT raid.gym_id, raid.level, raid.pokemon_id, raid.cp, raid.move_1, raid.move_2, CONVERT_TZ(raid.spawn, '+00:00', '".self::$time_offset."') AS spawn, CONVERT_TZ(raid.start, '+00:00', '".self::$time_offset."') AS start, CONVERT_TZ(raid.end, '+00:00', '".self::$time_offset."') AS end, CONVERT_TZ(raid.last_scanned, '+00:00', '".self::$time_offset."') AS last_scanned, gymdetails.name, gym.latitude, gym.longitude FROM raid
					JOIN gymdetails ON gymdetails.gym_id = raid.gym_id
					JOIN gym ON gym.gym_id = raid.gym_id
					WHERE raid.end > UTC_TIMESTAMP()
					ORDER BY raid.level DESC, raid.start".$limit;
		$result = $this->mysqli->query($req);
		$raids = array();
		while ($data = $result->fetch_object()) {
			$raids[] = $data;
		}
		return $raids;
	}
}