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
}