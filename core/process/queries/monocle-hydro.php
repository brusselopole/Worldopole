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
	$req = "SELECT COUNT(*) as total FROM sightings";
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
	$req = "SELECT COUNT(*) as total FROM forts";
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
	$req = "SELECT COUNT(*) as total FROM pokestops";
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
    $req = "SELECT COUNT(*) AS total FROM sightings WHERE expire_timestamp >= UNIX_TIMESTAMP()";
    $result = $mysqli->query($req);
    $data = $result->fetch_object();
    return $data;
}

function getTotalLures() {
    $data = (object) array("total" => 0);
    return $data;
}

function getTotalGyms() {
    global $mysqli;
    $req = "SELECT COUNT(*) AS total FROM forts";
    $result = $mysqli->query($req);
    $data = $result->fetch_object();
    return $data;
}

function getTotalRaids() {
	global $mysqli;
	$req = "SELECT COUNT(*) AS total FROM raids WHERE time_battle <= UNIX_TIMESTAMP() AND time_end >= UNIX_TIMESTAMP()";
	$result = $mysqli->query($req);
	$data = $result->fetch_object();
	return $data;
}


function getTotalGymsForTeam($team_id) {
    global $mysqli;
    $req = "SELECT COUNT(*) AS total FROM fort_sightings WHERE team = '$team_id'";
    $result = $mysqli->query($req);
    $data = $result->fetch_object();
    return $data;
}

function getRecentAll() {
    global $mysqli;
    $req = "SELECT DISTINCT pokemon_id, encounter_id, FROM_UNIXTIME(expire_timestamp) AS disappear_time, FROM_UNIXTIME(updated+0) AS last_modified, FROM_UNIXTIME(expire_timestamp) AS disappear_time_real,
              lat AS latitude, lon AS longitude, cp, atk_iv AS individual_attack, def_iv AS individual_defense, sta_iv AS individual_stamina
              FROM sightings
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

function getRecentMythic($mythic_pokemon) {
    global $mysqli;
    $req = "SELECT DISTINCT pokemon_id as pokemon_id, CONCAT('A', encounter_id) as encounter_id, FROM_UNIXTIME(expire_timestamp) AS disappear_time, FROM_UNIXTIME(updated+0) AS last_modified, FROM_UNIXTIME(expire_timestamp) AS disappear_time_real,
                lat AS latitude, lon AS longitude, cp, atk_iv AS individual_attack, def_iv AS individual_defense, sta_iv AS individual_stamina
                FROM sightings
                WHERE pokemon_id+0 IN (".implode(",", $mythic_pokemon).")
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
    $req = "SELECT COUNT(DISTINCT(fort_id)) AS total FROM fort_sightings WHERE guard_pokemon_id = '".$pokemon_id."'";
    $result = $mysqli->query($req);
    $data = $result->fetch_object();
    return $data;
}

function getPokemonLastSeen($pokemon_id) {
    global $mysqli, $time_offset;
    $req = "SELECT FROM_UNIXTIME(expire_timestamp) AS expire_timestamp, FROM_UNIXTIME(expire_timestamp) AS disappear_time_real, lat AS latitude, lon AS longitude
                FROM sightings
                WHERE pokemon_id = '".$pokemon_id."'
                ORDER BY expire_timestamp DESC
                LIMIT 0,1";
    $result = $mysqli->query($req);
    $data = $result->fetch_object();
    return $data;
}

function getTop50Pokemon($pokemon_id) {
    global $mysqli, $best_order, $best_direction;
    // Make it sortable; default sort: cp DESC
    $top_possible_sort = array('IV', 'cp', 'individual_attack', 'individual_defense', 'individual_stamina', 'move_1', 'move_2', 'disappear_time');
    $top_order = isset($_GET['order']) ? $_GET['order'] : '';
    $top_order_by = in_array($top_order, $top_possible_sort) ? $_GET['order'] : 'cp';
    $top_direction = isset($_GET['direction']) ? 'ASC' : 'DESC';
    $top_direction = !isset($_GET['order']) && !isset($_GET['direction']) ? 'DESC' : $top_direction;

    $req = "SELECT FROM_UNIXTIME(expire_timestamp+0) AS distime, pokemon_id+0 as pokemon_id, FROM_UNIXTIME(expire_timestamp+0) as disappear_time, lat as latitude, lon as longitude,
                cp, atk_iv+0 as individual_attack, def_iv+0 as individual_defense, sta_iv+0 as individual_stamina,
                ROUND(100*(atk_iv+def_iv+sta_iv)/45,1) AS IV, move_1+0 as move_1, move_2, form
                FROM sightings
	            WHERE pokemon_id+0 = '" . $pokemon_id . "' AND move_1+0 IS NOT NULL AND move_1+0 <> '0'
	            GROUP BY encounter_id+0
	            ORDER BY $top_order_by $top_direction, expire_timestamp+0 DESC
	            LIMIT 0,50";

    $result = $mysqli->query($req);
    $top = array();
    while ($data = $result->fetch_object()) {
        $top[] = $data;
    }
    return $top;
}

function getTop50Trainers($pokemon_id) {
    global $mysqli, $best_order, $best_direction;
    $best_possible_sort = array('trainer_name', 'IV', 'cp', 'move_1', 'move_2', 'last_seen');
    $best_order = isset($_GET['order']) ? $_GET['order'] : '';
    $best_order_by = in_array($best_order, $best_possible_sort) ? $_GET['order'] : 'cp';
    $best_direction = isset($_GET['direction']) ? 'ASC' : 'DESC';
    $best_direction = !isset($_GET['order']) && !isset($_GET['direction']) ? 'DESC' : $best_direction;

    $trainer_blacklist = "";
    if (!empty($config->system->trainer_blacklist)) {
        $trainer_blacklist = " AND owner_name NOT IN ('" . implode("','", $config->system->trainer_blacklist) . "')";
    }

    $req = "SELECT owner_name as trainer_name, ROUND(SUM(100*((atk_iv+0)+(def_iv+0)+(sta_iv+0))/45),1) AS IV, move_1, move_2, cp+0 as cp,
                FROM_UNIXTIME(last_modified+0) AS lasttime, last_modified+0 as last_seen
                FROM gym_defenders
				WHERE pokemon_id+0 = '" . $pokemon_id . "'" . $trainer_blacklist . "
				GROUP BY external_id
				ORDER BY $best_order_by $best_direction, owner_name+'' ASC
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
	$req = "SELECT COUNT(*) as total FROM pokestops";
	$result = $mysqli->query($req);
	$data = $result->fetch_object();
	return $data;
}


/////////
// Gyms
/////////

function getTeamGuardians($team_id) {
	global $mysqli;
	$req = "SELECT COUNT(*) AS total, guard_pokemon_id FROM fort_sightings WHERE team = '".$team_id."' GROUP BY guard_pokemon_id ORDER BY total DESC LIMIT 0,3";
	$result = $mysqli->query($req);

	$datas = array();
	while ($data = $result->fetch_object()) {
		$datas[] = $data;
	}

	return $datas;
}

function getOwnedAndPoints($team_id) {
	global $mysqli;
	$req 	= "SELECT COUNT(DISTINCT(fs.fort_id)) AS total, ROUND((SUM(gd.cp)) / COUNT(DISTINCT(fs.fort_id)),0) AS average_points
        			FROM fort_sightings fs
        			JOIN gym_defenders gd ON fs.fort_id = gd.fort_id
        			WHERE fs.team = '".$team_id."'";
	$result = $mysqli->query($req);
	$data = $result->fetch_object();
	return $data;
}