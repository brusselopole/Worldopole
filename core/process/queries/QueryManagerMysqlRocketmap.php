<?php

final class QueryManagerMysqlRocketmap extends QueryManagerMysql {

	public function __construct() {
		parent::__construct();
	}

	public function __destruct() {
		parent::__destruct();
	}

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
		$req = "SELECT DISTINCT pokemon_id, encounter_id, disappear_time, last_modified, (CONVERT_TZ(disappear_time, '+00:00', '".self::$time_offset."')) AS disappear_time_real,
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
		$req = "SELECT DISTINCT pokemon_id, encounter_id, disappear_time, last_modified, (CONVERT_TZ(disappear_time, '+00:00', '".self::$time_offset."')) AS disappear_time_real,
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

	function getTop50Pokemon($pokemon_id, $top_order_by, $top_direction) {
		$req = "SELECT (CONVERT_TZ(disappear_time, '+00:00', '".self::$time_offset."')) AS distime, pokemon_id, disappear_time, latitude, longitude,
							cp, individual_attack, individual_defense, individual_stamina,
							ROUND(100*(individual_attack+individual_defense+individual_stamina)/45,1) AS IV, move_1, move_2, form
							FROM pokemon
							WHERE pokemon_id = '".$pokemon_id."' AND move_1 IS NOT NULL AND move_1 <> '0'
							ORDER BY $top_order_by $top_direction, disappear_time DESC
							LIMIT 0,50";

		$result = $this->mysqli->query($req);
		$top = array();
		while ($data = $result->fetch_object()) {
			$top[] = $data;
		}
		return $top;
	}

	function getTop50Trainers($pokemon_id, $best_order_by, $best_direction) {
		$trainer_blacklist = "";
		if (!empty(self::$config->system->trainer_blacklist)) {
			$trainer_blacklist = " AND trainer_name NOT IN ('".implode("','", self::$config->system->trainer_blacklist)."')";
		}

		$req = "SELECT trainer_name, ROUND(SUM(100*(iv_attack+iv_defense+iv_stamina)/45),1) AS IV, move_1, move_2, cp,
						DATE_FORMAT(last_seen, '%Y-%m-%d') AS lasttime, last_seen
						FROM gympokemon
						WHERE pokemon_id = '".$pokemon_id."'".$trainer_blacklist."
						ORDER BY $best_order_by $best_direction, trainer_name ASC
						LIMIT 0,50";

		$result = $this->mysqli->query($req);
		$toptrainer = array();
		while ($data = $result->fetch_object()) {
			$toptrainer[] = $data;
		}
		return $toptrainer;
	}

	public function getPokemonHeatmap($pokemon_id, $start, $end) {
		$where = " WHERE pokemon_id = ".$pokemon_id." "
			. "AND disappear_time BETWEEN '".$start."' AND '".$end."'";
		$req 		= "SELECT latitude, longitude FROM pokemon".$where." ORDER BY disappear_time DESC LIMIT 10000";
		$result = $this->mysqli->query($req);
		$points = array();
		while ($data = $result->fetch_object()) {
			$points[] = $data;
		}
		return $points;
	}

	public function getPokemonGraph($pokemon_id) {
		$req = "SELECT COUNT(*) AS total,
					HOUR(CONVERT_TZ(disappear_time, '+00:00', '".self::$time_offset."')) AS disappear_hour
					FROM (SELECT disappear_time FROM pokemon WHERE pokemon_id = '".$pokemon_id."' ORDER BY disappear_time LIMIT 100000) AS pokemonFiltered
					GROUP BY disappear_hour
					ORDER BY disappear_hour";
		$result = $this->mysqli->query($req);
		$array = array_fill(0, 24, 0);
		while ($result && $data = $result->fetch_object()) {
			$array[$data->disappear_hour] = $data->total;
		}
		// shift array because AM/PM starts at 1AM not 0:00
		$array[] = $array[0];
		array_shift($array);
		return $array;
	}

	public function getPokemonLive($pokemon_id, $ivMin, $ivMax, $inmap_pokemons) {
		$inmap_pkms_filter = "";
		$where = " WHERE disappear_time >= UTC_TIMESTAMP() AND pokemon_id = ".$pokemon_id;

		$reqTestIv = "SELECT MAX(individual_attack) AS iv FROM pokemon ".$where;
		$resultTestIv = $this->mysqli->query($reqTestIv);
		$testIv = $resultTestIv->fetch_object();
		if (!is_null($inmap_pokemons) && ($inmap_pokemons != "")) {
			foreach ($inmap_pokemons as $inmap) {
				$inmap_pkms_filter .= "'".$inmap."',";
			}
			$inmap_pkms_filter = rtrim($inmap_pkms_filter, ",");
			$where .= " AND encounter_id NOT IN (".$inmap_pkms_filter.") ";
		}
		if ($testIv->iv != null && !is_null($ivMin) && ($ivMin != "")) {
			$where .= " AND ((100/45)*(individual_attack+individual_defense+individual_stamina)) >= (".$ivMin.") ";
		}
		if ($testIv->iv != null && !is_null($ivMax) && ($ivMax != "")) {
			$where .= " AND ((100/45)*(individual_attack+individual_defense+individual_stamina)) <= (".$ivMax.") ";
		}
		$req = "SELECT pokemon_id, encounter_id, latitude, longitude, disappear_time,
						(CONVERT_TZ(disappear_time, '+00:00', '".self::$time_offset."')) AS disappear_time_real,
						individual_attack, individual_defense, individual_stamina, move_1, move_2
						FROM pokemon ".$where."
						ORDER BY disappear_time DESC
						LIMIT 5000";
		$result = $this->mysqli->query($req);
		$spawns = array();
		while ($data = $result->fetch_object()) {
			$spawns[] = $data;
		}
		return $spawns;
	}

	public function getPokemonSliderMinMax() {
		$req = "SELECT MIN(disappear_time) AS min, MAX(disappear_time) AS max FROM pokemon";
		$result = $this->mysqli->query($req);
		$data = $result->fetch_object();
		return $data;
	}

	public function getMapsCoords() {
		$req = "SELECT MAX(latitude) AS max_latitude, MIN(latitude) AS min_latitude, MAX(longitude) AS max_longitude, MIN(longitude) as min_longitude FROM spawnpoint";
		$result = $this->mysqli->query($req);
		$data = $result->fetch_object();
		return $data;
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

	public function getAllPokestops() {
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



	////////////////
	// Gym History
	////////////////

	public function getGymHistories($gym_name, $team, $page, $ranking) {
		$where = '';
		if (isset($gym_name) && $gym_name != '') {
			$where = " WHERE name LIKE '%".$gym_name."%'";
		}
		if (isset($team) && $team != '') {
			$where .= ($where == "" ? " WHERE" : " AND")." team_id = ".$team;
		}
		switch ($ranking) {
			case 1:
				$order = " ORDER BY name, last_modified DESC";
				break;
			case 2:
				$order = " ORDER BY total_cp DESC, last_modified DESC";
				break;
			default:
				$order = " ORDER BY last_modified DESC, name";
		}

		$limit = " LIMIT ".($page * 10).",10";

		$req = "SELECT gymdetails.gym_id, name, team_id, total_cp, (6 - slots_available) as pokemon_count, (CONVERT_TZ(last_modified, '+00:00', '" . self::$time_offset . "')) as last_modified
				FROM gymdetails
				LEFT JOIN gym
				ON gymdetails.gym_id = gym.gym_id
				".$where.$order.$limit;

		$result = $this->mysqli->query($req);
		$gym_history = array();
		while ($data = $result->fetch_object()) {
			$gym_history[] = $data;
		}
		return $gym_history;
	}

	public function getGymHistoriesPokemon($gym_id) {
		$req = "SELECT DISTINCT gymmember.pokemon_uid, pokemon_id, cp, trainer_name
					FROM gymmember
					LEFT JOIN gympokemon
					ON gymmember.pokemon_uid = gympokemon.pokemon_uid
					WHERE gymmember.gym_id = '". $gym_id ."'
					ORDER BY deployment_time";
		$result = $this->mysqli->query($req);
		$pokemons = array();
		while ($data = $result->fetch_object()) {
			$pokemons[] = $data;
		}
		return $pokemons;
	}

	public function getHistoryForGym($page, $gym_id) {
		if (isset(self::$config->system->gymhistory_hide_cp_changes) && self::$config->system->gymhistory_hide_cp_changes === true) {
			$pageSize = 25;
		} else {
			$pageSize = 10;
		}
		$req = "SELECT gym_id, team_id, total_cp, pokemon_uids, pokemon_count, (CONVERT_TZ(last_modified, '+00:00', '".self::$time_offset."')) as last_modified
					FROM gymhistory
					WHERE gym_id='".$gym_id."'
					ORDER BY last_modified DESC
					LIMIT ".($page * $pageSize).",".($pageSize+1);
		$result = $this->mysqli->query($req);
		$history = array();
		$count = 0;
		while ($data = $result->fetch_object()) {
			$count++;
			$pkm = array();
			if ($data->total_cp == 0) {
				$data->pokemon_uids = '';
				$data->pokemon_count = 0;
			}
			if ($data->pokemon_uids != '') {
				$pkm_uids = explode(',', $data->pokemon_uids);
				$pkm = $this->getHistoryForGymPokemon($pkm_uids);
			}
			$data->pokemon = $pkm;
			$history[] = $data;
		}
		if ($count !== ($pageSize + 1)) {
			$last_page = true;
		} else {
			$last_page = false;
		}
		return array("last_page" => $last_page, "data" => $history);
	}

	private function getHistoryForGymPokemon($pkm_uids) {
		$req = "SELECT DISTINCT pokemon_uid, pokemon_id, cp, trainer_name
								FROM gympokemon
								WHERE pokemon_uid IN ('".implode("','", $pkm_uids)."')
								ORDER BY FIND_IN_SET(pokemon_uid, '".implode(",", $pkm_uids)."')";
		$result = $this->mysqli->query($req);
		$pokemons = array();
		while ($data = $result->fetch_object()) {
			$pokemons[$data->pokemon_uid] = $data;
		}
		return $pokemons;
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


	//////////////
	// Trainers
	//////////////

	public function getTrainers($trainer_name, $team, $page, $ranking) {
		$trainers = $this->getTrainerData($trainer_name, $team, $page, $ranking);
		foreach ($trainers as $trainer) {

			$trainer->rank = $this->getTrainerLevelRating($trainer->level)->rank;

			$active_gyms = 0;
			$pkmCount = 0;

			$trainer->pokemons = array();
			$active_pokemon = $this->getTrainerActivePokemon($trainer->name);
			foreach ($active_pokemon as $pokemon) {
				$active_gyms++;
				$trainer->pokemons[$pkmCount++] = $pokemon;
			}

			$inactive_pokemon = $this->getTrainerInactivePokemon($trainer->name);
			foreach ($inactive_pokemon as $pokemon) {
				$trainer->pokemons[$pkmCount++] = $pokemon;
			}
			$trainer->gyms = "".$active_gyms;
		}
		return $trainers;
	}

	public function getTrainerLevelCount($team_id) {
		$req = "SELECT level, count(level) AS count FROM trainer WHERE team = '".$team_id."'";
		if (!empty(self::$config->system->trainer_blacklist)) {
			$req .= " AND name NOT IN ('".implode("','", self::$config->system->trainer_blacklist)."')";
		}
		$req .= " GROUP BY level";
		$result = $this->mysqli->query($req);
		$levelData = array();
		while ($data = $result->fetch_object()) {
			$levelData[$data['level']] = $data['count'];
		}
		for ($i = 5; $i <= 40; $i++) {
			if (!isset($levelData[$i])) {
				$levelData[$i] = 0;
			}
		}
		# sort array again
		ksort($levelData);
		return $levelData;
	}

	private function getTrainerData($trainer_name, $team, $page, $ranking) {
		$where = "";

		if (!empty(self::$config->system->trainer_blacklist)) {
			$where .= ($where == "" ? " HAVING" : " AND")." name NOT IN ('".implode("','", self::$config->system->trainer_blacklist)."')";
		}
		if ($trainer_name != "") {
			$where = " HAVING name LIKE '%".$trainer_name."%'";
		}
		if ($team != 0) {
			$where .= ($where == "" ? " HAVING" : " AND")." team = ".$team;
		}
		switch ($ranking) {
			case 1:
				$order = " ORDER BY active DESC, level DESC";
				break;
			case 2:
				$order = " ORDER BY maxCp DESC, level DESC";
				break;
			default:
				$order = " ORDER BY level DESC, active DESC";
		}
		$order .= ", last_seen DESC, name ";
		$limit = " LIMIT ".($page * 10).",10 ";
		$req = "SELECT trainer.*, COUNT(actives_pokemons.trainer_name) AS active, max(actives_pokemons.cp) AS maxCp
				FROM trainer
				LEFT JOIN (SELECT DISTINCT gympokemon.pokemon_id, gympokemon.pokemon_uid, gympokemon.trainer_name, gympokemon.cp, DATEDIFF(UTC_TIMESTAMP(), gympokemon.last_seen) AS last_scanned
				FROM gympokemon
				INNER JOIN (SELECT gymmember.pokemon_uid, gymmember.gym_id FROM gymmember GROUP BY gymmember.pokemon_uid, gymmember.gym_id HAVING gymmember.gym_id <> '') AS filtered_gymmember
				ON gympokemon.pokemon_uid = filtered_gymmember.pokemon_uid) AS actives_pokemons ON actives_pokemons.trainer_name = trainer.name
				GROUP BY trainer.name " . $where . $order . $limit;

		$result = $this->mysqli->query($req);
		$trainers = array();
		while ($data = $result->fetch_object()) {
			$data->last_seen = date("Y-m-d", strtotime($data->last_seen));
			$trainers[$data->name] = $data;
		}
		return $trainers;
	}

	private function getTrainerLevelRating($level) {
		$req = "SELECT COUNT(1) AS rank FROM trainer WHERE level = ".$level;
		if (!empty(self::$config->system->trainer_blacklist)) {
			$req .= " AND name NOT IN ('".implode("','", self::$config->system->trainer_blacklist)."')";
		}
		$result = $this->mysqli->query($req);
		$data = $result->fetch_object();
		return $data;
	}

	private function getTrainerActivePokemon($trainer_name){
		$req = "(SELECT DISTINCT gympokemon.pokemon_id, gympokemon.pokemon_uid, gympokemon.cp, DATEDIFF(UTC_TIMESTAMP(), gympokemon.last_seen) AS last_scanned, gympokemon.trainer_name, gympokemon.iv_defense, gympokemon.iv_stamina, gympokemon.iv_attack, filtered_gymmember.gym_id, CONVERT_TZ(filtered_gymmember.deployment_time, '+00:00', '".self::$time_offset."') as deployment_time, '1' AS active
					FROM gympokemon INNER JOIN
					(SELECT gymmember.pokemon_uid, gymmember.gym_id, gymmember.deployment_time FROM gymmember GROUP BY gymmember.pokemon_uid, gymmember.deployment_time, gymmember.gym_id HAVING gymmember.gym_id <> '') AS filtered_gymmember
					ON gympokemon.pokemon_uid = filtered_gymmember.pokemon_uid
					WHERE gympokemon.trainer_name='".$trainer_name."'
					ORDER BY gympokemon.cp DESC)";
		$result = $this->mysqli->query($req);
		$pokemons = array();
		while ($data = $result->fetch_object()) {
			$pokemons[] = $data;
		}
		return $pokemons;
	}

	private function getTrainerInactivePokemon($trainer_name){
		$req = "(SELECT DISTINCT gympokemon.pokemon_id, gympokemon.pokemon_uid, gympokemon.cp, DATEDIFF(UTC_TIMESTAMP(), gympokemon.last_seen) AS last_scanned, gympokemon.trainer_name, gympokemon.iv_defense, gympokemon.iv_stamina, gympokemon.iv_attack, null AS gym_id, CONVERT_TZ(filtered_gymmember.deployment_time, '+00:00', '".self::$time_offset."') as deployment_time, '0' AS active
					FROM gympokemon LEFT JOIN
					(SELECT * FROM gymmember HAVING gymmember.gym_id <> '') AS filtered_gymmember
					ON gympokemon.pokemon_uid = filtered_gymmember.pokemon_uid
					WHERE filtered_gymmember.pokemon_uid IS NULL AND gympokemon.trainer_name='".$trainer_name."'
					ORDER BY gympokemon.cp DESC)";
		$result = $this->mysqli->query($req);
		$pokemons = array();
		while ($data = $result->fetch_object()) {
			$pokemons[] = $data;
		}
		return $pokemons;
	}


	/////////
	// Cron
	/////////

	public function getPokemonCountsActive() {
		$req = "SELECT pokemon_id, COUNT(*) as total FROM pokemon WHERE disappear_time >= UTC_TIMESTAMP() GROUP BY pokemon_id";
		$result = $this->mysqli->query($req);
		$counts = array();
		while ($data = $result->fetch_object()) {
			$counts[$data->pokemon_id] = $data->total;
		}
		return $counts;
	}

	public function getPokemonCountsLastDay() {
		$req = "SELECT pokemon_id, COUNT(*) AS spawns_last_day
					FROM pokemon
					WHERE disappear_time >= (SELECT MAX(disappear_time) FROM pokemon) - INTERVAL 1 DAY
					GROUP BY pokemon_id
				  	ORDER BY pokemon_id ASC";
		$result = $this->mysqli->query($req);
		$counts = array();
		while ($data = $result->fetch_object()) {
			$counts[$data->pokemon_id] = $data->spawns_last_day;
		}
		return $counts;
	}

	public function getPokemonSinceLastUpdate($pokemon_id, $last_update) {
		$where = "WHERE p.pokemon_id = '".$pokemon_id."' AND (UNIX_TIMESTAMP(p.disappear_time) - (LENGTH(s.kind) - LENGTH( REPLACE ( kind, \"s\", \"\") )) * 900) > '".$last_update."'";
		$req = "SELECT count, (UNIX_TIMESTAMP(p.disappear_time) - (LENGTH(s.kind) - LENGTH( REPLACE ( kind, \"s\", \"\") )) * 900) as last_timestamp, (CONVERT_TZ(p.disappear_time, '+00:00', '".self::$time_offset."')) AS disappear_time_real, p.latitude, p.longitude 
				FROM pokemon p
				JOIN spawnpoint s ON p.spawnpoint_id = s.id
				JOIN (SELECT count(*) as count
                    FROM pokemon p
                    JOIN spawnpoint s ON p.spawnpoint_id = s.id
                    " . $where."
                ) x
				" . $where . "
				ORDER BY last_timestamp DESC
                LIMIT 0,1";
		$result = $this->mysqli->query($req);
		$data = $result->fetch_object();
		return $data;
	}

	public function getRaidsSinceLastUpdate($pokemon_id, $last_update) {
		$where = "WHERE pokemon_id = '".$pokemon_id."' AND UNIX_TIMESTAMP(start) > '".$last_update."'";
		$req = "SELECT UNIX_TIMESTAMP(start) as start_timestamp, end, (CONVERT_TZ(end, '+00:00', '".self::$time_offset."')) AS end_time_real, latitude, longitude, count
                FROM raid r
                JOIN gym g
                JOIN (SELECT count(*) as count
                    FROM raid
                    " . $where."
                ) x
                ON r.gym_id = g.gym_id
                " . $where."
                ORDER BY start DESC
                LIMIT 0,1";
		$result = $this->mysqli->query($req);
		$data = $result->fetch_object();
		return $data;
	}

	public function getCaptchaCount() {
		$req = "SELECT SUM(accounts_captcha) AS total FROM mainworker";
		$result = $this->mysqli->query($req);
		$data = $result->fetch_object();
		return $data;
	}

	public function getNestData() {
		$pokemon_exclude_sql = "";
		if (!empty(self::$config->system->nest_exclude_pokemon)) {
			$pokemon_exclude_sql = "AND p.pokemon_id NOT IN (".implode(",", self::$config->system->nest_exclude_pokemon).")";
		}
		$req = "SELECT p.pokemon_id, MAX(p.latitude) AS latitude, MAX(p.longitude) AS longitude, count(p.pokemon_id) AS total_pokemon, MAX(UNIX_TIMESTAMP(s.latest_seen)) as latest_seen, (LENGTH(s.kind) - LENGTH( REPLACE ( MAX(kind), \"s\", \"\") )) * 900 AS duration
			          FROM pokemon p 
			          INNER JOIN spawnpoint s ON (p.spawnpoint_id = s.id) 
			          WHERE p.disappear_time > UTC_TIMESTAMP() - INTERVAL 24 HOUR 
			          " . $pokemon_exclude_sql . " 
			          GROUP BY p.spawnpoint_id, p.pokemon_id 
			          HAVING COUNT(p.pokemon_id) >= 6 
			          ORDER BY p.pokemon_id";
		$result = $this->mysqli->query($req);
		$nests = array();
		while ($data = $result->fetch_object()) {
			$nests[] = $data;
		}
		return $nests;
	}

}