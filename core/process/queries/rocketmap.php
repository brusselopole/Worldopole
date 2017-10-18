<?php

// Manage Time Interval
#######################
include_once('../timezone.loader.php');


// Genearl
##########

function req_maps_localization_coordinates()
{
    return "SELECT MAX(latitude) AS max_latitude, MIN(latitude) AS min_latitude, MAX(longitude) AS max_longitude, MIN(longitude) AS min_longitude FROM spawnpoint";

}

// Pokemon
############

function req_pokemon_count()
{
    return "SELECT COUNT(*) AS total FROM pokemon WHERE disappear_time >= UTC_TIMESTAMP()";
}

function req_pokemon_count_id()
{
    return "SELECT pokemon_id FROM pokemon WHERE disappear_time >= UTC_TIMESTAMP()";

}

function req_mystic_pokemon($mythic_pokemon)
{
    global $time_offset;
    return "SELECT DISTINCT pokemon_id, encounter_id, disappear_time, last_modified, (CONVERT_TZ(disappear_time, '+00:00', '" . $time_offset . "')) AS disappear_time_real,
	            latitude, longitude, cp, individual_attack, individual_defense, individual_stamina
                FROM pokemon
                WHERE pokemon_id IN (" . implode(",", $mythic_pokemon) . ")
                ORDER BY last_modified DESC
                LIMIT 0,12";
}

function req_all_pokemon()
{
    global $time_offset;
    return "SELECT DISTINCT pokemon_id, encounter_id, disappear_time, last_modified, (CONVERT_TZ(disappear_time, '+00:00', '" . $time_offset . "')) AS disappear_time_real,
                latitude, longitude, cp, individual_attack, individual_defense, individual_stamina
                FROM pokemon
                ORDER BY last_modified DESC
                LIMIT 0,12";
}


// Single Pokemon
##########

function req_pokemon_total_count($pokemon_id)
{
    return "SELECT COUNT(*) AS pokemon_spawns FROM pokemon WHERE pokemon_id = '" . $pokemon_id . "'";
}

function req_pokemon_total_gym_protected($pokemon_id)
{
    return "SELECT COUNT(DISTINCT(gym_id)) AS total FROM gym WHERE guard_pokemon_id = '" . $pokemon_id . "'";
}

function req_pokemon_last_seen($pokemon_id)
{
    global $time_offset;
    return "SELECT disappear_time, (CONVERT_TZ(disappear_time, '+00:00', '" . $time_offset . "')) AS disappear_time_real, latitude, longitude
	            FROM pokemon
	            WHERE pokemon_id = '" . $pokemon_id . "'
	            ORDER BY disappear_time DESC
	            LIMIT 0,1";
}

function req_pokemon_get_top_50($pokemon_id, $top_order_by, $top_direction)
{
    global $time_offset;
    return "SELECT (CONVERT_TZ(disappear_time, '+00:00', '" . $time_offset . "')) AS distime, pokemon_id, disappear_time, latitude, longitude,
	            cp, individual_attack, individual_defense, individual_stamina,
	            ROUND(SUM(100*(individual_attack+individual_defense+individual_stamina)/45),1) AS IV, move_1, move_2, form
	            FROM pokemon
	            WHERE pokemon_id = '" . $pokemon_id . "' AND move_1 IS NOT NULL AND move_1 <> '0'
	            GROUP BY encounter_id
	            ORDER BY $top_order_by $top_direction, disappear_time DESC
	            LIMIT 0,50";
}

function req_pokemon_get_top_trainers($pokemon_id, $best_order_by, $best_direction)
{
    global $config;
    $trainer_blacklist = "";
    if (!empty($config->system->trainer_blacklist)) {
        $trainer_blacklist = " AND trainer_name NOT IN ('" . implode("','", $config->system->trainer_blacklist) . "')";
    }
    return "SELECT trainer_name, ROUND(SUM(100*(iv_attack+iv_defense+iv_stamina)/45),1) AS IV, move_1, move_2, cp,
				DATE_FORMAT(last_seen, '%Y-%m-%d') AS lasttime, last_seen
				FROM gympokemon
				WHERE pokemon_id = '" . $pokemon_id . "'" . $trainer_blacklist . "
				GROUP BY pokemon_uid
				ORDER BY $best_order_by $best_direction, trainer_name ASC
				LIMIT 0,50";
}

function req_pokemon_slider_init()
{
    return "SELECT MIN(disappear_time) AS min, MAX(disappear_time) AS max FROM pokemon";
}

function req_pokemon_headmap_points($pokemon_id, $start, $end)
{
    $where = " WHERE pokemon_id = " . $pokemon_id . " "
        . "AND disappear_time BETWEEN '" . $start . "' AND '" . $end . "'";
    return "SELECT latitude, longitude FROM pokemon" . $where . " ORDER BY disappear_time DESC LIMIT 10000";
}

function req_pokemon_graph_data($pokemon_id)
{
    global $time_offset;
    return "SELECT COUNT(*) AS total,
			    HOUR(CONVERT_TZ(disappear_time, '+00:00', '" . $time_offset . "')) AS disappear_hour
				FROM (SELECT disappear_time FROM pokemon WHERE pokemon_id = '" . $pokemon_id . "' ORDER BY disappear_time LIMIT 10000) AS pokemonFiltered
				GROUP BY disappear_hour
				ORDER BY disappear_hour";
}

function req_pokemon_live_data_test($pokemon_id)
{
    $where = " WHERE disappear_time >= UTC_TIMESTAMP() AND pokemon_id = " . $pokemon_id;
    return "SELECT MAX(individual_attack) AS iv FROM pokemon " . $where;
}

function req_pokemon_live_data($pokemon_id, $testIv, $post)
{
    global $mysqli, $time_offset;
    $inmap_pkms_filter = "";
    $where = " WHERE disappear_time >= UTC_TIMESTAMP() AND pokemon_id = " . $pokemon_id;
    if (isset($post['inmap_pokemons']) && ($post['inmap_pokemons'] != "")) {
        foreach ($post['inmap_pokemons'] as $inmap) {
            $inmap_pkms_filter .= "'" . $inmap . "',";
        }
        $inmap_pkms_filter = rtrim($inmap_pkms_filter, ",");
        $where .= " AND encounter_id NOT IN (" . $inmap_pkms_filter . ") ";
    }
    if ($testIv->iv != null && isset($post['ivMin']) && ($post['ivMin'] != "")) {
        $ivMin = mysqli_real_escape_string($mysqli, $post['ivMin']);
        $where .= " AND ((100/45)*(individual_attack+individual_defense+individual_stamina)) >= (" . $ivMin . ") ";
    }
    if ($testIv->iv != null && isset($post['ivMax']) && ($post['ivMax'] != "")) {
        $ivMax = mysqli_real_escape_string($mysqli, $post['ivMax']);
        $where .= " AND ((100/45)*(individual_attack+individual_defense+individual_stamina)) <=(" . $ivMax . ") ";
    }
    return "SELECT pokemon_id, encounter_id, latitude, longitude, disappear_time,
						(CONVERT_TZ(disappear_time, '+00:00', '" . $time_offset . "')) AS disappear_time_real,
						individual_attack, individual_defense, individual_stamina, move_1, move_2
						FROM pokemon " . $where . "
						ORDER BY disappear_time DESC
						LIMIT 5000";
}

function req_pokemon_count_24h()
{
    return "SELECT pokemon_id, COUNT(*) AS spawns_last_day
		FROM pokemon
		WHERE disappear_time >= (SELECT MAX(disappear_time) FROM pokemon) - INTERVAL 1 DAY
		GROUP BY pokemon_id
		ORDER BY pokemon_id ASC";
}


// Pokestops
############

function req_pokestop_count()
{
    return "SELECT COUNT(*) AS total FROM pokestop";
}

function req_pokestop_lure_count()
{
    return "SELECT COUNT(*) AS total FROM pokestop WHERE lure_expiration >= UTC_TIMESTAMP()";
}

function req_pokestop_data()
{
    global $time_offset;
    return "SELECT latitude, longitude, lure_expiration, UTC_TIMESTAMP() AS now, (CONVERT_TZ(lure_expiration, '+00:00', '" . $time_offset . "')) AS lure_expiration_real FROM pokestop ";
}

// Gyms
#######

function req_gym_count()
{
    return "SELECT COUNT(DISTINCT(gym_id)) AS total FROM gym";
}

function req_gym_count_for_team($team_id)
{
    return "SELECT COUNT(DISTINCT(gym_id)) AS total FROM gym WHERE team_id = '$team_id'";
}

function req_gym_guards_for_team($team_id)
{
    return "SELECT COUNT(*) AS total, guard_pokemon_id FROM gym WHERE team_id = '$team_id' GROUP BY guard_pokemon_id ORDER BY total DESC LIMIT 0,3";
}

function req_gym_count_cp_for_team($team_id)
{
    return "SELECT COUNT(DISTINCT(gym_id)) AS total, ROUND(AVG(total_cp),0) AS average_points FROM gym WHERE team_id = '$team_id'";
}

function req_gym_data()
{
    global $time_offset;
    return "SELECT gym_id, team_id, latitude, longitude, (CONVERT_TZ(last_scanned, '+00:00', '" . $time_offset . "')) AS last_scanned, (6 - slots_available) AS level FROM gym";
}

function req_gym_data_simple($gym_id)
{
    global $time_offset;
    return "SELECT gym_id, team_id, guard_pokemon_id, latitude, longitude, (CONVERT_TZ(last_scanned, '+00:00', '" . $time_offset . "')) AS last_scanned, total_cp, (6 - slots_available) AS level
				FROM gym WHERE gym_id='" . $gym_id . "'";
}

function req_gym_defender_for($gym_id)
{
    global $time_offset;
    return "SELECT gymdetails.name AS name, gymdetails.description AS description, gymdetails.url AS url, gym.team_id AS team,
	            (CONVERT_TZ(gym.last_scanned, '+00:00', '" . $time_offset . "')) AS last_scanned, gym.guard_pokemon_id AS guard_pokemon_id, gym.total_cp AS total_cp, (6 - gym.slots_available) AS level
			    FROM gymdetails
			    LEFT JOIN gym ON gym.gym_id = gymdetails.gym_id
			    WHERE gym.gym_id='" . $gym_id . "'";
}

function req_gym_defender_stats_for($gym_id)
{
    return "SELECT DISTINCT gympokemon.pokemon_uid, pokemon_id, iv_attack, iv_defense, iv_stamina, MAX(cp) AS cp, gymmember.gym_id
			    FROM gympokemon INNER JOIN gymmember ON gympokemon.pokemon_uid=gymmember.pokemon_uid
			    GROUP BY gympokemon.pokemon_uid, pokemon_id, iv_attack, iv_defense, iv_stamina, gym_id
			    HAVING gymmember.gym_id='" . $gym_id . "'
			    ORDER BY cp DESC";
}

// Trainer
##########

function req_trainers($get)
{
    global $config, $mysqli;
    $name = "";
    $page = "0";
    $where = "";
    $order = "";
    $team = 0;
    $ranking = 0;
    if (isset($get['name'])) {
        $trainer_name = mysqli_real_escape_string($mysqli, $get['name']);
        $where = " HAVING name LIKE '%" . $trainer_name . "%'";
    }
    if (isset($get['team']) && $get['team'] != 0) {
        $team = mysqli_real_escape_string($mysqli, $get['team']);
        $where .= ($where == "" ? " HAVING" : " AND") . " team = " . $team;
    }
    if (!empty($config->system->trainer_blacklist)) {
        $where .= ($where == "" ? " HAVING" : " AND") . " name NOT IN ('" . implode("','", $config->system->trainer_blacklist) . "')";
    }
    if (isset($get['page'])) {
        $page = mysqli_real_escape_string($mysqli, $get['page']);
    }
    if (isset($get['ranking'])) {
        $ranking = mysqli_real_escape_string($mysqli, $get['ranking']);
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
    $limit = " LIMIT " . ($page * 10) . ",10 ";
    return "SELECT trainer.*, COUNT(actives_pokemons.trainer_name) AS active, max(actives_pokemons.cp) AS maxCp
				FROM trainer
				LEFT JOIN (SELECT DISTINCT gympokemon.pokemon_id, gympokemon.pokemon_uid, gympokemon.trainer_name, gympokemon.cp, DATEDIFF(UTC_TIMESTAMP(), gympokemon.last_seen) AS last_scanned
				FROM gympokemon
				INNER JOIN (SELECT gymmember.pokemon_uid, gymmember.gym_id FROM gymmember GROUP BY gymmember.pokemon_uid, gymmember.gym_id HAVING gymmember.gym_id <> '') AS filtered_gymmember
				ON gympokemon.pokemon_uid = filtered_gymmember.pokemon_uid) AS actives_pokemons ON actives_pokemons.trainer_name = trainer.name
				GROUP BY trainer.name " . $where . $order . $limit;
}

function req_trainer_active_pokemon($name)
{
    global $time_offset;
    return "(SELECT DISTINCT gympokemon.pokemon_id, gympokemon.pokemon_uid, gympokemon.cp, DATEDIFF(UTC_TIMESTAMP(), gympokemon.last_seen) AS last_scanned, gympokemon.trainer_name, gympokemon.iv_defense, gympokemon.iv_stamina, gympokemon.iv_attack, filtered_gymmember.gym_id, CONVERT_TZ(filtered_gymmember.deployment_time, '+00:00', '" . $time_offset . "') as deployment_time, '1' AS active
	            FROM gympokemon INNER JOIN
				(SELECT gymmember.pokemon_uid, gymmember.gym_id, gymmember.deployment_time FROM gymmember GROUP BY gymmember.pokemon_uid, gymmember.deployment_time, gymmember.gym_id HAVING gymmember.gym_id <> '') AS filtered_gymmember
				ON gympokemon.pokemon_uid = filtered_gymmember.pokemon_uid
				WHERE gympokemon.trainer_name='" . $name . "'
				ORDER BY gympokemon.cp DESC)";
}

function req_trainer_inactive_pokemon($name)
{
    global $time_offset;
    return "(SELECT DISTINCT gympokemon.pokemon_id, gympokemon.pokemon_uid, gympokemon.cp, DATEDIFF(UTC_TIMESTAMP(), gympokemon.last_seen) AS last_scanned, gympokemon.trainer_name, gympokemon.iv_defense, gympokemon.iv_stamina, gympokemon.iv_attack, null AS gym_id, CONVERT_TZ(filtered_gymmember.deployment_time, '+00:00', '" . $time_offset . "') as deployment_time, '0' AS active
				FROM gympokemon LEFT JOIN
				(SELECT * FROM gymmember HAVING gymmember.gym_id <> '') AS filtered_gymmember
				ON gympokemon.pokemon_uid = filtered_gymmember.pokemon_uid
				WHERE filtered_gymmember.pokemon_uid IS NULL AND gympokemon.trainer_name='" . $name . "'
				ORDER BY gympokemon.cp DESC)";
}

function req_trainer_ranking($trainer)
{
    global $config;
    $reqRanking = "SELECT COUNT(1) AS rank FROM trainer WHERE level = " . $trainer->level;
    if (!empty($config->system->trainer_blacklist)) {
        $reqRanking .= " AND name NOT IN ('" . implode("','", $config->system->trainer_blacklist) . "')";
    }
    return $reqRanking;
}

function req_trainer_levels_for_team($teamid)
{
    global $config;
    $reqLevels = "SELECT level, count(level) AS count FROM trainer WHERE team = '" . $teamid . "'";
    if (!empty($config->system->trainer_blacklist)) {
        $reqLevels .= " AND name NOT IN ('" . implode("','", $config->system->trainer_blacklist) . "')";
    }
    $reqLevels .= " GROUP BY level";
    return $reqLevels;
}

// Raids
########

function req_raids_data($page)
{
    global $time_offset;
    $limit = " LIMIT " . ($page * 10) . ",10";
    return "SELECT raid.gym_id, raid.level, raid.pokemon_id, raid.cp, raid.move_1, raid.move_2, CONVERT_TZ(raid.spawn, '+00:00', '" . $time_offset . "') AS spawn, CONVERT_TZ(raid.start, '+00:00', '" . $time_offset . "') AS start, CONVERT_TZ(raid.end, '+00:00', '" . $time_offset . "') AS end, CONVERT_TZ(raid.last_scanned, '+00:00', '" . $time_offset . "') AS last_scanned, gymdetails.name, gym.latitude, gym.longitude FROM raid
				JOIN gymdetails ON gymdetails.gym_id = raid.gym_id
				JOIN gym ON gym.gym_id = raid.gym_id
				WHERE raid.end > UTC_TIMESTAMP()
				ORDER BY raid.level DESC, raid.start" . $limit;
}

// Captcha
##########

function req_captcha_count()
{
    return "SELECT SUM(accounts_captcha) AS total FROM mainworker";

}

// Test
#######

function req_tester_pokemon()
{
    return "SELECT COUNT(*) AS total FROM pokemon";
}

function req_tester_gym()
{
    return "SELECT COUNT(*) AS total FROM gym";
}

function req_tester_pokestop()
{
    return "SELECT COUNT(*) AS total FROM pokestop";
}

// Nests
########

function req_map_data()
{
    global $config;
    $pokemon_exclude_sql = "";
    if (!empty($config->system->nest_exclude_pokemon)) {
        $pokemon_exclude_sql = "AND p.pokemon_id NOT IN (" . implode(",", $config->system->nest_exclude_pokemon) . ")";
    }
    return "SELECT p.pokemon_id, max(p.latitude) AS latitude, max(p.longitude) AS longitude, count(p.pokemon_id) AS total_pokemon, s.latest_seen, (LENGTH(s.kind) - LENGTH( REPLACE ( kind, \"s\", \"\") )) * 900 AS duration
          FROM pokemon p 
          INNER JOIN spawnpoint s ON (p.spawnpoint_id = s.id) 
          WHERE p.disappear_time > UTC_TIMESTAMP() - INTERVAL 24 HOUR 
          " . $pokemon_exclude_sql . " 
          GROUP BY p.spawnpoint_id, p.pokemon_id 
          HAVING COUNT(p.pokemon_id) >= 6 
          ORDER BY p.pokemon_id";
}