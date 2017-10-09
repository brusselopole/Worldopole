<?php

// Include & load the variables
// ############################

$variables = SYS_PATH.'/core/json/variables.json';
$config = json_decode(file_get_contents($variables));

if (!defined('SYS_PATH')) {
	echo 'Error: config.php does not exist or failed to load.<br>';
	echo 'Check whether you renamed the config.example.php file!';
	exit();
} 
if (!isset($config->system)) {
	echo 'Error: Could not load core/json/variables.json.<br>';
	echo 'json_last_error(): '.json_last_error().'<br>';
	echo 'Check the file encoding as well. It have to be UTF-8 without BOM!';
	exit();
}


// Manage Time Interval
// #####################

include_once('timezone.loader.php');

// Load Quarrys
// ###################

include_once('quarrys.php');

// Debug mode
#############

if (SYS_DEVELOPMENT_MODE) {
	error_reporting(E_ALL);
}


// MySQL Connect
#################


$mysqli = new mysqli(SYS_DB_HOST, SYS_DB_USER, SYS_DB_PSWD, SYS_DB_NAME, SYS_DB_PORT);


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
		// everything seems to be fine let's run an initial cronjob
		include_once(SYS_PATH.'/core/cron/crontabs.include.php');
	}
}


// Load the locale elements
############################

include_once('locales.loader.php');



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

			$pokemon_id = mysqli_real_escape_string($mysqli, $_GET['id']);

			if (!is_object($pokemons->pokemon->$pokemon_id)) {
				header('Location:/404');
				exit();
			}


			$pokemon			= new stdClass();
			$pokemon			= $pokemons->pokemon->$pokemon_id;
			$pokemon->id = $pokemon_id;


			// Some math
			// ----------

			$pokemon->max_cp_percent 	= percent(3670, $pokemon->max_cp);
			$pokemon->max_hp_percent 	= percent(411, $pokemon->max_hp);


			// Get Dabase results
			//-------------------


			// Total gym protected

            $result = $mysqli->query(req_pokemon_total_gym_protected($pokemon_id));
			$data = $result->fetch_object();

			$pokemon->protected_gyms = $data->total;

			// Spawn rate

			if ($pokemon->spawn_count > 0 && $pokemon->per_day == 0) {
				$pokemon->spawns_per_day = "<1";
			} else {
				$pokemon->spawns_per_day = $pokemon->per_day;
			}

			// Last seen


            $result = $mysqli->query(req_pokemon_last_seen($pokemon_id));
			$data = $result->fetch_object();

			if (isset($data)) {
				$last_spawn = $data;

				$pokemon->last_seen = strtotime($data->disappear_time_real);
				$pokemon->last_position = new stdClass();
				$pokemon->last_position->latitude = $data->latitude;
				$pokemon->last_position->longitude = $data->longitude;
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
			
			// Top50 Pokemon List
			// Don't run the query for super common pokemon because it's too heavy
			if ($pokemon->spawn_rate < 0.20) {
				// Make it sortable; default sort: cp DESC
				$top_possible_sort = array('IV', 'cp', 'individual_attack', 'individual_defense', 'individual_stamina', 'move_1', 'move_2', 'disappear_time');
				$top_order = isset($_GET['order']) ? $_GET['order'] : '';
				$top_order_by = in_array($top_order, $top_possible_sort) ? $_GET['order'] : 'cp';
				$top_direction = isset($_GET['direction']) ? 'ASC' : 'DESC';
				$top_direction = !isset($_GET['order']) && !isset($_GET['direction']) ? 'DESC' : $top_direction;

                $result = $mysqli->query(req_pokemon_get_top_50($pokemon_id, $top_order_by, $top_direction));
				$top = array();
				while ($data = $result->fetch_object()) {
					$top[] = $data;
				}
			}
			
			// Trainer with highest Pokemon
			
			// Make it sortable but use different variable names this time; default sort: cp DESC
			$best_possible_sort = array('trainer_name', 'IV', 'cp', 'move_1', 'move_2', 'last_seen');
			$best_order = isset($_GET['order']) ? $_GET['order'] : '';
			$best_order_by = in_array($best_order, $best_possible_sort) ? $_GET['order'] : 'cp';
			$best_direction = isset($_GET['direction']) ? 'ASC' : 'DESC';
			$best_direction = !isset($_GET['order']) && !isset($_GET['direction']) ? 'DESC' : $best_direction;

            $result = $mysqli->query(req_pokemon_get_top_trainers($pokemon_id, $best_order_by, $best_direction));
			$toptrainer = array();
			while ($data = $result->fetch_object()) {
				$toptrainer[] = $data;
			}
			
			break;

		// Pokedex
		##########

		case 'pokedex':
			// Pokemon List from the JSON file
			// --------------------------------

			$max = $config->system->max_pokemon;
			$pokedex = new stdClass();

			for ($i = 1; $i <= $max; $i++) {
				$pokedex->$i = new stdClass();
				$pokedex->$i->id 			= $i;
				$pokedex->$i->permalink = 'pokemon/'.$i;
				$pokedex->$i->img			= 'core/pokemons/'.$i.$config->system->pokeimg_suffix;
				$pokedex->$i->name = $pokemons->pokemon->$i->name;
				$pokedex->$i->spawn = ($pokemons->pokemon->$i->spawn_count > 0) ? 1 : 0;
				$pokedex->$i->spawn_count = $pokemons->pokemon->$i->spawn_count;
			}


			break;


		// Pokestops
		############

		case 'pokestops':
			$pokestop = new stdClass();

            $result = $mysqli->query(req_pokestop_count());
			$data = $result->fetch_object();

			$pokestop->total = $data->total;

            $result = $mysqli->query(req_pokestop_lure_count());
			$data = $result->fetch_object();

			$pokestop->lured = $data->total;



			break;


		// Gyms
		########


		case 'gym':
			// 3 Teams (teamm rocket is neutral)
			// 1 Fight

			$teams = new stdClass();

			$teams->mystic = new stdClass();
			$teams->mystic->guardians = new stdClass();
			$teams->mystic->id = 1;

			$teams->valor = new stdClass();
			$teams->valor->guardians = new stdClass();
			$teams->valor->id = 2;

			$teams->instinct = new stdClass();
			$teams->instinct->guardians = new stdClass();
			$teams->instinct->id = 3;

			$teams->rocket = new stdClass();
			$teams->rocket->guardians = new stdClass();
			$teams->rocket->id = 0;



			foreach ($teams as $team_key => $team_values) {
				// Team Guardians

                $result = $mysqli->query(req_gym_guards_for_team($team_values->id));

				$i = 0;

				while ($data = $result->fetch_object()) {
					$teams->$team_key->guardians->$i = $data->guard_pokemon_id;

					$i++;
				}


				// Gym owned and average points

                $result = $mysqli->query(req_gym_count_cp_for_team($team_values->id));
				$data	= $result->fetch_object();

				$teams->$team_key->gym_owned = $data->total;
				$teams->$team_key->average = $data->average_points;
			}


			break;





		case 'dashboard':
			// This case is only used for test purpose.

			$stats_file = SYS_PATH.'/core/json/gym.stats.json';

			if (!is_file($stats_file)) {
				echo "Sorry, no Gym stats file was found. <br> Did you enable cron? ";
				exit();
			}


			$stats_file = SYS_PATH.'/core/json/pokemon.stats.json';

			if (!is_file($stats_file)) {
				echo "Sorry, no Pokémon stats file was found. <br> Did you enabled cron?";
				exit();
			}


			$stats_file = SYS_PATH.'/core/json/pokestop.stats.json';

			if (!is_file($stats_file)) {
				echo "Sorry, no Pokéstop stats file was found. <br> Did you enabled cron?";
				exit();
			}

			if ($config->system->captcha_support) {
				$stats_file = SYS_PATH.'/core/json/captcha.stats.json';

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

    $result = $mysqli->query(req_pokemon_count());
	$data = $result->fetch_object();


	$home->pokemon_now = $data->total;


	// Lured stops
	// -----------

    $result = $mysqli->query(req_pokestop_lure_count());
	$data = $result->fetch_object();

	$home->pokestop_lured = $data->total;


	// Gyms
	// ----

    $result = $mysqli->query(req_gym_count());
	$data = $result->fetch_object();

	$home->gyms = $data->total;


	// Recent spawns
	// ------------

	if ($config->system->recents_filter) {
		// get all mythic pokemon ids
		$mythic_pokemons = array();
		foreach ($pokemons->pokemon as $id => $pokemon) {
			if ($pokemon->spawn_rate < $config->system->recents_filter_rarity && $pokemon->rating >= $config->system->recents_filter_rating) {
				$mythic_pokemons[] = $id;
			}
		}
		// get all mythic pokemon
        $req_poke = req_mystic_pokemon($mythic_pokemons);
	} else {
		// get all pokemon
        $req_poke = req_all_pokemon();
    }
    $result = $mysqli->query($req_poke);
	$recents	= array();

	if ($result->num_rows > 0) {
		while ($data = $result->fetch_object()) {
			$recent = new stdClass();
			$recent->id = $data->pokemon_id;
			$recent->uid = $data->encounter_id;
			$recent->last_seen = strtotime($data->disappear_time_real);

			$location_link = isset($config->system->location_url) ? $config->system->location_url : 'https://maps.google.com/?q={latitude},{longitude}&ll={latitude},{longitude}&z=16';
			$location_link = str_replace('{latitude}', $data->latitude, $location_link);
			$location_link = str_replace('{longitude}', $data->longitude, $location_link);
			$recent->location_link = $location_link;

			if ($config->system->recents_encounter_details) {
				$recent->encdetails = new stdClass();
				$recent->encdetails->cp = $data->cp;
				$recent->encdetails->attack = $data->individual_attack;
				$recent->encdetails->defense = $data->individual_defense;
				$recent->encdetails->stamina = $data->individual_stamina;
				if (isset($recent->encdetails->cp) && isset($recent->encdetails->attack) && isset($recent->encdetails->defense) && isset($recent->encdetails->stamina)) {
					$recent->encdetails->available = true;
				} else {
					$recent->encdetails->available = false;
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

    $result = $mysqli->query(req_gym_count_for_team(1));
	$data = $result->fetch_object();

	$home->teams->mystic = $data->total;


    $result = $mysqli->query(req_gym_count_for_team(2));
	$data = $result->fetch_object();

	$home->teams->valor = $data->total;


    $result = $mysqli->query(req_gym_count_for_team(3));
	$data = $result->fetch_object();

	$home->teams->instinct = $data->total;


    $result = $mysqli->query(req_gym_count_for_team(0));
	$data = $result->fetch_object();

	$home->teams->rocket = $data->total;
}