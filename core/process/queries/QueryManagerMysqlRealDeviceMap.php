<?php

namespace Worldopole;

class QueryManagerMysqlRealDeviceMap extends QueryManagerMysql
{
    public function __construct()
    {
        parent::__construct();
    }

    public function __destruct()
    {
        parent::__destruct();
    }

    ///////////
    // Tester
    ///////////

    public function testTotalPokemon()
    {
        $req = 'SELECT COUNT(*) as total FROM pokemon';
        $result = $this->mysqli->query($req);
        if (!is_object($result)) {
            return 1;
        } else {
            $data = $result->fetch_object();
            $total = $data->total;

            if (0 == $total) {
                return 2;
            }
        }

        return 0;
    }

    public function testTotalGyms()
    {
        $req = 'SELECT COUNT(*) as total FROM gym';
        $result = $this->mysqli->query($req);
        if (!is_object($result)) {
            return 1;
        } else {
            $data = $result->fetch_object();
            $total = $data->total;

            if (0 == $total) {
                return 2;
            }
        }

        return 0;
    }

    public function testTotalPokestops()
    {
        $req = 'SELECT COUNT(*) as total FROM pokestop';
        $result = $this->mysqli->query($req);
        if (!is_object($result)) {
            return 1;
        } else {
            $data = $result->fetch_object();
            $total = $data->total;

            if (0 == $total) {
                return 2;
            }
        }

        return 0;
    }

    /////////////
    // Homepage
    /////////////

    public function getTotalPokemon()
    {
        $req = 'SELECT COUNT(*) AS total FROM pokemon WHERE expire_timestamp >= UNIX_TIMESTAMP()';
        $result = $this->mysqli->query($req);
        $data = $result->fetch_object();

        return $data;
    }

    public function getTotalLures()
    {
        $req = 'SELECT COUNT(*) as total FROM pokestop WHERE lure_expire_timestamp >= UNIX_TIMESTAMP()';
        $result = $this->mysqli->query($req);
        $data = $result->fetch_object();

        return $data;
    }

    public function getTotalGyms()
    {
        $req = 'SELECT COUNT(*) AS total FROM gym';
        $result = $this->mysqli->query($req);
        $data = $result->fetch_object();

        return $data;
    }

    public function getTotalRaids()
    {
        $req = 'SELECT COUNT(*) AS total FROM gym WHERE raid_battle_timestamp <= UNIX_TIMESTAMP() AND raid_end_timestamp >= UNIX_TIMESTAMP()';
        $result = $this->mysqli->query($req);
        $data = $result->fetch_object();

        return $data;
    }

    public function getTotalGymsForTeam($team_id)
    {
        $req = 'SELECT COUNT(*) AS total FROM gym WHERE team_id = '.$team_id;
        $result = $this->mysqli->query($req);
        $data = $result->fetch_object();

        return $data;
    }

    public function getRecentAll()
    {
        $req = 'SELECT pokemon_id, id, FROM_UNIXTIME(expire_timestamp) AS disappear_time, FROM_UNIXTIME(updated) AS last_modified, FROM_UNIXTIME(expire_timestamp) AS disappear_time_real,
              lat AS latitude, lon AS longitude, cp, atk_iv AS individual_attack, def_iv AS individual_defense, sta_iv AS individual_stamina
              FROM pokemon
              ORDER BY changed DESC
              LIMIT 0,12;';
        $result = $this->mysqli->query($req);
        $data = array();
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_object()) {
                $data[] = $row;
            }
        }

        return $data;
    }

    public function getRecentMythic($mythic_pokemon)
    {
        $req = 'SELECT pokemon_id, id as encounter_id, FROM_UNIXTIME(expire_timestamp) AS disappear_time, FROM_UNIXTIME(updated) AS last_modified, FROM_UNIXTIME(expire_timestamp) AS disappear_time_real,
                lat AS latitude, lon AS longitude, cp, atk_iv AS individual_attack, def_iv AS individual_defense, sta_iv AS individual_stamina
                FROM pokemon
                WHERE pokemon_id IN ('.implode(',', $mythic_pokemon).')
                ORDER BY changed DESC
                LIMIT 0,12';
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

    public function getGymsProtectedByPokemon($pokemon_id)
    {
        return array();
    }

    public function getPokemonLastSeen($pokemon_id)
    {
        $req = "SELECT FROM_UNIXTIME(expire_timestamp) AS expire_timestamp, FROM_UNIXTIME(expire_timestamp) AS disappear_time_real, lat AS latitude, lon AS longitude
                FROM pokemon
                WHERE pokemon_id = '".$pokemon_id."'
                ORDER BY expire_timestamp DESC
                LIMIT 0,1";
        $result = $this->mysqli->query($req);
        $data = $result->fetch_object();

        return $data;
    }

    public function getTop50Pokemon($pokemon_id, $top_order_by, $top_direction)
    {
        $req = "SELECT FROM_UNIXTIME(expire_timestamp) AS distime, pokemon_id as pokemon_id, FROM_UNIXTIME(expire_timestamp) as disappear_time, lat as latitude, lon as longitude,
                cp, atk_iv as individual_attack, def_iv as individual_defense, sta_iv as individual_stamina,
                iv AS IV, move_1 as move_1, move_2, form
                FROM pokemon
	            WHERE pokemon_id = '".$pokemon_id."' AND move_1 IS NOT NULL AND move_1 <> '0'
	            ORDER BY $top_order_by $top_direction, expire_timestamp DESC
	            LIMIT 0,50";

        $result = $this->mysqli->query($req);
        $top = array();
        while ($data = $result->fetch_object()) {
            $top[] = $data;
        }

        return $top;
    }

    public function getTop50Trainers($pokemon_id, $best_order_by, $best_direction)
    {
        return array();
    }

    public function getPokemonHeatmap($pokemon_id, $start, $end)
    {
        $where = ' WHERE pokemon_id = '.$pokemon_id.' '
            ."AND FROM_UNIXTIME(expire_timestamp) BETWEEN '".$start."' AND '".$end."'";
        $req = 'SELECT lat AS latitude, lon AS longitude FROM pokemon'.$where.' LIMIT 100000';
        $result = $this->mysqli->query($req);
        $points = array();
        while ($data = $result->fetch_object()) {
            $points[] = $data;
        }

        return $points;
    }

    public function getPokemonGraph($pokemon_id)
    {
        $req = "SELECT COUNT(*) AS total, HOUR(disappear_time) AS disappear_hour
					FROM (SELECT FROM_UNIXTIME(expire_timestamp) as disappear_time FROM pokemon WHERE pokemon_id = '".$pokemon_id."' LIMIT 100000) AS pokemonFiltered
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

    public function getPokemonLive($pokemon_id, $ivMin, $ivMax, $inmap_pokemons)
    {
        $inmap_pkms_filter = '';
        $where = ' WHERE expire_timestamp >= UNIX_TIMESTAMP() AND pokemon_id = '.$pokemon_id;

        $reqTestIv = 'SELECT MAX(iv) AS iv FROM pokemon '.$where;
        $resultTestIv = $this->mysqli->query($reqTestIv);
        $testIv = $resultTestIv->fetch_object();
        if (!is_null($inmap_pokemons) && ('' != $inmap_pokemons)) {
            foreach ($inmap_pokemons as $inmap) {
                $inmap_pkms_filter .= "'".$inmap."',";
            }
            $inmap_pkms_filter = rtrim($inmap_pkms_filter, ',');
            $where .= ' AND encounter_id NOT IN ('.$inmap_pkms_filter.') ';
        }
        if (null != $testIv->iv && !is_null($ivMin) && ('' != $ivMin) && !($ivMax == 100 && $ivMin == 0)) {
            $where .= ' AND iv >= ('.$ivMin.') ';
        }
        if (null != $testIv->iv && !is_null($ivMax) && ('' != $ivMax) && !($ivMax == 100 && $ivMin == 0)) {
            $where .= ' AND iv <= ('.$ivMax.') ';
        }
        $req = 'SELECT pokemon_id, lat AS latitude, lon AS longitude,
    					FROM_UNIXTIME(expire_timestamp) AS disappear_time,
    					FROM_UNIXTIME(expire_timestamp) AS disappear_time_real,
    					atk_iv AS individual_attack, def_iv AS individual_defense, sta_iv AS individual_stamina,
   						move_1, move_2
					FROM pokemon '.$where.'
					LIMIT 5000';
        $result = $this->mysqli->query($req);
        $spawns = array();
        while ($data = $result->fetch_object()) {
            $spawns[] = $data;
        }

        return $spawns;
    }

    public function getPokemonSliderMinMax()
    {
        $req = 'SELECT FROM_UNIXTIME(MIN(expire_timestamp)) AS min, FROM_UNIXTIME(MAX(expire_timestamp)) AS max FROM pokemon';
        $result = $this->mysqli->query($req);
        $data = $result->fetch_object();

        return $data;
    }

    public function getMapsCoords()
    {
        $req = 'SELECT MAX(lat) AS max_latitude, MIN(lat) AS min_latitude, MAX(lon) AS max_longitude, MIN(lon) as min_longitude FROM spawnpoint';
        $result = $this->mysqli->query($req);
        $data = $result->fetch_object();

        return $data;
    }

    public function getPokemonCount($pokemon_id)
    {
        $req = 'SELECT COALESCE(SUM(count),0) as count, MAX(date) as last_seen_day
					FROM pokemon_stats
					WHERE pokemon_id = '.$pokemon_id;
        $result = $this->mysqli->query($req);
        $data = $result->fetch_object();

        return $data;
    }

    public function getPokemonCountAll()
    {
        $req = 'SELECT pokemon_id, SUM(count) as count, MAX(date) as last_seen_day
					FROM pokemon_stats
					GROUP BY pokemon_id';
        $result = $this->mysqli->query($req);
        $array = array();
        while ($data = $result->fetch_object()) {
            $array[] = $data;
        }

        return $array;
    }


    public function getRaidCount($pokemon_id)
    {
        $req = 'SELECT COALESCE(SUM(count),0) as count, MAX(date) as last_seen_day
					FROM raid_stats
					WHERE pokemon_id = '.$pokemon_id;
        $result = $this->mysqli->query($req);
        $data = $result->fetch_object();

        return $data;
    }

    public function getRaidCountAll()
    {
        $req = 'SELECT pokemon_id, SUM(count) as count, MAX(date) as last_seen_day
					FROM raid_stats
					GROUP BY pokemon_id';
        $result = $this->mysqli->query($req);
        $array = array();
        while ($data = $result->fetch_object()) {
            $array[] = $data;
        }

        return $array;
    }

    ///////////////
    // Pokestops
    //////////////

    public function getTotalPokestops()
    {
        $req = 'SELECT COUNT(*) as total FROM pokestop';
        $result = $this->mysqli->query($req);
        $data = $result->fetch_object();

        return $data;
    }

    public function getAllPokestops()
    {
        $req = 'SELECT lat as latitude, lon as longitude, lure_expire_timestamp AS lure_expiration, UNIX_TIMESTAMP() AS now, FROM_UNIXTIME(lure_expire_timestamp) AS lure_expiration_real FROM pokestop';
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

    public function getTeamGuardians($team_id)
    {
        return array();
    }

    public function getOwnedAndPoints($team_id)
    {
        $req = "SELECT COUNT(id) AS total, ROUND(AVG(total_cp)) AS average_points
        			FROM gym
        			WHERE team_id = '".$team_id."'";
        $result = $this->mysqli->query($req);
        $data = $result->fetch_object();

        return $data;
    }

    public function getAllGyms()
    {
        $req = 'SELECT id as gym_id, team_id, lat as latitude, lon as longitude, updated as last_scanned, (6 - availble_slots) AS level
					FROM gym';
        $result = $this->mysqli->query($req);
        $gyms = array();
        while ($data = $result->fetch_object()) {
            $gyms[] = $data;
        }

        return $gyms;
    }

    public function getGymData($gym_id)
    {
        $req = "SELECT name, null AS description, url, team_id AS team, FROM_UNIXTIME(updated) AS last_scanned, guarding_pokemon_id AS guard_pokemon_id, (6 - availble_slots) AS level, total_cp
                FROM gym
                WHERE id = '".$gym_id."'";
        $result = $this->mysqli->query($req);
        $data = $result->fetch_object();

        return $data;
    }

    public function getGymDefenders($gym_id)
    {
        return array();
    }

    ///////////
    // Raids
    ///////////

    public function getAllRaids($page)
    {
        $limit = ' LIMIT '.($page * 10).',10';
        $req = 'SELECT id AS gym_id, raid_level AS level, raid_pokemon_id AS pokemon_id, raid_pokemon_cp AS cp, raid_pokemon_move_1 AS move_1, raid_pokemon_move_2 AS move_2, FROM_UNIXTIME(raid_spawn_timestamp) AS spawn, FROM_UNIXTIME(raid_battle_timestamp) AS start, FROM_UNIXTIME(raid_end_timestamp) AS end, FROM_UNIXTIME(updated) AS last_scanned, name, lat AS latitude, lon as longitude
                FROM gym
                WHERE raid_end_timestamp > UNIX_TIMESTAMP()
                ORDER BY raid_level DESC, raid_battle_timestamp'.$limit;
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
        return array();
    }

    public function getGymHistoriesPokemon($gym_id)
    {
        return array();
    }

    public function getHistoryForGym($page, $gym_id)
    {
        return array();
    }

    //////////////
    // Trainers
    //////////////

    public function getTrainers($trainer_name, $team, $page, $rankingNumber)
    {
        return array();
    }

    public function getTrainerLevelRanking()
    {
        return array();
    }

    public function getActivePokemon($trainer_name)
    {
        return array();
    }

    public function getInactivePokemon($trainer_name)
    {
        return array();
    }

    public function getTrainerLevelCount($team_id)
    {
        return array();
    }

    /////////
    // Cron
    /////////

    public function getPokemonCountsActive()
    {
        $req = 'SELECT pokemon_id, COUNT(*) as total FROM pokemon WHERE expire_timestamp >= UNIX_TIMESTAMP() GROUP BY pokemon_id';
        $result = $this->mysqli->query($req);
        $counts = array();
        while ($data = $result->fetch_object()) {
            $counts[$data->pokemon_id] = $data->total;
        }

        return $counts;
    }

    public function getTotalPokemonIV()
    {
        $req = 'SELECT COUNT(*) as total FROM pokemon WHERE expire_timestamp >= UNIX_TIMESTAMP() AND iv IS NOT NULL';
        $result = $this->mysqli->query($req);
        $data = $result->fetch_object();

        return $data;
    }

    public function getPokemonCountsLastDay()
    {
        $req = 'SELECT pokemon_id, COUNT(*) AS spawns_last_day
					FROM pokemon
					WHERE expire_timestamp >= (SELECT MAX(expire_timestamp) - 86400 FROM pokemon)
					GROUP BY pokemon_id
				  	ORDER BY pokemon_id ASC';
        $result = $this->mysqli->query($req);
        $counts = array();
        while ($data = $result->fetch_object()) {
            $counts[$data->pokemon_id] = $data->spawns_last_day;
        }

        return $counts;
    }

    public function getCaptchaCount()
    {
        return ["total"=>0];
    }

    public function getNestData($time, $minLatitude, $maxLatitude, $minLongitude, $maxLongitude)
    {
        $pokemon_exclude_sql = '';
        if (!empty(self::$config->system->nest_exclude_pokemon)) {
            $pokemon_exclude_sql = 'AND p.pokemon_id NOT IN ('.implode(',', self::$config->system->nest_exclude_pokemon).')';
        }

        $req = 'SELECT p.pokemon_id, MAX(p.lat) AS latitude, MAX(p.lon) AS longitude, count(p.pokemon_id) AS total_pokemon, MAX(p.updated) as latest_seen, 0 as duration
			          FROM pokemon p
			          WHERE p.expire_timestamp > UNIX_TIMESTAMP() - '.($time * 3600).' AND p.spawn_id IS NOT NULL
			            AND p.lat >= '.$minLatitude.' AND p.lat < '.$maxLatitude.' AND p.lon >= '.$minLongitude.' AND p.lon < '.$maxLongitude.'
			          '.$pokemon_exclude_sql.'
			          GROUP BY p.spawn_id, p.pokemon_id
			          HAVING COUNT(p.pokemon_id) >= '.($time / 4).'
			          ORDER BY p.pokemon_id';
        $result = $this->mysqli->query($req);
        $nests = array();
        while ($data = $result->fetch_object()) {
            $nests[] = $data;
        }
        $req = 'SELECT p.pokemon_id, MAX(p.lat) AS latitude, MAX(p.lon) AS longitude, count(p.pokemon_id) AS total_pokemon, MAX(p.updated) as latest_seen, 0 as duration
			          FROM pokemon p
			          WHERE p.expire_timestamp > UNIX_TIMESTAMP() - '.($time * 3600).' AND p.pokestop_id IS NOT NULL
			            AND p.lat >= '.$minLatitude.' AND p.lat < '.$maxLatitude.' AND p.lon >= '.$minLongitude.' AND p.lon < '.$maxLongitude.'
			          '.$pokemon_exclude_sql.'
			          GROUP BY p.pokestop_id, p.pokemon_id
			          HAVING COUNT(p.pokemon_id) >= '.($time / 4).'
			          ORDER BY p.pokemon_id';
        $result = $this->mysqli->query($req);
        while ($data = $result->fetch_object()) {
            $nests[] = $data;
        }

        return $nests;
    }

    public function getSpawnpointCount($minLatitude, $maxLatitude, $minLongitude, $maxLongitude)
    {
        $req = 'SELECT COUNT(*) as total 
					FROM spawnpoint
 					WHERE lat >= '.$minLatitude.' AND lat < '.$maxLatitude.' AND lon >= '.$minLongitude.' AND lon < '.$maxLongitude;
        $result = $this->mysqli->query($req);
        $data = $result->fetch_object();

        return $data;
    }
}
