<?php

// Include & load the variables
// ############################

$variables 	= SYS_PATH.'/core/json/variables.json';
$config 	= json_decode(file_get_contents($variables));

if (!isset($config->system)) {
	echo 'Error: Could not load core/json/variables.json.';
	exit();
}


// Manage Time Interval
// #####################

include_once('timezone.loader.php');



// Debug mode
#############

if (SYS_DEVELOPMENT_MODE) {
	error_reporting(E_ALL);
}


// MySQL Connect
#################


$mysqli 	= new mysqli(SYS_DB_HOST, SYS_DB_USER, SYS_DB_PSWD, SYS_DB_NAME, SYS_DB_PORT);


if ($mysqli->connect_error != '') {
	header('Location:'.HOST_URL.'offline.html');
	exit();
}


// Perform some tests to be sure that we got datas and rights
// If not we lock the website (HA-HA-HA evil laught)
// Those test are performed once.
##############################################################

if (!file_exists(SYS_PATH.'/install/done.lock')) {
	// run install tests
	include_once('install/tester.php');
	run_tests();

	// check for error
	if (file_exists(SYS_PATH.'/install/website.lock')) {
		echo file_get_contents(SYS_PATH.'/install/website.lock');
		exit();
	} else {
		$content = time();
		file_put_contents(SYS_PATH.'/install/done.lock', $content);
	}
}


// Load the locale elements
############################

include_once('locales.loader.php');




// Update the pokemon.rarity.json file
######################################
// ( for Brusselopole we use CRONTAB but as we're not sure that every had access to it we build this really simple false crontab system
// => check filemtime, if > 24h launch an update. )

$pokedex_rarity_filetime	= filemtime($pokedex_rarity_file);
$now				= time();
$diff				= $now - $pokedex_rarity_filetime;

// Update each 24h
$update_delay		= 86400;

if ($diff > $update_delay) {
	include_once(SYS_PATH.'/core/cron/pokemon.rarity.php');
}




##########################
//
// Pages data loading
//
##########################

if (isset($_GET['page'])) {
	$page = htmlentities($_GET['page']);
} else {
	$page = '';
}

if (!empty($page)) {
	switch ($page) {
		// Single Pokemon
		#################

		case 'pokemon':
			// Current Pokemon datas
			// ---------------------


			$pokemon_id 			= mysqli_real_escape_string($mysqli, $_GET['id']);

			if (!is_object($pokemons->pokemon->$pokemon_id)) {
				header('Location:/404');
				exit();
			}


			$pokemon			= new stdClass();
			$pokemon			= $pokemons->pokemon->$pokemon_id;
			$pokemon->id			= $pokemon_id;



			// Some math
			// ----------

			$pokemon->max_cp_percent 	= percent(3581, $pokemon->max_cp);
			$pokemon->max_pv_percent 	= percent(704, $pokemon->max_pv);



			// Get Dabase results
			//-------------------


			// Total gym protected

			$req 		= "SELECT count( DISTINCT(gym_id) ) as total FROM gym WHERE guard_pokemon_id = '".$pokemon_id."'  ";
			$result 	= $mysqli->query($req);
			$data 		= $result->fetch_object();

			$pokemon->protected_gyms = $data->total;


			// Total spawn

			$req 		= "SELECT COUNT(*) as total FROM pokemon WHERE pokemon_id = '".$pokemon_id."'";
			$result 	= $mysqli->query($req);
			$data 		= $result->fetch_object();

			$pokemon->total_spawn 	= $data->total;


			// Spawn rate

			if ($pokemon->total_spawn > 0) {
				$req 		= "SELECT COUNT(DISTINCT DATE(disappear_time)) as total FROM pokemon";
				$result 	= $mysqli->query($req);
				$data		= $result->fetch_object();

				$pokemon->total_days = $data->total;
				$pokemon->spawn_rate 	= round(($pokemon->total_spawn/$pokemon->total_days), 2);
			} else {
				$pokemon->total_days 	= 0;
				$pokemon->spawn_rate	= 0;
			}


			// Last seen

			$req 		= "SELECT disappear_time, (CONVERT_TZ(disappear_time, '+00:00', '".$time_offset."')) as disappear_time_real, latitude, longitude
			FROM pokemon
			WHERE pokemon_id = '".$pokemon_id."'
			ORDER BY disappear_time DESC
			LIMIT 0,1";
			$result 	= $mysqli->query($req);
			$data 		= $result->fetch_object();

			if (isset($data)) {
				$last_spawn 				= $data;

				$pokemon->last_seen			= strtotime($data->disappear_time_real);
				$pokemon->last_position			= new stdClass();
				$pokemon->last_position->latitude 	= $data->latitude;
				$pokemon->last_position->longitude 	= $data->longitude;
			}


			// Related Pokemons
			// ----------------


			foreach ($pokemon->types as $type) {
				$types[] = $type;
			}

			$related = array();
			foreach ($pokemons->pokemon as $pokeid => $test_pokemon) {
				if (!empty($test_pokemon->types)) {
					foreach ($test_pokemon->types as $type) {
						if (in_array($type, $types)) {
							if (!in_array($pokeid, $related)) {
								$related[] = $pokeid;
							}
						}
					}
				}
			}
			sort($related);
			break;



		// Pokedex
		##########

		case 'pokedex':
			// Pokemon List from the JSON file
			// --------------------------------


			$max 		= $config->system->max_pokemon;
			$pokedex 	= new stdClass();

			$req 		= "SELECT COUNT(*) as total,pokemon_id FROM pokemon GROUP by pokemon_id ";
			$result 	= $mysqli->query($req);
			$data_array = array();

			while ($data = $result->fetch_object()) {
				$data_array[$data->pokemon_id] = $data->total;
			};

			for ($i= 1; $i <= $max; $i++) {
				$pokedex->$i			= new stdClass();
				$pokedex->$i->id 		= $i;
				$pokedex->$i->permalink 	= 'pokemon/'.$i;
				$pokedex->$i->img		= 'core/pokemons/'.$i.$config->system->pokeimg_suffix;
				$pokedex->$i->name		= $pokemons->pokemon->$i->name;
				$pokedex->$i->spawn 		= isset($data_array[$i])? $data_array[$i] : 0;
			}


			break;


		// Pokestops
		############

		case 'pokestops':
			$pokestop 	= new stdClass();

			$req 		= "SELECT COUNT(*) as total FROM pokestop";
			$result 	= $mysqli->query($req);
			$data 		= $result->fetch_object();

			$pokestop->total = $data->total;

			$req 		= "SELECT COUNT(*) as total FROM pokestop WHERE lure_expiration >= UTC_TIMESTAMP()";
			$result 	= $mysqli->query($req);
			$data 		= $result->fetch_object();

			$pokestop->lured = $data->total;



			break;


		// Gyms
		########


		case 'gym':
			// 3 Teams (teamm rocket is neutral)
			// 1 Fight

			$teams 				= new stdClass();

			$teams->mystic 			= new stdClass();
			$teams->mystic->guardians 	= new stdClass();
			$teams->mystic->id 		= 1;

			$teams->valor 			= new stdClass();
			$teams->valor->guardians 	= new stdClass();
			$teams->valor->id 		= 2;

			$teams->instinct 		= new stdClass();
			$teams->instinct->guardians 	= new stdClass();
			$teams->instinct->id 		= 3;

			$teams->rocket 			= new stdClass();
			$teams->rocket->guardians 	= new stdClass();
			$teams->rocket->id 		= 0;



			foreach ($teams as $team_key => $team_values) {
				// Team Guardians

				$req 	= "SELECT COUNT(*) as total, guard_pokemon_id FROM gym WHERE team_id = '".$team_values->id."' GROUP BY guard_pokemon_id ORDER BY total DESC LIMIT 0,3 ";
				$result = $mysqli->query($req);

				$i=0;

				while ($data = $result->fetch_object()) {
					$teams->$team_key->guardians->$i	= $data->guard_pokemon_id;

					$i++;
				}


				// Gym owned and average points

				$req 	= "SELECT COUNT(DISTINCT(gym_id)) as total, ROUND(AVG(gym_points),0) as average_points FROM gym WHERE team_id = '".$team_values->id."'  ";
				$result = $mysqli->query($req);
				$data	= $result->fetch_object();

				$teams->$team_key->gym_owned	= $data->total;
				$teams->$team_key->average	= $data->average_points;
			}


			break;





		case 'dashboard':
			// This case is only used for test purpose.

			$stats_file	= SYS_PATH.'/core/json/gym.stats.json';

			if (!is_file($stats_file)) {
				echo "Sorry, no Gym stats file was found. <br> Did you enable cron? ";
				exit();
			}


			$stats_file	= SYS_PATH.'/core/json/pokemon.stats.json';

			if (!is_file($stats_file)) {
				echo "Sorry, no Pokémon stats file was found. <br> Did you enabled cron?";
				exit();
			}


			$stats_file	= SYS_PATH.'/core/json/pokestop.stats.json';

			if (!is_file($stats_file)) {
				echo "Sorry, no Pokéstop stats file was found. <br> Did you enabled cron?";
				exit();
			}

			if ($config->system->captcha_support) {
				$stats_file	= SYS_PATH.'/core/json/captcha.stats.json';

				if (!is_file($stats_file)) {
					echo "Sorry, no Captcha stats file were found  <br> Have you enable cron?";
					exit();
				}
			}
			
			break;
	}
} /////////////
// Homepage
/////////////

else {
	$home = new stdClass();

	// Right now
	// ---------

	$req 		= "SELECT COUNT(*) as total FROM pokemon WHERE disappear_time >= UTC_TIMESTAMP()";
	$result 	= $mysqli->query($req);
	$data 		= $result->fetch_object();


	$home->pokemon_now = $data->total;


	// Lured stops
	// -----------

	$req 		= "SELECT COUNT(*) as total FROM pokestop WHERE lure_expiration >= UTC_TIMESTAMP()";
	$result 	= $mysqli->query($req);
	$data 		= $result->fetch_object();

	$home->pokestop_lured = $data->total;


	// Gyms
	// ----

	$req 		= "SELECT COUNT(DISTINCT(gym_id)) as total FROM gym";
	$result 	= $mysqli->query($req);
	$data 		= $result->fetch_object();

	$home->gyms = $data->total;


	// Recent spawns
	// ------------

	if ($config->system->mythic_recents) {
		// get all mythic pokemon ids
		$mythic_pokemons  = array();
		foreach ($pokemons->pokemon as $id => $pokemon) {
			if ($pokemon->spawn_rate < 0.01) {
				$mythic_pokemons[] = $id;
			}
		}
		// get all mythic pokemon
		$req 		= "SELECT DISTINCT pokemon_id, encounter_id, disappear_time, last_modified, (CONVERT_TZ(disappear_time, '+00:00', '".$time_offset."')) as disappear_time_real, latitude, longitude, individual_attack, individual_defense, individual_stamina FROM pokemon
				   WHERE pokemon_id IN (".implode(",", $mythic_pokemons).")
				   ORDER BY last_modified DESC LIMIT 0,12";
	} else {
		// get all pokemon
		$req		= "SELECT DISTINCT pokemon_id, encounter_id, disappear_time, last_modified, (CONVERT_TZ(disappear_time, '+00:00', '".$time_offset."')) as disappear_time_real, latitude, longitude, individual_attack, individual_defense, individual_stamina FROM pokemon
				   ORDER BY last_modified DESC LIMIT 0,12";
	}
	$result 	= $mysqli->query($req);
	$recents	= array();

	if ($result->num_rows > 0) {
		while ($data = $result->fetch_object()) {
			$recent = new stdClass();
			$recent->id = $data->pokemon_id;
			$recent->uid = $data->encounter_id;
			$recent->last_seen = strtotime($data->disappear_time_real);

			$recent->last_location = new stdClass();
			$recent->last_location->latitude = $data->latitude;
			$recent->last_location->longitude = $data->longitude;

			if ($config->system->recents_show_iv) {
				$recent->iv = new stdClass();
				$recent->iv->attack = $data->individual_attack;
				$recent->iv->defense = $data->individual_defense;
				$recent->iv->stamina = $data->individual_stamina;
				if (isset($recent->iv->attack) && isset($recent->iv->defense) && isset($recent->iv->stamina)) {
					$recent->iv->available = true;
				} else {
					$recent->iv->available = false;
				}
			}

			$recents[] = $recent;
		}
	}


	// Team battle
	// -----------

	$home->teams = new stdClass();

	// Team
	// 1 = bleu
	// 2 = rouge
	// 3 = jaune

	$req = "SELECT count( DISTINCT(gym_id) ) as total FROM gym WHERE team_id = '1'  ";
	$result = $mysqli->query($req);
	$data 	= $result->fetch_object();

	$home->teams->mystic 	= $data->total;


	$req = "SELECT count( DISTINCT(gym_id) ) as total FROM gym WHERE team_id = '2'  ";
	$result = $mysqli->query($req);
	$data 	= $result->fetch_object();

	$home->teams->valor 	= $data->total;


	$req = "SELECT count( DISTINCT(gym_id) ) as total FROM gym WHERE team_id = '3'  ";
	$result = $mysqli->query($req);
	$data 	= $result->fetch_object();

	$home->teams->instinct 	= $data->total;


	$req = "SELECT count( DISTINCT(gym_id) ) as total FROM gym WHERE team_id = '0'  ";
	$result = $mysqli->query($req);
	$data 	= $result->fetch_object();

	$home->teams->rocket 	= $data->total;
}
