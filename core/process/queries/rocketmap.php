<?php

$mysqli = new mysqli(SYS_DB_HOST, SYS_DB_USER, SYS_DB_PSWD, SYS_DB_NAME, SYS_DB_PORT);
if ($mysqli->connect_error != '') {
    header('Location:'.HOST_URL.'offline.html');
    exit();
}
$mysqli->set_charset('utf8');

if ($mysqli->connect_error != '') {
	header('Location:'.HOST_URL.'offline.html');
	exit();
}


/////////
// Misc
/////////

function getExcapedPokemonID($string)
{
	global $mysqli;
	return mysqli_real_escape_string($mysqli, $string);
}


///////////
// Tester
///////////

function testTotalPokemon() {
	global $mysqli;
	$req = "SELECT COUNT(*) as total FROM pokemon";
	$result = $mysqli->query($req);
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
	global $mysqli;
	$req = "SELECT COUNT(*) as total FROM gym";
	$result = $mysqli->query($req);
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
	global $mysqli;
	$req = "SELECT COUNT(*) as total FROM pokestop";
	$result = $mysqli->query($req);
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
    global $mysqli;
    $req = "SELECT COUNT(*) AS total FROM pokemon WHERE disappear_time >= UTC_TIMESTAMP()";
    $result = $mysqli->query($req);
    $data = $result->fetch_object();
    return $data;
}

function getTotalLures() {
    global $mysqli;
    $req = "SELECT COUNT(*) AS total FROM pokestop WHERE lure_expiration >= UTC_TIMESTAMP()";
    $result = $mysqli->query($req);
    $data = $result->fetch_object();
    return $data;
}

function getTotalGyms() {
    global $mysqli;
    $req = "SELECT COUNT(DISTINCT(gym_id)) AS total FROM gym";
    $result = $mysqli->query($req);
    $data = $result->fetch_object();
    return $data;
}

function getTotalRaids() {
	global $mysqli;
	$req = "SELECT COUNT(*) AS total FROM raid WHERE start <= UTC_TIMESTAMP AND  end >= UTC_TIMESTAMP()";
	$result = $mysqli->query($req);
	$data = $result->fetch_object();
	return $data;
}

function getTotalGymsForTeam($team_id) {
    global $mysqli;
    $req = "SELECT COUNT(DISTINCT(gym_id)) AS total FROM gym WHERE team_id = '".$team_id."'";
    $result = $mysqli->query($req);
    $data = $result->fetch_object();
    return $data;
}

function getRecentAll() {
    global $mysqli, $time_offset;
    $req = "SELECT DISTINCT pokemon_id, encounter_id, disappear_time, last_modified, (CONVERT_TZ(disappear_time, '+00:00', '".$time_offset."')) AS disappear_time_real,
				latitude, longitude, cp, individual_attack, individual_defense, individual_stamina
				FROM pokemon
				ORDER BY last_modified DESC
				LIMIT 0,12";
    $result = $mysqli->query($req);
    $data = array();
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_object()) {
            $data[] = $row;
        }
    }
    return $data;
}

function getRecentMythic($mythic_pokemons) {
    global $mysqli, $time_offset;
    $req = "SELECT DISTINCT pokemon_id, encounter_id, disappear_time, last_modified, (CONVERT_TZ(disappear_time, '+00:00', '".$time_offset."')) AS disappear_time_real,
				latitude, longitude, cp, individual_attack, individual_defense, individual_stamina
				FROM pokemon
				WHERE pokemon_id IN (".implode(",", $mythic_pokemons).")
				ORDER BY last_modified DESC
				LIMIT 0,12";
    $result = $mysqli->query($req);
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
    global $mysqli;
    $req = "SELECT COUNT(DISTINCT(gym_id)) AS total FROM gym WHERE guard_pokemon_id = '".$pokemon_id."'";
    $result = $mysqli->query($req);
    $data = $result->fetch_object();
    return $data;
}

function getPokemonLastSeen($pokemon_id) {
    global $mysqli, $time_offset;
    $req = "SELECT disappear_time, (CONVERT_TZ(disappear_time, '+00:00', '".$time_offset."')) AS disappear_time_real, latitude, longitude
						FROM pokemon
						WHERE pokemon_id = '".$pokemon_id."'
						ORDER BY disappear_time DESC
						LIMIT 0,1";
    $result = $mysqli->query($req);
    $data = $result->fetch_object();
    return $data;
}

function getTop50Pokemon($pokemon_id) {
    global $mysqli, $time_offset, $best_order, $best_direction;
    // Make it sortable; default sort: cp DESC
    $top_possible_sort = array('IV', 'cp', 'individual_attack', 'individual_defense', 'individual_stamina', 'move_1', 'move_2', 'disappear_time');
    $top_order = isset($_GET['order']) ? $_GET['order'] : '';
    $top_order_by = in_array($top_order, $top_possible_sort) ? $_GET['order'] : 'cp';
    $top_direction = isset($_GET['direction']) ? 'ASC' : 'DESC';
    $top_direction = !isset($_GET['order']) && !isset($_GET['direction']) ? 'DESC' : $top_direction;

    $req = "SELECT (CONVERT_TZ(disappear_time, '+00:00', '".$time_offset."')) AS distime, pokemon_id, disappear_time, latitude, longitude,
						cp, individual_attack, individual_defense, individual_stamina,
						ROUND(100*(individual_attack+individual_defense+individual_stamina)/45,1) AS IV, move_1, move_2, form
						FROM pokemon
						WHERE pokemon_id = '".$pokemon_id."' AND move_1 IS NOT NULL AND move_1 <> '0'
						GROUP BY encounter_id
						ORDER BY $top_order_by $top_direction, disappear_time DESC
						LIMIT 0,50";

    $result = $mysqli->query($req);
    $top = array();
    while ($data = $result->fetch_object()) {
        $top[] = $data;
    }
    return $top;
}

function getTop50Trainers($pokemon_id) {
    global $mysqli, $time_offset, $best_order, $best_direction;
    $best_possible_sort = array('trainer_name', 'IV', 'cp', 'move_1', 'move_2', 'last_seen');
    $best_order = isset($_GET['order']) ? $_GET['order'] : '';
    $best_order_by = in_array($best_order, $best_possible_sort) ? $_GET['order'] : 'cp';
    $best_direction = isset($_GET['direction']) ? 'ASC' : 'DESC';
    $best_direction = !isset($_GET['order']) && !isset($_GET['direction']) ? 'DESC' : $best_direction;

    $trainer_blacklist = "";
    if (!empty($config->system->trainer_blacklist)) {
        $trainer_blacklist = " AND trainer_name NOT IN ('".implode("','", $config->system->trainer_blacklist)."')";
    }

    $req = "SELECT trainer_name, ROUND(SUM(100*(iv_attack+iv_defense+iv_stamina)/45),1) AS IV, move_1, move_2, cp,
					DATE_FORMAT(last_seen, '%Y-%m-%d') AS lasttime, last_seen
					FROM gympokemon
					WHERE pokemon_id = '".$pokemon_id."'".$trainer_blacklist."
					GROUP BY pokemon_uid
					ORDER BY $best_order_by $best_direction, trainer_name ASC
					LIMIT 0,50";

    $result = $mysqli->query($req);
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
	global $mysqli;
	$req = "SELECT COUNT(*) as total FROM pokestop";
	$result = $mysqli->query($req);
	$data = $result->fetch_object();
	return $data;
}


/////////
// Gyms
/////////

function getTeamGuardians($team_id) {
	global $mysqli;
	$req = "SELECT COUNT(*) AS total, guard_pokemon_id FROM gym WHERE team_id = '".$team_id ."' GROUP BY guard_pokemon_id ORDER BY total DESC LIMIT 0,3";
	$result = $mysqli->query($req);

	$datas = array();
	while ($data = $result->fetch_object()) {
		$datas[] = $data;
	}

	return $datas;
}

function getOwnedAndPoints($team_id) {
	global $mysqli;
	$req 	= "SELECT COUNT(DISTINCT(gym_id)) AS total, ROUND(AVG(total_cp),0) AS average_points FROM gym WHERE team_id = '".$team_id."'";
	$result = $mysqli->query($req);
	$data = $result->fetch_object();
	return $data;
}
