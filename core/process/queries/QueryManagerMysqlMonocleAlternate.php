<?php

class QueryManagerMysqlMonocleAlternate extends QueryManagerMysql {

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
		$req = "SELECT COUNT(*) as total FROM sightings";
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
		$req = "SELECT COUNT(*) as total FROM forts";
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
		$req = "SELECT COUNT(*) as total FROM pokestops";
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
		$req = "SELECT COUNT(*) AS total FROM sightings WHERE expire_timestamp >= UNIX_TIMESTAMP()";
		$result = $this->mysqli->query($req);
		$data = $result->fetch_object();
		return $data;
	}

	function getTotalLures() {
		$data = (object) array("total" => 0);
		return $data;
	}

	function getTotalGyms() {
		$req = "SELECT COUNT(*) AS total FROM forts";
		$result = $this->mysqli->query($req);
		$data = $result->fetch_object();
		return $data;
	}

	function getTotalRaids() {
		$req = "SELECT COUNT(*) AS total FROM raids WHERE time_battle <= UNIX_TIMESTAMP() AND time_end >= UNIX_TIMESTAMP()";
		$result = $this->mysqli->query($req);
		$data = $result->fetch_object();
		return $data;
	}


	function getTotalGymsForTeam($team_id) {
		$req = "SELECT COUNT(*) AS total 
					FROM forts f
					LEFT JOIN fort_sightings fs ON (fs.fort_id = f.id AND fs.last_modified = (SELECT MAX(last_modified) FROM fort_sightings fs2 WHERE fs2.fort_id=f.id))
					WHERE team = '$team_id'";
		$result = $this->mysqli->query($req);
		$data = $result->fetch_object();
		return $data;
	}

	function getRecentAll() {
		$req = "SELECT DISTINCT pokemon_id, encounter_id, FROM_UNIXTIME(expire_timestamp) AS disappear_time, FROM_UNIXTIME(updated) AS last_modified, FROM_UNIXTIME(expire_timestamp) AS disappear_time_real,
              lat AS latitude, lon AS longitude, cp, atk_iv AS individual_attack, def_iv AS individual_defense, sta_iv AS individual_stamina
              FROM sightings
              ORDER BY updated DESC
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

	function getRecentMythic($mythic_pokemon) {
		$req = "SELECT pokemon_id, encounter_id, FROM_UNIXTIME(expire_timestamp) AS disappear_time, FROM_UNIXTIME(updated) AS last_modified, FROM_UNIXTIME(expire_timestamp) AS disappear_time_real,
                lat AS latitude, lon AS longitude, cp, atk_iv AS individual_attack, def_iv AS individual_defense, sta_iv AS individual_stamina
                FROM sightings
                WHERE pokemon_id IN (".implode(",", $mythic_pokemon).")
                ORDER BY updated DESC
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
		$req = "SELECT COUNT(f.id) AS total 
					FROM forts f
					LEFT JOIN fort_sightings fs ON (fs.fort_id = f.id AND fs.last_modified = (SELECT MAX(last_modified) FROM fort_sightings fs2 WHERE fs2.fort_id=f.id))
					WHERE guard_pokemon_id = '".$pokemon_id."'";
		$result = $this->mysqli->query($req);
		$data = $result->fetch_object();
		return $data;
	}

	function getPokemonLastSeen($pokemon_id) {
		$req = "SELECT FROM_UNIXTIME(expire_timestamp) AS expire_timestamp, FROM_UNIXTIME(expire_timestamp) AS disappear_time_real, lat AS latitude, lon AS longitude
                FROM sightings
                WHERE pokemon_id = '".$pokemon_id."'
                ORDER BY expire_timestamp DESC
                LIMIT 0,1";
		$result = $this->mysqli->query($req);
		$data = $result->fetch_object();
		return $data;
	}

	function getTop50Pokemon($pokemon_id, $top_order_by, $top_direction) {
		$req = "SELECT FROM_UNIXTIME(expire_timestamp) AS distime, pokemon_id as pokemon_id, FROM_UNIXTIME(expire_timestamp) as disappear_time, lat as latitude, lon as longitude,
                cp, atk_iv as individual_attack, def_iv as individual_defense, sta_iv as individual_stamina,
                ROUND(100*(atk_iv+def_iv+sta_iv)/45,1) AS IV, move_1 as move_1, move_2, form
                FROM sightings
	            WHERE pokemon_id = '" . $pokemon_id . "' AND move_1 IS NOT NULL AND move_1 <> '0'
	            ORDER BY $top_order_by $top_direction, expire_timestamp DESC
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
			$trainer_blacklist = " AND owner_name NOT IN ('" . implode("','", self::$config->system->trainer_blacklist) . "')";
		}

		$req = "SELECT owner_name as trainer_name, ROUND((100*((atk_iv)+(def_iv)+(sta_iv))/45),1) AS IV, move_1, move_2, cp as cp,
                FROM_UNIXTIME(last_modified) AS lasttime, last_modified as last_seen
                FROM gym_defenders
				WHERE pokemon_id = '" . $pokemon_id . "'" . $trainer_blacklist . "
				ORDER BY $best_order_by $best_direction, owner_name ASC
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
			. "AND FROM_UNIXTIME(expire_timestamp) BETWEEN '".$start."' AND '".$end."'";
		$req 		= "SELECT lat AS latitude, lon AS longitude FROM sightings".$where." ORDER BY expire_timestamp DESC LIMIT 100000";
		$result = $this->mysqli->query($req);
		$points = array();
		while ($data = $result->fetch_object()) {
			$points[] = $data;
		}
		return $points;
	}

	public function getPokemonGraph($pokemon_id) {
		$req = "SELECT COUNT(*) AS total, HOUR(disappear_time) AS disappear_hour
					FROM (SELECT FROM_UNIXTIME(expire_timestamp) as disappear_time FROM sightings WHERE pokemon_id = '".$pokemon_id."' ORDER BY disappear_time LIMIT 100000) AS pokemonFiltered
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
		$where = " WHERE expire_timestamp >= UNIX_TIMESTAMP() AND pokemon_id = " . $pokemon_id;

		$reqTestIv = "SELECT MAX(atk_iv) AS iv FROM sightings " . $where;
		$resultTestIv = $this->mysqli->query($reqTestIv);
		$testIv = $resultTestIv->fetch_object();
		if (!is_null($inmap_pokemons) && ($inmap_pokemons != "")) {
			foreach ($inmap_pokemons as $inmap) {
				$inmap_pkms_filter .= "'".$inmap."',";
			}
			$inmap_pkms_filter = rtrim($inmap_pkms_filter, ",");
			$where .= " AND encounter_id NOT IN (" . $inmap_pkms_filter . ") ";
		}
		if ($testIv->iv != null && !is_null($ivMin) && ($ivMin != "")) {
			$where .= " AND ((100/45)*(atk_iv + def_iv + sta_iv)) >= (" . $ivMin . ") ";
		}
		if ($testIv->iv != null && !is_null($ivMax) && ($ivMax != "")) {
			$where .= " AND ((100/45)*(atk_iv + def_iv + sta_iv)) <= (" . $ivMax . ") ";
		}
		$req = "SELECT pokemon_id, lat AS latitude, lon AS longitude,
    					FROM_UNIXTIME(expire_timestamp) AS disappear_time,
    					FROM_UNIXTIME(expire_timestamp) AS disappear_time_real,
    					atk_iv AS individual_attack, def_iv AS individual_defense, sta_iv AS individual_stamina,
   						move_1, move_2
					FROM sightings " . $where . "
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
		$req = "SELECT FROM_UNIXTIME(MIN(expire_timestamp)) AS min, FROM_UNIXTIME(MAX(expire_timestamp)) AS max FROM sightings";
		$result = $this->mysqli->query($req);
		$data = $result->fetch_object();
		return $data;
	}

	public function getMapsCoords() {
		$req = "SELECT MAX(lat) AS max_latitude, MIN(lat) AS min_latitude, MAX(lon) AS max_longitude, MIN(lon) as min_longitude FROM spawnpoints";
		$result = $this->mysqli->query($req);
		$data = $result->fetch_object();
		return $data;
	}


	///////////////
	// Pokestops
	//////////////


	function getTotalPokestops() {
		$req = "SELECT COUNT(*) as total FROM pokestops";
		$result = $this->mysqli->query($req);
		$data = $result->fetch_object();
		return $data;
	}

	public function getAllPokestops() {
		$req = "SELECT lat as latitude, lon as longitude, null AS lure_expiration, UNIX_TIMESTAMP() AS now, null AS lure_expiration_real FROM pokestops";
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
		$req = "SELECT COUNT(*) AS total, guard_pokemon_id 
					FROM forts f
					LEFT JOIN fort_sightings fs ON (fs.fort_id = f.id AND fs.last_modified = (SELECT MAX(last_modified) FROM fort_sightings fs2 WHERE fs2.fort_id=f.id))
					WHERE team = '".$team_id."' GROUP BY guard_pokemon_id ORDER BY total DESC LIMIT 0,3";
		$result = $this->mysqli->query($req);

		$datas = array();
		while ($data = $result->fetch_object()) {
			$datas[] = $data;
		}

		return $datas;
	}

	function getOwnedAndPoints($team_id) {
		$req = "SELECT COUNT(f.id) AS total, ROUND(AVG(fs.total_cp)) AS average_points
        			FROM forts f
					LEFT JOIN fort_sightings fs ON (fs.fort_id = f.id AND fs.last_modified = (SELECT MAX(last_modified) FROM fort_sightings fs2 WHERE fs2.fort_id=f.id))
        			WHERE fs.team = '" . $team_id . "'";
		$result = $this->mysqli->query($req);
		$data = $result->fetch_object();
		return $data;
	}

	function getAllGyms() {
		$req = "SELECT f.id as gym_id, team as team_id, f.lat as latitude, f.lon as longitude, updated as last_scanned, (6 - fs.slots_available) AS level 
					FROM forts f
					LEFT JOIN fort_sightings fs ON (fs.fort_id = f.id AND fs.last_modified = (SELECT MAX(last_modified) FROM fort_sightings fs2 WHERE fs2.fort_id=f.id));";
		$result = $this->mysqli->query($req);
		$gyms = array();
		while ($data = $result->fetch_object()) {
			$gyms[] = $data;
		}
		return $gyms;
	}

	public function getGymData($gym_id) {
		$req = "SELECT f.name AS name, null AS description, f.url AS url, fs.team AS team, FROM_UNIXTIME(fs.updated) AS last_scanned, fs.guard_pokemon_id AS guard_pokemon_id, (6 - fs.slots_available) AS level, fs.total_cp	
			FROM forts f
			LEFT JOIN fort_sightings fs ON (fs.fort_id = f.id AND fs.last_modified = (SELECT MAX(last_modified) FROM fort_sightings fs2 WHERE fs2.fort_id=f.id))
			WHERE f.id ='".$gym_id."'";
		$result = $this->mysqli->query($req);
		$data = $result->fetch_object();
		return $data;
	}

	public function getGymDefenders($gym_id) {
		$req = "SELECT external_id as pokemon_uid, pokemon_id, atk_iv as iv_attack, def_iv as iv_defense, sta_iv as iv_stamina, cp, fort_id as gym_id
			FROM gym_defenders 
			WHERE fort_id='".$gym_id."'
			ORDER BY deployment_time";
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
		$req = "SELECT r.fort_id AS gym_id, r.level AS level, r.pokemon_id AS pokemon_id, r.cp AS cp, r.move_1 AS move_1, r.move_2 AS move_2, FROM_UNIXTIME(r.time_spawn) AS spawn, FROM_UNIXTIME(r.time_battle) AS start, FROM_UNIXTIME(r.time_end) AS end, FROM_UNIXTIME(fs.updated) AS last_scanned, f.name, f.lat AS latitude, f.lon as longitude 
					FROM forts f
					LEFT JOIN fort_sightings fs ON (fs.fort_id = f.id AND fs.last_modified = (SELECT MAX(last_modified) FROM fort_sightings fs2 WHERE fs2.fort_id=f.id))
				 	LEFT JOIN raids r ON (r.fort_id = f.id AND r.time_end >= UNIX_TIMESTAMP())
					WHERE r.time_end > UNIX_TIMESTAMP() 
					ORDER BY r.level DESC, r.time_battle" . $limit;
		$result = $this->mysqli->query($req);
		$raids = array();
		while ($data = $result->fetch_object()) {
			$raids[] = $data;
		}
		return $raids;
	}



	////////////////
	// Gym History
	////////////////

	public function getGymHistories($gym_name, $team, $page, $ranking)
	{
		$where = "";
		if (isset($gym_name) && $gym_name != '') {
			$where = " WHERE name LIKE '%".$gym_name."%'";
		}
		if (isset($team) && $team != '') {
			$where .= ($where === "" ? " WHERE" : " AND")." fs.team = ".$team;
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

		$req = "SELECT f.id as gym_id, fs.total_cp, f.name, fs.team as team_id, (6 - slots_available) as pokemon_count, FROM_UNIXTIME(last_modified) AS last_modified 
			FROM forts f
			LEFT JOIN fort_sightings fs ON (fs.fort_id = f.id AND fs.last_modified = (SELECT MAX(last_modified) FROM fort_sightings fs2 WHERE fs2.fort_id=f.id))
			".$where.$order.$limit;
		$result = $this->mysqli->query($req);
		$gym_history = array();
		while ($data = $result->fetch_object()) {
			$gym_history[] = $data;
		}
		return $gym_history;
	}

	public function getGymHistoriesPokemon($gym_id)
	{
		$req = "SELECT external_id AS pokemon_uid, pokemon_id, cp_now as cp, owner_name AS trainer_name
					FROM gym_defenders
					WHERE fort_id = '". $gym_id ."'
					ORDER BY deployment_time";
		$result = $this->mysqli->query($req);
		$pokemons = array();
		while ($data = $result->fetch_object()) {
			$pokemons[] = $data;
		}
		return $pokemons;
	}

	public function getHistoryForGym($page, $gym_id)
	{
		if (isset(self::$config->system->gymhistory_hide_cp_changes) && self::$config->system->gymhistory_hide_cp_changes === true) {
			$pageSize = 25;
		} else {
			$pageSize = 10;
		}
		$req = "SELECT f.id as gym_id, fs.team as team_id, total_cp, FROM_UNIXTIME(fs.last_modified) as last_modified, last_modified as last_modified_real
					FROM fort_sightings fs
					LEFT JOIN forts f ON f.id = fs.fort_id
					WHERE f.id = '". $gym_id ."'
					ORDER BY fs.last_modified DESC
					LIMIT ".($page * $pageSize).",".($pageSize+1);
		$result = $this->mysqli->query($req);
		$history = array();
		$count = 0;
		while ($data = $result->fetch_object()) {
			$count++;
			if ($data->total_cp == 0) {
				$data->pokemon = array();
				$data->pokemon_count = 0;
				$data->pokemon_uids = "";
			} else {
				$data->pokemon = $this->getHistoryForGymPokemon($gym_id, $data->last_modified_real);
				$data->pokemon_count = count($data->pokemon);
				$data->pokemon_uids = implode(",", array_keys($data->pokemon));
			}
			if ($data->total_cp === 0 || $data->pokemon_count !== 0) {
				$history[] = $data;
			}
		}
		if ($count !== ($pageSize + 1)) {
			$last_page = true;
		} else {
			$last_page = false;
		}
		return array("last_page" => $last_page, "data" => $history);
	}

	private function getHistoryForGymPokemon($gym_id, $last_modified)
	{
		$req = "SELECT ghd.defender_id, gd.pokemon_id, ghd.cp, gd.owner_name as trainer_name
					FROM gym_history_defenders ghd
					JOIN gym_defenders gd ON ghd.defender_id = gd.external_id
					WHERE ghd.fort_id = '". $gym_id ."' AND date = '".$last_modified."'
					ORDER BY gd.deployment_time";
		$result = $this->mysqli->query($req);
		$pokemons = array();
		while ($data = $result->fetch_object()) {
			$pokemons[$data->defender_id] = $data;
		}
		return $pokemons;
	}



	//////////////
	// Trainers
	//////////////

	public function getTrainers($trainer_name, $team, $page, $rankingNumber) {
		$ranking = $this->getTrainerLevelRanking();
		$where = "";
		if (!empty(self::$config->system->trainer_blacklist)) {
			$where .= " AND gd.owner_name NOT IN ('".implode("','", self::$config->system->trainer_blacklist)."')";
		}
		if ($trainer_name != "") {
			$where = " AND gd.owner_name LIKE '%".$trainer_name."%'";
		}
		if ($team != 0) {
			$where .= ($where == "" ? " HAVING" : " AND")." team = ".$team;
		}
		switch ($rankingNumber) {
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
		$req = "SELECT gd.owner_name AS name, MAX(owner_level) AS level, MAX(cp) AS maxCp, MAX(active) AS active, MAX(team) AS team, FROM_UNIXTIME(MAX(last_modified)) as last_seen
				  	FROM gym_defenders gd
				  	LEFT JOIN (
				  		SELECT owner_name, COUNT(*) as active
				  		FROM gym_defenders gd2
						WHERE fort_id IS NOT NULL
				  		GROUP BY owner_name
				  	) active ON active.owner_name = gd.owner_name
				  	WHERE gd.owner_level IS NOT NULL " . $where . "
				  	GROUP BY gd.owner_name" . $order  . $limit;
		$result = $this->mysqli->query($req);
		$trainers = array();
		while ($data = $result->fetch_object()) {
			$data->last_seen = date("Y-m-d", strtotime($data->last_seen));
            if (is_null($data->active)) {
                $data->active = 0;
            }
			$trainers[$data->name] = $data;

			$pokemon = array_merge($this->getActivePokemon($data->name),  $this->getInactivePokemon($data->name));

			$trainers[$data->name]->gyms = $data->active;
			$trainers[$data->name]->pokemons = $pokemon;
			$trainers[$data->name]->rank = $ranking[$data->level];
		}
		return $trainers;
	}

	public function getTrainerLevelRanking() {
		$exclue = "";
		if (!empty(self::$config->system->trainer_blacklist)) {
			$exclue .= " AND owner_name NOT IN ('".implode("','", self::$config->system->trainer_blacklist)."')";
		}
		$req = "SELECT COUNT(*) AS count, level FROM (SELECT MAX(owner_level) as level FROM gym_defenders WHERE owner_level IS NOT NULL ".$exclue." GROUP BY owner_level, owner_name) x GROUP BY level";
		$result = $this->mysqli->query($req);
		$levelData = array();
		while ($data = $result->fetch_object()) {
			$levelData[$data->level] = $data->count;
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

	public function getActivePokemon($trainer_name) {
		$req = "SELECT pokemon_id, cp, atk_iv AS iv_attack, sta_iv AS iv_stamina, def_iv AS iv_defense, FROM_UNIXTIME(deployment_time) AS deployment_time, '1' AS active, fort_id as gym_id, FLOOR((UNIX_TIMESTAMP() - created) / 86400) AS last_scanned
						FROM gym_defenders 
						WHERE owner_name = '".$trainer_name."' AND fort_id IS NOT NULL
						ORDER BY deployment_time";
		$result = $this->mysqli->query($req);
		$pokemon = array();
		while ($data = $result->fetch_object()) {
			$pokemon[] = $data;
		}
		return $pokemon;
	}

	public function getInactivePokemon($trainer_name) {
		$req = "SELECT pokemon_id, cp, atk_iv AS iv_attack, sta_iv AS iv_stamina, def_iv AS iv_defense, NULL AS deployment_time, '0' AS active, fort_id as gym_id, FLOOR((UNIX_TIMESTAMP() - created) / 86400) AS last_scanned
					FROM gym_defenders 
					WHERE owner_name = '".$trainer_name."' AND fort_id IS NULL
					ORDER BY last_scanned";
		$result = $this->mysqli->query($req);
		$pokemon = array();
		while ($data = $result->fetch_object()) {
			$pokemon[] = $data;
		}
		return $pokemon;
	}

	public function getTrainerLevelCount($team_id) {
		$exclue = "";
		if (!empty(self::$config->system->trainer_blacklist)) {
			$exclue .= " AND owner_name NOT IN ('".implode("','", self::$config->system->trainer_blacklist)."')";
		}
		$req = "SELECT COUNT(*) AS count, level FROM (SELECT MAX(owner_level) as level FROM gym_defenders WHERE owner_level IS NOT NULL AND team = '".$team_id."' ".$exclue." GROUP BY owner_level, owner_name) x GROUP BY level";
		$result = $this->mysqli->query($req);
		$levelData = array();
		while ($data = $result->fetch_object()) {
			$levelData[$data->level] = $data->count;
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


	/////////
	// Cron
	/////////

	public function getPokemonCountsActive() {
		$req = "SELECT pokemon_id, COUNT(*) as total FROM sightings WHERE expire_timestamp >= UNIX_TIMESTAMP() GROUP BY pokemon_id";
		$result = $this->mysqli->query($req);
		$counts = array();
		while ($data = $result->fetch_object()) {
			$counts[$data->pokemon_id] = $data->total;
		}
		return $counts;
	}

	public function getPokemonCountsLastDay() {
		$req = "SELECT pokemon_id, COUNT(*) AS spawns_last_day
					FROM sightings
					WHERE expire_timestamp >= (SELECT MAX(expire_timestamp) - 86400 FROM sightings)
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
		$where = "WHERE p.pokemon_id = '".$pokemon_id."' AND p.expire_timestamp - (coalesce(CASE WHEN duration = 0 THEN NULL ELSE duration END ,30)*60) > '".$last_update."'";
		$req = "SELECT count, p.expire_timestamp - (coalesce(CASE WHEN duration = 0 THEN NULL ELSE duration END ,30)*60) AS last_timestamp, (FROM_UNIXTIME(p.expire_timestamp)) AS disappear_time_real, p.lat as latitude, p.lon as longitude
					FROM sightings p
					LEFT JOIN spawnpoints s ON p.spawn_id = s.spawn_id
					JOIN (SELECT COUNT(*) AS count
						FROM sightings p
						LEFT JOIN spawnpoints s ON p.spawn_id = s.spawn_id
                    	" . $where."
                    ) x
					" . $where . "
					ORDER BY last_timestamp DESC
					LIMIT 0 , 1";
		$result = $this->mysqli->query($req);
		$data = $result->fetch_object();
		return $data;
	}

	public function getRaidsSinceLastUpdate($pokemon_id, $last_update) {
		$where = "WHERE pokemon_id = '".$pokemon_id."AND".$last_update."'";
		$req = "SELECT time_battle AS start_timestamp, time_end as end, (FROM_UNIXTIME(time_end)) AS end_time_real, lat as latitude, lon as longitude, count
					FROM raids r
					JOIN forts g
					JOIN (SELECT COUNT(*) AS count
						FROM raids
                    	" . $where."
                    ) x 
					ON r.fort_id = g.id
                    " . $where . "
                    ORDER BY time_battle DESC
					LIMIT 0 , 1";
		$result = $this->mysqli->query($req);
		$data = $result->fetch_object();
		return $data;
	}

	public function getCaptchaCount() {
		$req = " SELECT COUNT(*) as total FROM accounts WHERE captchaed IS NOT NULL AND reason IS NULL";
		$result = $this->mysqli->query($req);
		$data = $result->fetch_object();
		return $data;
	}

	public function getNestData() {
		$pokemon_exclude_sql = "";
		if (!empty(self::$config->system->nest_exclude_pokemon)) {
			$pokemon_exclude_sql = "AND p.pokemon_id NOT IN (" . implode(",", self::$config->system->nest_exclude_pokemon) . ")";
		}
		$req = "SELECT p.pokemon_id, MAX(p.lat) AS latitude, MAX(p.lon) AS longitude, count(p.pokemon_id) AS total_pokemon, MAX(s.updated) as latest_seen, coalesce(CASE WHEN MAX(duration) = 0 THEN NULL ELSE MAX(duration) END ,30)*60 as duration
			          FROM sightings p
			          INNER JOIN spawnpoints s ON (p.spawn_id = s.spawn_id)
			          WHERE p.expire_timestamp > UNIX_TIMESTAMP() - 86400
			          " . $pokemon_exclude_sql . "
			          GROUP BY p.spawn_id, p.pokemon_id
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
