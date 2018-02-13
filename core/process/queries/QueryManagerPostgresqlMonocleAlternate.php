<?php
/**
 * Created by PhpStorm.
 * User: floriankostenzer
 * Date: 27.01.18
 * Time: 02:26
 */

class QueryManagerPostgresqlMonocleAlternate extends QueryManagerPostgresql {

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
		$result = pg_query($this->db, $req);
		if ($result === false) {
			return 1;
		} else {
			$data = pg_fetch_object($result);
			$total = $data->total;

			if ($total == 0) {
				return 2;
			}
		}
		return 0;
	}

	function testTotalGyms() {
		$req = "SELECT COUNT(*) as total FROM forts";
		$result = pg_query($this->db, $req);
		if ($result === false) {
			return 1;
		} else {
			$data = pg_fetch_object($result);
			$total = $data->total;

			if ($total == 0) {
				return 2;
			}
		}
		return 0;
	}

	function testTotalPokestops() {
		$req = "SELECT COUNT(*) as total FROM pokestops";
		$result = pg_query($this->db, $req);
		if ($result === false) {
			return 1;
		} else {
			$data = pg_fetch_object($result);
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
		$req = "SELECT COUNT(*) AS total FROM sightings WHERE expire_timestamp >= EXTRACT(EPOCH FROM NOW())";
		$result = pg_query($this->db, $req);
		$data = pg_fetch_object($result);
		return $data;
	}

	function getTotalLures() {
		$data = (object) array("total" => 0);
		return $data;
	}

	function getTotalGyms() {
		$req = "SELECT COUNT(*) AS total FROM forts";
		$result = pg_query($this->db, $req);
		$data = pg_fetch_object($result);
		return $data;
	}

	function getTotalRaids() {
		$req = "SELECT COUNT(*) AS total FROM raids WHERE time_battle <= EXTRACT(EPOCH FROM NOW()) AND time_end >= EXTRACT(EPOCH FROM NOW())";
		$result = pg_query($this->db, $req);
		$data = pg_fetch_object($result);
		return $data;
	}


	function getTotalGymsForTeam($team_id) {
		$req = "SELECT COUNT(*) AS total FROM fort_sightings WHERE team = '$team_id'";
		$result = pg_query($this->db, $req);
		$data = pg_fetch_object($result);
		return $data;
	}

	function getRecentAll() {
		$req = "SELECT DISTINCT pokemon_id, encounter_id, TO_TIMESTAMP(expire_timestamp) AS disappear_time, TO_TIMESTAMP(updated) AS last_modified, TO_TIMESTAMP(expire_timestamp) AS disappear_time_real,
              lat AS latitude, lon AS longitude, cp, atk_iv AS individual_attack, def_iv AS individual_defense, sta_iv AS individual_stamina
              FROM sightings
              ORDER BY last_modified DESC
              LIMIT 12 OFFSET 0";
		$result = pg_query($this->db, $req);
		$data = array();
		if ($result->num_rows > 0) {
			while ($row = pg_fetch_object($result)) {
				$data[] = $row;
			}
		}
		return $data;
	}

	function getRecentMythic($mythic_pokemon) {
		$req = "SELECT pokemon_id, encounter_id, TO_TIMESTAMP(expire_timestamp) AS disappear_time, TO_TIMESTAMP(updated) AS last_modified, TO_TIMESTAMP(expire_timestamp) AS disappear_time_real,
                lat AS latitude, lon AS longitude, cp, atk_iv AS individual_attack, def_iv AS individual_defense, sta_iv AS individual_stamina
                FROM sightings
                WHERE pokemon_id IN (".implode(",", $mythic_pokemon).")
                ORDER BY updated DESC
                LIMIT 12 OFFSET 0";
		$result = pg_query($this->db, $req);
		$data = array();
		if ($result->num_rows > 0) {
			while ($row = pg_fetch_object($result)) {
				$data[] = $row;
			}
		}
		return $data;
	}

	///////////////////
	// Single Pokemon
	///////////////////

	function getGymsProtectedByPokemon($pokemon_id) {
		$req = "SELECT COUNT(DISTINCT(fort_id)) AS total FROM fort_sightings WHERE guard_pokemon_id = '".$pokemon_id."'";
		$result = pg_query($this->db, $req);
		$data = pg_fetch_object($result);
		return $data;
	}

	function getPokemonLastSeen($pokemon_id) {
		$req = "SELECT TO_TIMESTAMP(expire_timestamp) AS expire_timestamp, TO_TIMESTAMP(expire_timestamp) AS disappear_time_real, lat AS latitude, lon AS longitude
                FROM sightings
                WHERE pokemon_id = '".$pokemon_id."'
                ORDER BY expire_timestamp DESC
                LIMIT 1 OFFSET 0";
		$result = pg_query($this->db, $req);
		$data = pg_fetch_object($result);
		return $data;
	}

	function getTop50Pokemon($pokemon_id, $top_order_by, $top_direction) {
		$req = "SELECT expire_timestamp, TO_TIMESTAMP(expire_timestamp) AS distime, pokemon_id as pokemon_id, TO_TIMESTAMP(expire_timestamp) as disappear_time, lat as latitude, lon as longitude,
                cp, atk_iv as individual_attack, def_iv as individual_defense, sta_iv as individual_stamina,
                ROUND(100*(atk_iv+def_iv+sta_iv)/45,1) AS \"IV\", move_1 as move_1, move_2, form
                FROM sightings
	            WHERE pokemon_id = '" . $pokemon_id . "' AND move_1 IS NOT NULL AND move_1 <> '0'
	            ORDER BY $top_order_by $top_direction, expire_timestamp DESC
	            LIMIT 50 OFFSET 0";
		$result = pg_query($this->db, $req);
		$top = array();
		while ($data = pg_fetch_object($result)) {
			$top[] = $data;
		}
		return $top;
	}

	function getTop50Trainers($pokemon_id, $best_order_by, $best_direction) {
		$trainer_blacklist = "";
		if (!empty(self::$config->system->trainer_blacklist)) {
			$trainer_blacklist = " AND owner_name NOT IN ('" . implode("','", self::$config->system->trainer_blacklist) . "')";
		}

		$req = "SELECT owner_name as trainer_name, ROUND((100.0*((atk_iv)+(def_iv)+(sta_iv))/45),1) AS \"IV\", move_1, move_2, cp as cp,
                TO_TIMESTAMP(last_modified) AS lasttime, last_modified as last_seen
                FROM gym_defenders
				WHERE pokemon_id = '" . $pokemon_id . "'" . $trainer_blacklist . "
				ORDER BY $best_order_by $best_direction, owner_name ASC
				LIMIT 50 OFFSET 0";

		$result = pg_query($this->db, $req);
		$toptrainer = array();
		while ($data = pg_fetch_object($result)) {
			$toptrainer[] = $data;
		}
		return $toptrainer;
	}

	public function getPokemonHeatmap($pokemon_id, $start, $end) {
		$where = " WHERE pokemon_id = ".$pokemon_id." "
			. "AND TO_TIMESTAMP(expire_timestamp) BETWEEN '".$start."' AND '".$end."'";
		$req 		= "SELECT lat AS latitude, lon AS longitude FROM sightings".$where." ORDER BY expire_timestamp DESC LIMIT 100000";
		$result = pg_query($this->db, $req);
		$points = array();
		while ($data = pg_fetch_object($result)) {
			$points[] = $data;
		}
		return $points;
	}

	public function getPokemonGraph($pokemon_id) {
		$req = "SELECT COUNT(*) AS total, EXTRACT(HOUR FROM disappear_time) AS disappear_hour
					FROM (SELECT TO_TIMESTAMP(expire_timestamp) as disappear_time FROM sightings WHERE pokemon_id = '".$pokemon_id."' ORDER BY disappear_time LIMIT 100000) AS pokemonFiltered
				GROUP BY disappear_hour
				ORDER BY disappear_hour";
		$result = pg_query($this->db, $req);
		$array = array_fill(0, 24, 0);
		while ($result && $data = pg_fetch_object($result)) {
			$array[$data->disappear_hour] = $data->total;
		}
		// shift array because AM/PM starts at 1AM not 0:00
		$array[] = $array[0];
		array_shift($array);
		return $array;
	}

	public function getPokemonLive($pokemon_id, $ivMin, $ivMax, $inmap_pokemons) {
		$inmap_pkms_filter = "";
		$where = " WHERE expire_timestamp >= EXTRACT(EPOCH FROM NOW()) AND pokemon_id = " . $pokemon_id;

		$reqTestIv = "SELECT MAX(atk_iv) AS iv FROM sightings " . $where;
		$resultTestIv = pg_query($this->db, $reqTestIv);
		$testIv = pg_fetch_object($resultTestIv);
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
    					TO_TIMESTAMP(expire_timestamp) AS disappear_time,
    					TO_TIMESTAMP(expire_timestamp) AS disappear_time_real,
    					atk_iv AS individual_attack, def_iv AS individual_defense, sta_iv AS individual_stamina,
   						move_1, move_2
					FROM sightings " . $where . "
					ORDER BY disappear_time DESC
					LIMIT 5000";
		$result = pg_query($this->db, $req);
		$spawns = array();
		while ($data = pg_fetch_object($result)) {
			$spawns[] = $data;
		}
		return $spawns;
	}

	public function getPokemonSliderMinMax() {
		$req = "SELECT TO_TIMESTAMP(MIN(expire_timestamp)) AS min, TO_TIMESTAMP(MAX(expire_timestamp)) AS max FROM sightings";
		$result = pg_query($this->db, $req);
		$data = pg_fetch_object($result);
		return $data;
	}

	public function getMapsCoords() {
		$req = "SELECT MAX(lat) AS max_latitude, MIN(lat) AS min_latitude, MAX(lon) AS max_longitude, MIN(lon) as min_longitude FROM spawnpoints";
		$result = pg_query($this->db, $req);
		$data = pg_fetch_object($result);
		return $data;
	}


	///////////////
	// Pokestops
	//////////////


	function getTotalPokestops() {
		$req = "SELECT COUNT(*) as total FROM pokestops";
		$result = pg_query($this->db, $req);
		$data = pg_fetch_object($result);
		return $data;
	}

	public function getAllPokestops() {
		$req = "SELECT lat as latitude, lon as longitude, null AS lure_expiration, EXTRACT(EPOCH FROM NOW()) AS now, null AS lure_expiration_real FROM pokestops";
		$result = pg_query($this->db, $req);
		$pokestops = array();
		while ($data = pg_fetch_object($result)) {
			$pokestops[] = $data;
		}
		return $pokestops;
	}


	/////////
	// Gyms
	/////////

	function getTeamGuardians($team_id) {
		$req = "SELECT COUNT(*) AS total, guard_pokemon_id FROM fort_sightings WHERE team = '".$team_id."' GROUP BY guard_pokemon_id ORDER BY total DESC LIMIT 3 OFFSET 0";
		$result = pg_query($this->db, $req);

		$datas = array();
		while ($data = pg_fetch_object($result)) {
			$datas[] = $data;
		}

		return $datas;
	}

	function getOwnedAndPoints($team_id) {
		$req = "SELECT COUNT(DISTINCT(fs.fort_id)) AS total, ROUND((SUM(gd.cp)) / COUNT(DISTINCT(fs.fort_id)),0) AS average_points
        			FROM fort_sightings fs
        			JOIN gym_defenders gd ON fs.fort_id = gd.fort_id
        			WHERE fs.team = '" . $team_id . "'";
		$result = pg_query($this->db, $req);
		$data = pg_fetch_object($result);
		return $data;
	}

	function getAllGyms() {
		$req = "SELECT f.id as gym_id, team as team_id, f.lat as latitude, f.lon as longitude, updated as last_scanned, (6 - fs.slots_available) AS level FROM forts f LEFT JOIN fort_sightings fs ON f.id = fs.fort_id;";
		$result = pg_query($this->db, $req);
		$gyms = array();
		while ($data = pg_fetch_object($result)) {
			$gyms[] = $data;
		}
		return $gyms;
	}

	public function getGymData($gym_id) {
		$req = "SELECT f.name AS name, null AS description, f.url AS url, fs.team AS team, TO_TIMESTAMP(fs.updated) AS last_scanned, fs.guard_pokemon_id AS guard_pokemon_id, (6 - fs.slots_available) AS level, SUM(gd.cp) as total_cp	
			FROM fort_sightings fs
			LEFT JOIN forts f ON f.id = fs.fort_id
			LEFT JOIN gym_defenders gd ON f.id = gd.fort_id
			WHERE f.id ='".$gym_id."'
			GROUP BY f.name, f.url, fs.team, fs.updated, fs.guard_pokemon_id, fs.slots_available, gd.cp";
		$result = pg_query($this->db, $req);
		$data = pg_fetch_object($result);
		return $data;
	}

	public function getGymDefenders($gym_id) {
		$req = "SELECT DISTINCT external_id as pokemon_uid, pokemon_id, atk_iv as iv_attack, def_iv as iv_defense, sta_iv as iv_stamina, cp, fort_id as gym_id
			FROM gym_defenders 
			WHERE fort_id='".$gym_id."'
			ORDER BY cp DESC";
		$result = pg_query($this->db, $req);
		$defenders = array();
		while ($data = pg_fetch_object($result)) {
			$defenders[] = $data;
		}
		return $defenders;
	}



	////////////////
	// Gym History
	////////////////

	public function getGymHistories($gym_name, $team, $page, $ranking)
	{
		// TODO: Implement
		return array();
	}

	public function getGymHistoriesPokemon($gym_id)
	{
		// TODO: Implement
		return array();
	}

	public function getHistoryForGym($page, $gym_id)
	{
		// TODO: Implement
		return array();
	}

	public function getHistoryForGymPokemon($pkm_uids)
	{
		// TODO: Implement
		return array();
	}

	///////////
	// Raids
	///////////

	public function getAllRaids($page) {
		$limit = " LIMIT 10 OFFSET ". ($page * 10);
		$req = "SELECT r.fort_id AS gym_id, r.level AS level, r.pokemon_id AS pokemon_id, r.cp AS cp, r.move_1 AS move_1, r.move_2 AS move_2, TO_TIMESTAMP(r.time_spawn) AS spawn, TO_TIMESTAMP(r.time_battle) AS start, TO_TIMESTAMP(r.time_end) AS end, TO_TIMESTAMP(fs.updated) AS last_scanned, f.name, f.lat AS latitude, f.lon as longitude 
					FROM raids r 
					JOIN forts f ON f.id = r.fort_id 
					LEFT JOIN fort_sightings fs ON fs.fort_id = r.fort_id 
					WHERE r.time_end > EXTRACT(EPOCH FROM NOW()) 
					ORDER BY r.level DESC, r.time_battle" . $limit;
		$result = pg_query($this->db, $req);
		$raids = array();
		while ($data = pg_fetch_object($result)) {
			$raids[] = $data;
		}
		return $raids;
	}


	//////////////
	// Trainers
	//////////////

	public function getTrainers($trainer_name, $team, $page, $ranking) {
		return array(); // Waiting for Monocle to store level
	}

	public function getTrainerLevelCount($team_id) {
		$levelData = array();
		for ($i = 5; $i <= 40; $i++) {
			if (!isset($levelData[$i])) {
				$levelData[$i] = 0;
			}
		}
		return $levelData; // Waiting for Monocle to store level
	}


	/////////
	// Cron
	/////////

	public function getPokemonCountsActive() {
		$req = "SELECT pokemon_id, COUNT(*) as total FROM sightings WHERE expire_timestamp >= EXTRACT(EPOCH FROM NOW()) GROUP BY pokemon_id";
		$result = pg_query($this->db, $req);
		$counts = array();
		while ($data = pg_fetch_object($result)) {
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
		$result = pg_query($this->db, $req);
		$counts = array();
		while ($data = pg_fetch_object($result)) {
			$counts[$data->pokemon_id] = $data->spawns_last_day;
		}
		return $counts;
	}

	public function getPokemonSinceLastUpdate($pokemon_id, $last_update) {
		$where = "WHERE p.pokemon_id = '".$pokemon_id."' AND p.expire_timestamp - (coalesce(CASE WHEN duration = 0 THEN NULL ELSE duration END ,30)*60) > '".$last_update."'";
		$req = "SELECT count, p.expire_timestamp - (coalesce(CASE WHEN duration = 0 THEN NULL ELSE duration END ,30)*60) AS last_timestamp, (TO_TIMESTAMP(expire_timestamp)) AS disappear_time_real, lat as latitude, lon as longitude
					FROM sightings p
					LEFT JOIN spawnpoints s ON p.spawn_id = s.spawn_id
				  	JOIN (SELECT COUNT(*) AS count
						FROM FROM sightings p
						LEFT JOIN spawnpoints s ON p.spawn_id = s.spawn_id
                    	" . $where. "
                    ) count ON 1 = 1
					" . $where . "
					ORDER BY last_timestamp DESC
					LIMIT 1 OFFSET 0";
		$result = pg_query($this->db, $req);
		$data = pg_fetch_object($result);
		return $data;
	}

	public function getRaidsSinceLastUpdate($pokemon_id, $last_update) {
		$where = "WHERE pokemon_id = '".$pokemon_id."' AND time_battle > '".$last_update."'";
		$req = "SELECT time_battle AS start_timestamp, time_end as end, (TO_TIMESTAMP(time_end)) AS end_time_real, lat as latitude, lon as longitude, count
					FROM raids r
					JOIN forts g ON r.fort_id = g.id
					JOIN (SELECT COUNT(*) AS count
						FROM raids
                    	" . $where."
                    ) count ON 1 = 1 
	                " . $where."
	                ORDER BY time_battle DESC
					LIMIT 1 OFFSET 0";
		$result = pg_query($this->db, $req);
		$data = pg_fetch_object($result);
		return $data;
	}

	public function getCaptchaCount() {
		$req = " SELECT COUNT(*) as total FROM accounts WHERE captchaed IS NOT NULL AND reason IS NULL";
		$result = pg_query($this->db, $req);
		$data = pg_fetch_object($result);
		return $data;
	}

	public function getNestData() {
		$pokemon_exclude_sql = "";
		if (!empty(self::$config->system->nest_exclude_pokemon)) {
			$pokemon_exclude_sql = "AND p.pokemon_id NOT IN (" . implode(",", self::$config->system->nest_exclude_pokemon) . ")";
		}
		$req = "SELECT p.spawn_id, p.pokemon_id, MAX(p.lat) AS latitude, MAX(p.lon) AS longitude, count(p.pokemon_id) AS total_pokemon, MAX(s.updated) as latest_seen, coalesce(CASE WHEN MAX(duration) = 0 THEN NULL ELSE MAX(duration) END ,30)*60 as duration
			          FROM sightings p
			          INNER JOIN spawnpoints s ON (p.spawn_id = s.spawn_id)
			          WHERE p.expire_timestamp > EXTRACT(EPOCH FROM NOW()) - 86400
			          " . $pokemon_exclude_sql . "
			          GROUP BY p.spawn_id, p.pokemon_id
			          HAVING COUNT(p.pokemon_id) >= 6
			          ORDER BY p.pokemon_id";
		$result = pg_query($this->db, $req);
		$nests = array();
		while ($data = pg_fetch_object($result)) {
			$nests[] = $data;
		}
		return $nests;
	}

}
