<?php
# Well, this file can only be loaded by your own server
# as it contains json datas formatted
# and you don't want to have other website to get your datas ;)
# If you want to use this file as an "API" just remove the first condition.

$pos = !empty($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], getenv('HTTP_HOST'));

if ($pos === false) {
	http_response_code(401);
	die('Restricted access');
}


include_once('../../config.php');

// Include & load the variables
// ############################

$variables = SYS_PATH.'/core/json/variables.json';
$config = json_decode(file_get_contents($variables));

// Manage Time Interval
// #####################

include_once('timezone.loader.php');

// Load Quarrys
// ###################

include_once('quarrys.php');


// Load the locale elements
############################

include_once('locales.loader.php');


// Load functions
##################
include_once(SYS_PATH . '/functions.php');

# MySQL
$mysqli = new mysqli(SYS_DB_HOST, SYS_DB_USER, SYS_DB_PSWD, SYS_DB_NAME, SYS_DB_PORT);
if ($mysqli->connect_error != '') {
	exit('Error MySQL Connect');
}
$mysqli->set_charset('utf8');
$request = "";
if (isset($_GET['type'])) {
	$request = $_GET['type'];
}
$postRequest = "";
if (isset($_POST['type'])) {
	$postRequest = $_POST['type'];
	$request = "postRequest";
}
switch ($request) {
	############################
	//
	// Update datas on homepage
	//
	############################

	case 'home_update':
		// Right now
		// ---------

        $result = $mysqli->query(req_pokemon_count());
		$data = $result->fetch_object();

		$values[] = $data->total;


		// Lured stops
		// -----------

        $result = $mysqli->query(req_pokestop_lure_count());
		$data = $result->fetch_object();

		$values[] = $data->total;



		// Team battle
		// -----------

        $result = $mysqli->query(req_gym_count());
		$data = $result->fetch_object();

		$values[] = $data->total;

		// Team
		// 1 = bleu
		// 2 = rouge
		// 3 = jaune

        $result = $mysqli->query(req_gym_count_for_team(2));
		$data = $result->fetch_object();

		// Red
		$values[] = $data->total;


        $result = $mysqli->query(req_gym_count_for_team(1));
		$data = $result->fetch_object();

		// Blue
		$values[] = $data->total;


        $result = $mysqli->query(req_gym_count_for_team(3));
		$data = $result->fetch_object();

		// Yellow
		$values[] = $data->total;

        $result = $mysqli->query(req_gym_count_for_team(0));
		$data = $result->fetch_object();

		// Neutral
		$values[] = $data->total;

		header('Content-Type: application/json');
		echo json_encode($values);

		break;


	####################################
	//
	// Update latests spawn on homepage
	//
	####################################

	case 'spawnlist_update':
		// Recent spawn
		// ------------
		$total_spawns = array();
		$last_uid_param = "";
		if (isset($_GET['last_uid'])) {
			$last_uid_param = $_GET['last_uid'];
		}
		if ($config->system->recents_filter) {
			// get all mythic pokemon ids
			$mythic_pokemons = array();
			foreach ($pokemons->pokemon as $id => $pokemon) {
				if ($pokemon->spawn_rate < $config->system->recents_filter_rarity && $pokemon->rating >= $config->system->recents_filter_rating) {
					$mythic_pokemons[] = $id;
				}
			}

			// get last mythic pokemon
            $req_poke = req_mystic_pokemon($mythic_pokemons);
        } else {
			// get last pokemon
            $req_poke = req_all_pokemon();
        }
        $result = $mysqli->query($req_poke);
		while ($data = $result->fetch_object()) {
			$new_spawn = array();
			$pokeid = $data->pokemon_id;
			$pokeuid = $data->encounter_id;

			if ($last_uid_param != $pokeuid) {
				$last_seen = strtotime($data->disappear_time_real);

				$location_link = isset($config->system->location_url) ? $config->system->location_url : 'https://maps.google.com/?q={latitude},{longitude}&ll={latitude},{longitude}&z=16';
				$location_link = str_replace('{latitude}', $data->latitude, $location_link);
				$location_link = str_replace('{longitude}', $data->longitude, $location_link);

				if ($config->system->recents_encounter_details) {
					$encdetails = new stdClass();
					$encdetails->cp = $data->cp;
					$encdetails->attack = $data->individual_attack;
					$encdetails->defense = $data->individual_defense;
					$encdetails->stamina = $data->individual_stamina;
					if (isset($encdetails->cp) && isset($encdetails->attack) && isset($encdetails->defense) && isset($encdetails->stamina)) {
						$encdetails->available = true;
					} else {
						$encdetails->available = false;
					}
				}

				$html = '
			    <div class="col-md-1 col-xs-4 pokemon-single" data-pokeid="'.$pokeid.'" data-pokeuid="'.$pokeuid.'" style="display: none;">
				<a href="pokemon/'.$pokeid.'"><img src="core/pokemons/'.$pokeid.$config->system->pokeimg_suffix.'" alt="'.$pokemons->pokemon->$pokeid->name.'" class="img-responsive"></a>
				<a href="pokemon/'.$pokeid.'"><p class="pkmn-name">'.$pokemons->pokemon->$pokeid->name.'</p></a>
				<a href="'.$location_link.'" target="_blank">
					<small class="pokemon-timer">00:00:00</small>
				</a>';
				if ($config->system->recents_encounter_details) {
					if ($encdetails->available) {
						if ($config->system->iv_numbers) {
							$html .= '
							<div class="progress" style="height: 15px; margin-bottom: 0">
								<div title="'.$locales->IV_ATTACK.': '.$encdetails->attack.'" class="progress-bar progress-bar-danger" role="progressbar" aria-valuenow="'.$encdetails->attack.'" aria-valuemin="0" aria-valuemax="45" style="width: '.(100 / 3).'%; line-height: 16px">
									<span class="sr-only">'.$locales->IV_ATTACK.': '.$encdetails->attack.'</span>'.$encdetails->attack.'
								</div>
								<div title="'.$locales->IV_DEFENSE.': '.$encdetails->defense.'" class="progress-bar progress-bar-info" role="progressbar" aria-valuenow="'.$encdetails->defense.'" aria-valuemin="0" aria-valuemax="45" style="width: '.(100 / 3).'%; line-height: 16px">
									<span class="sr-only">'.$locales->IV_DEFENSE.': '.$encdetails->defense.'</span>'.$encdetails->defense.'
								</div>
								<div title="'.$locales->IV_STAMINA.': '.$encdetails->stamina.'" class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="'.$encdetails->stamina.'" aria-valuemin="0" aria-valuemax="45" style="width: '.(100 / 3).'%; line-height: 16px">
									<span class="sr-only">'.$locales->IV_STAMINA.': '.$encdetails->stamina.'</span>'.$encdetails->stamina.'
								</div>
							</div>';
						} else {
							$html .= '
							<div class="progress" style="height: 6px; width: 80%; margin: 5px auto 0 auto">
							<div title="'.$locales->IV_ATTACK.': '.$encdetails->attack.'" class="progress-bar progress-bar-danger" role="progressbar" aria-valuenow="'.$encdetails->attack.'" aria-valuemin="0" aria-valuemax="45" style="width: '.(((100 / 15) * $encdetails->attack) / 3).'%">
									<span class="sr-only">'.$locales->IV_ATTACK.': '.$encdetails->attack.'</span>
							</div>
							<div title="'.$locales->IV_DEFENSE.': '.$encdetails->defense.'" class="progress-bar progress-bar-info" role="progressbar" aria-valuenow="'.$encdetails->defense.'" aria-valuemin="0" aria-valuemax="45" style="width: '.(((100 / 15) * $encdetails->defense) / 3).'%">
									<span class="sr-only">'.$locales->IV_DEFENSE.': '.$encdetails->defense.'</span>
							</div>
							<div title="'.$locales->IV_STAMINA.': '.$encdetails->stamina.'" class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="'.$encdetails->stamina.'" aria-valuemin="0" aria-valuemax="45" style="width: '.(((100 / 15) * $encdetails->stamina) / 3).'%">
									<span class="sr-only">'.$locales->IV_STAMINA.': '.$encdetails->stamina.'</span>
							</div>
							</div>';
						}
						$html .= '<small>'.$encdetails->cp.'</small>';
					} else {
						if ($config->system->iv_numbers) {
							$html .= '
							<div class="progress" style="height: 15px; margin-bottom: 0">
								<div title="'.$locales->IV_ATTACK.': not available" class="progress-bar progress-bar-danger" role="progressbar" aria-valuenow="'.$encdetails->attack.'" aria-valuemin="0" aria-valuemax="45" style="width: '.(100 / 3).'%; line-height: 16px">
									<span class="sr-only">'.$locales->IV_ATTACK.': '.$locales->NOT_AVAILABLE.'</span>?
								</div>
								<div title="'.$locales->IV_DEFENSE.': not available" class="progress-bar progress-bar-info" role="progressbar" aria-valuenow="'.$encdetails->defense.'" aria-valuemin="0" aria-valuemax="45" style="width: '.(100 / 3).'%; line-height: 16px">
									<span class="sr-only">'.$locales->IV_DEFENSE.': '.$locales->NOT_AVAILABLE.'</span>?
								</div>
								<div title="'.$locales->IV_STAMINA.': not available" class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="'.$encdetails->stamina.'" aria-valuemin="0" aria-valuemax="45" style="width: '.(100 / 3).'%; line-height: 16px">
									<span class="sr-only">'.$locales->IV_STAMINA.': '.$locales->NOT_AVAILABLE.'</span>?
								</div>
							</div>';
						} else {
						$html .= '
					    <div class="progress" style="height: 6px; width: 80%; margin: 5px auto 0 auto">
						    <div title="IV not available" class="progress-bar" role="progressbar" style="width: 100%; background-color: rgb(210,210,210)" aria-valuenow="1" aria-valuemin="0" aria-valuemax="1">
							    <span class="sr-only">IV '.$locales->NOT_AVAILABLE.'</span>
						    </div>
					    </div>';
						}
						$html .= '<small>???</small>';
					}
				}
				$html .= '
			    </div>';
				$new_spawn['html'] = $html;
				$countdown = $last_seen - time();
				$new_spawn['countdown'] = $countdown;
				$new_spawn['pokemon_uid'] = $pokeuid;
				$total_spawns[] = $new_spawn;
			} else {
				break;
			}
		}

		header('Content-Type: application/json');
		echo json_encode($total_spawns);

		break;


	####################################
	//
	// List Pokestop
	//
	####################################

	case 'pokestop':
        $result = $mysqli->query(req_pokestop_data());

		$pokestops = [];

		while ($data = $result->fetch_object()) {
			if ($data->lure_expiration >= $data->now) {
				$icon = 'pokestap_lured.png';
				$text = sprintf($locales->POKESTOPS_MAP_LURED, date('H:i:s', strtotime($data->lure_expiration_real)));
				$lured = true;
			} else {
				$icon = 'pokestap.png';
				$text = $locales->POKESTOPS_MAP_REGULAR;
				$lured = false;
			}

			$pokestops[] = [
				$text,
				$icon,
				$data->latitude,
				$data->longitude,
				$lured
			];
		}

		header('Content-Type: application/json');
		echo json_encode($pokestops);

		break;


	####################################
	//
	// Update data for the gym battle
	//
	####################################

	case 'update_gym':
		$teams = new stdClass();
		$teams->mystic = 1;
		$teams->valor = 2;
		$teams->instinct = 3;


		foreach ($teams as $team_name => $team_id) {
            $result = $mysqli->query(req_gym_count_cp_for_team($team_id));
			$data = $result->fetch_object();

			$return[] = $data->total;
			$return[] = $data->average_points;
		}

		header('Content-Type: application/json');
		echo json_encode($return);

		break;


	####################################
	//
	// Get datas for the gym map
	//
	####################################


	case 'gym_map':
        $result = $mysqli->query(req_gym_data());

		$gyms = [];

		while ($data = $result->fetch_object()) {
			// Team
			// 1 = bleu
			// 2 = rouge
			// 3 = jaune

			switch ($data->team_id) {
				case 0:
					$icon	= 'map_white.png';
					$team	= 'No Team (yet)';
					$color = 'rgba(0, 0, 0, .6)';
					break;

				case 1:
					$icon	= 'map_blue_';
					$team	= 'Team Mystic';
					$color = 'rgba(74, 138, 202, .6)';
					break;

				case 2:
					$icon	= 'map_red_';
					$team	= 'Team Valor';
					$color = 'rgba(240, 68, 58, .6)';
					break;

				case 3:
					$icon	= 'map_yellow_';
					$team	= 'Team Instinct';
					$color = 'rgba(254, 217, 40, .6)';
					break;
			}

			if ($data->team_id != 0) {
				$icon .= $data->level.".png";
			}

			$gyms[] = [
				$icon,
				$data->latitude,
				$data->longitude,
				$data->gym_id,
			];
		}

		header('Content-Type: application/json');
		echo json_encode($gyms);

		break;


	####################################
	//
	// Get datas for gym defenders
	//
	####################################

	case 'gym_defenders':
		$gym_id = $mysqli->real_escape_string($_GET['gym_id']);
        $result = $mysqli->query(req_gym_defender_for($gym_id));

		$gymData['gymDetails']['gymInfos'] = false;

		while ($data = $result->fetch_object()) {
			$gymData['gymDetails']['gymInfos']['name'] = $data->name;
			$gymData['gymDetails']['gymInfos']['description'] = $data->description;
			if ($data->url == null) {
				$gymData['gymDetails']['gymInfos']['url'] = '';
			} else {
				$gymData['gymDetails']['gymInfos']['url'] = $data->url;
			}
			$gymData['gymDetails']['gymInfos']['points'] = $data->total_cp;
			$gymData['gymDetails']['gymInfos']['level'] = $data->level;
			$gymData['gymDetails']['gymInfos']['last_scanned'] = $data->last_scanned;
			$gymData['gymDetails']['gymInfos']['team'] = $data->team;
			$gymData['gymDetails']['gymInfos']['guardPokemonId'] = $data->guard_pokemon_id;
		}

        $result = $mysqli->query(req_gym_defender_stats_for($gym_id));

		$i = 0;

		$gymData['infoWindow'] = '
			<div class="gym_defenders">
			';
		while ($data = $result->fetch_object()) {
			$gymData['gymDetails']['pokemons'][] = $data;
			if ($data != false) {
				if ($config->system->iv_numbers) {
					$gymData['infoWindow'] .= '
					<div style="text-align: center; width: 50px; display: inline-block; margin-right: 3px">
						<a href="pokemon/'.$data->pokemon_id.'">
						<img src="core/pokemons/'.$data->pokemon_id.$config->system->pokeimg_suffix.'" height="50" style="display:inline-block" >
						</a>
						<p class="pkmn-name">'.$data->cp.'</p>
						<div class="progress" style="height: 12px; margin-bottom: 0">
							<div title="'.$locales->IV_ATTACK.': '.$data->iv_attack.'" class="progress-bar progress-bar-danger" role="progressbar" aria-valuenow="'.$data->iv_attack.'" aria-valuemin="0" aria-valuemax="45" style="width: '.(100 / 3).'%; line-height: 13px; font-size: 11px">
								<span class="sr-only">'.$locales->IV_ATTACK.' : '.$data->iv_attack.'</span>'.$data->iv_attack.'
								</div>
								<div title="'.$locales->IV_DEFENSE.': '.$data->iv_defense.'" class="progress-bar progress-bar-info" role="progressbar" aria-valuenow="'.$data->iv_defense.'" aria-valuemin="0" aria-valuemax="45" style="width: '.(100 / 3).'%; line-height: 13px; font-size: 11px">
									<span class="sr-only">'.$locales->IV_DEFENSE.' : '.$data->iv_defense.'</span>'.$data->iv_defense.'
								</div>
								<div title="'.$locales->IV_STAMINA.': '.$data->iv_stamina.'" class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="'.$data->iv_stamina.'" aria-valuemin="0" aria-valuemax="45" style="width: '.(100 / 3).'%; line-height: 13px; font-size: 11px">
									<span class="sr-only">'.$locales->IV_STAMINA.' : '.$data->iv_stamina.'</span>'.$data->iv_stamina.'
								</div>
							</div>
						</div>';
				} else {
					$gymData['infoWindow'] .= '
					<div style="text-align: center; width: 50px; display: inline-block; margin-right: 3px">
						<a href="pokemon/'.$data->pokemon_id.'">
						<img src="core/pokemons/'.$data->pokemon_id.$config->system->pokeimg_suffix.'" height="50" style="display:inline-block" >
						</a>
						<p class="pkmn-name">'.$data->cp.'</p>
						<div class="progress" style="height: 4px; width: 40px; margin-bottom: 10px; margin-top: 2px; margin-left: auto; margin-right: auto">
							<div title="'.$locales->IV_ATTACK.': '.$data->iv_attack.'" class="progress-bar progress-bar-danger" role="progressbar" aria-valuenow="'.$data->iv_attack.'" aria-valuemin="0" aria-valuemax="45" style="width: '.(((100 / 15) * $data->iv_attack) / 3).'%">
								<span class="sr-only">'.$locales->IV_ATTACK.': '.$data->iv_attack.'</span>
							</div>
							<div title="'.$locales->IV_DEFENSE.': '.$data->iv_defense.'" class="progress-bar progress-bar-info" role="progressbar" aria-valuenow="'.$data->iv_defense.'" aria-valuemin="0" aria-valuemax="45" style="width: '.(((100 / 15) * $data->iv_defense) / 3).'%">
								<span class="sr-only">'.$locales->IV_DEFENSE.': '.$data->iv_defense.'</span>
							</div>
							<div title="'.$locales->IV_STAMINA.': '.$data->iv_stamina.'" class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="'.$data->iv_stamina.'" aria-valuemin="0" aria-valuemax="45" style="width: '.(((100 / 15) * $data->iv_stamina) / 3).'%">
								<span class="sr-only">'.$locales->IV_STAMINA.': '.$data->iv_stamina.'</span>
							</div>
						</div>
					</div>'
						; }
			} else {
				$gymData['infoWindow'] .= '
				<div style="text-align: center; width: 50px; display: inline-block; margin-right: 3px">
					<a href="pokemon/'.$gymData['gymDetails']['gymInfos']['guardPokemonId'].'">
					<img src="core/pokemons/'.$gymData['gymDetails']['gymInfos']['guardPokemonId'].$config->system->pokeimg_suffix.'" height="50" style="display:inline-block" >
					</a>
					<p class="pkmn-name">???</p>
				</div>'
				;
			}
			$i++;
		}

		// check whether we could retrieve gym infos, otherwise use basic gym info
		if (!$gymData['gymDetails']['gymInfos']) {

            $result = $mysqli->query(req_gym_data_simple($gym_id));
			$data = $result->fetch_object();

			$gymData['gymDetails']['gymInfos']['name'] = $locales->NOT_AVAILABLE;
			$gymData['gymDetails']['gymInfos']['description'] = $locales->NOT_AVAILABLE;
			$gymData['gymDetails']['gymInfos']['url'] = 'https://upload.wikimedia.org/wikipedia/commons/thumb/8/86/Solid_grey.svg/200px-Solid_grey.svg.png';
			$gymData['gymDetails']['gymInfos']['points'] = $data->total_cp;
			$gymData['gymDetails']['gymInfos']['level'] = $data->level;
			$gymData['gymDetails']['gymInfos']['last_scanned'] = $data->last_scanned;
			$gymData['gymDetails']['gymInfos']['team'] = $data->team_id;
			$gymData['gymDetails']['gymInfos']['guardPokemonId'] = $data->guard_pokemon_id;

			$gymData['infoWindow'] .= '
				<div style="text-align: center; width: 50px; display: inline-block; margin-right: 3px">
					<a href="pokemon/'.$data->guard_pokemon_id.'">
					<img src="core/pokemons/'.$data->guard_pokemon_id.$config->system->pokeimg_suffix.'" height="50" style="display:inline-block" >
					</a>
					<p class="pkmn-name">???</p>
				</div>';
		}
		$gymData['infoWindow'] = $gymData['infoWindow'].'</div>';

		header('Content-Type: application/json');
		echo json_encode($gymData);

		break;


	case 'trainer':
        $result = $mysqli->query(req_trainers($_GET));
		$trainers = array();
		while ($data = $result->fetch_object()) {
			$data->last_seen = date("Y-m-d", strtotime($data->last_seen));
			$trainers[$data->name] = $data;
		}
		foreach ($trainers as $trainer) {
            $resultRanking = $mysqli->query(req_trainer_ranking($trainer));
			while ($data = $resultRanking->fetch_object()) {
				$trainer->rank = $data->rank;
			}

            $resultPkms = $mysqli->query(req_trainer_active_pokemon($trainer->name));
			$trainer->pokemons = array();
			$active_gyms = 0;
			$pkmCount = 0;
			while ($resultPkms && $dataPkm = $resultPkms->fetch_object()) {
				$active_gyms++;
				$trainer->pokemons[$pkmCount++] = $dataPkm;
			}
			$trainer->gyms = $active_gyms;

            $resultPkms = $mysqli->query(req_trainer_inactive_pokemon($trainer->name));
			while ($resultPkms && $dataPkm = $resultPkms->fetch_object()) {
				$trainer->pokemons[$pkmCount++] = $dataPkm;
			}
		}
		$json = array();
		$json['trainers'] = $trainers;
		$locale = array();
		$locale["today"] = $locales->TODAY;
		$locale["day"] = $locales->DAY;
		$locale["days"] = $locales->DAYS;
		$locale["ivAttack"] = $locales->IV_ATTACK;
		$locale["ivDefense"] = $locales->IV_DEFENSE;
		$locale["ivStamina"] = $locales->IV_STAMINA;
		$json['locale'] = $locale;

		header('Content-Type: application/json');
		echo json_encode($json);

		break;


	case 'raids':
		$page = "0";
		if (isset($_GET['page'])) {
			$page = mysqli_real_escape_string($mysqli, $_GET['page']);
		}

        $result = $mysqli->query(req_raids_data($page));
		$raids = array();
		while ($data = $result->fetch_object()) {
			$data->starttime = date("H:i", strtotime($data->start));
			$data->endtime = date("H:i", strtotime($data->end));
			$data->gym_id = str_replace('.', '_', $data->gym_id);
			if (isset($data->move_1)) {
				$move1 = $data->move_1;
				$data->quick_move = $move->$move1->name;
			} else {
				$data->quick_move = "?";
			}
			if (isset($data->move_2)) {
				$move2 = $data->move_2;
				$data->charge_move = $move->$move2->name;
			} else {
				$data->charge_move = "?";
			}
			$raids[$data->gym_id] = $data;
		}
		$json = array();
		$json['raids'] = $raids;
		$locale = array();
		$locale['noraids'] = $locales->RAIDS_NONE;
		$json['locale'] = $locale;

		header('Content-Type: application/json');
		echo json_encode($json);

		break;

	case 'pokemon_slider_init':
        $result = $mysqli->query(req_pokemon_slider_init());
		$bounds		= $result->fetch_object();

		header('Content-Type: application/json');
		echo json_encode($bounds);

		break;


	case 'pokemon_heatmap_points':
		$json = "";
		if (isset($_GET['start']) && isset($_GET['end']) && isset($_GET['pokemon_id'])) {
			$start = date("Y-m-d H:i", (int) $_GET['start']);
			$end = date("Y-m-d H:i", (int) $_GET['end']);
			$pokemon_id = mysqli_real_escape_string($mysqli, $_GET['pokemon_id']);
            $result = $mysqli->query(req_pokemon_headmap_points($pokemon_id, $start, $end));
			$points = array();
			while ($result && $data = $result->fetch_object()) {
				$points[] = $data;
			}

			$json = json_encode($points);
		}

		header('Content-Type: application/json');
		echo $json;
		break;


	case 'maps_localization_coordinates':
		$json = "";
        $result = $mysqli->query(req_maps_localization_coordinates());
		$coordinates = $result->fetch_object();

		header('Content-Type: application/json');
		echo json_encode($coordinates);

		break;


	case 'pokemon_graph_data':
		$json = "";
		if (isset($_GET['pokemon_id'])) {
			$pokemon_id = mysqli_real_escape_string($mysqli, $_GET['pokemon_id']);
            $result = $mysqli->query(req_pokemon_graph_data($pokemon_id));
			$array = array_fill(0, 24, 0);
			while ($result && $data = $result->fetch_object()) {
				$array[$data->disappear_hour] = $data->total;
			}
			// shift array because AM/PM starts at 1AM not 0:00
			$array[] = $array[0];
			array_shift($array);

			$json = json_encode($array);
		}

		header('Content-Type: application/json');
		echo $json;
		break;


	case 'postRequest':
		break;

	default:
		echo "What do you mean?";
		exit();
	break;
}

if ($postRequest != "") {
	switch ($postRequest) {
		case 'pokemon_live':
			$json = "";
			if (isset($_POST['pokemon_id'])) {
                $resultTestIv = $mysqli->query(req_pokemon_liva_data_test($pokemon_id));
				$testIv = $resultTestIv->fetch_object();
                $result = $mysqli->query(req_pokemon_live_data($pokemon_id, $testIv, $_POST));
				$json = array();
				$json['points'] = array();
				$locale = array();
				$locale["ivAttack"] = $locales->IV_ATTACK;
				$locale["ivDefense"] = $locales->IV_DEFENSE;
				$locale["ivStamina"] = $locales->IV_STAMINA;
				$json['locale'] = $locale;
				while ($result && $data = $result->fetch_object()) {
					$pokeid = $data->pokemon_id;
					$data->name = $pokemons->pokemon->$pokeid->name;
					if (isset($data->move_1)) {
						$move1 = $data->move_1;
						$data->quick_move = $move->$move1->name;
					} else {
						$data->quick_move = "?";
					}
					if (isset($data->move_2)) {
						$move2 = $data->move_2;
						$data->charge_move = $move->$move2->name;
					} else {
						$data->charge_move = "?";
					}
					$json['points'][] = $data;
				}

				$json = json_encode($json);
			}

			header('Content-Type: application/json');

			echo $json;

		break;



		default:
			echo "What do you mean?";
			exit();
		break;
	}
}
