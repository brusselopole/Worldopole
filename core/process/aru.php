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


// Load the locale elements
############################

include_once('locales.loader.php');


// Load functions
##################
include_once(SYS_PATH.'/functions.php');

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

		$req = "SELECT COUNT(*) AS total FROM pokemon WHERE disappear_time >= UTC_TIMESTAMP()";
		$result = $mysqli->query($req);
		$data = $result->fetch_object();

		$values[] = $data->total;


		// Lured stops
		// -----------

		$req = "SELECT COUNT(*) AS total FROM pokestop WHERE lure_expiration >= UTC_TIMESTAMP()";
		$result = $mysqli->query($req);
		$data = $result->fetch_object();

		$values[] = $data->total;



		// Team battle
		// -----------

		$req = "SELECT COUNT(DISTINCT(gym_id)) AS total FROM gym";
		$result = $mysqli->query($req);
		$data = $result->fetch_object();

		$values[] = $data->total;

		// Team
		// 1 = bleu
		// 2 = rouge
		// 3 = jaune

		$req = "SELECT COUNT(DISTINCT(gym_id)) AS total FROM gym WHERE team_id = '2'";
		$result = $mysqli->query($req);
		$data = $result->fetch_object();

		// Red
		$values[] = $data->total;


		$req = "SELECT COUNT(DISTINCT(gym_id)) AS total FROM gym WHERE team_id = '1'";
		$result = $mysqli->query($req);
		$data = $result->fetch_object();

		// Blue
		$values[] = $data->total;


		$req = "SELECT COUNT(DISTINCT(gym_id)) AS total FROM gym WHERE team_id = '3'";
		$result = $mysqli->query($req);
		$data = $result->fetch_object();

		// Yellow
		$values[] = $data->total;

		$req = "SELECT COUNT(DISTINCT(gym_id)) AS total FROM gym WHERE team_id = '0'";
		$result = $mysqli->query($req);
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
			$req = "SELECT pokemon_id, encounter_id, disappear_time, last_modified, (CONVERT_TZ(disappear_time, '+00:00', '".$time_offset."')) AS disappear_time_real,
					latitude, longitude, cp, individual_attack, individual_defense, individual_stamina
					FROM pokemon
					WHERE pokemon_id IN (".implode(",", $mythic_pokemons).")
					ORDER BY last_modified DESC
					LIMIT 0,12";
		} else {
			// get last pokemon
			$req = "SELECT pokemon_id, encounter_id, disappear_time, last_modified, (CONVERT_TZ(disappear_time, '+00:00', '".$time_offset."')) AS disappear_time_real,
					latitude, longitude, cp, individual_attack, individual_defense, individual_stamina
					FROM pokemon
					ORDER BY last_modified DESC
					LIMIT 0,12";
		}
		$result = $mysqli->query($req);
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
				<a href="pokemon/'.$pokeid.'"><img src="'.$pokemons->pokemon->$pokeid->img.'" alt="'.$pokemons->pokemon->$pokeid->name.'" class="img-responsive"></a>
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
		$where = "";
		$req = "SELECT latitude, longitude, lure_expiration, UTC_TIMESTAMP() AS now, (CONVERT_TZ(lure_expiration, '+00:00', '".$time_offset."')) AS lure_expiration_real FROM pokestop ";

		$result = $mysqli->query($req);

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
			$req = "SELECT COUNT(DISTINCT(gym_id)) AS total, ROUND(AVG(total_cp),0) AS average_points FROM gym WHERE team_id = '".$team_id."'";
			$result = $mysqli->query($req);
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
		$req = "SELECT gym_id, team_id, latitude, longitude, (CONVERT_TZ(last_scanned, '+00:00', '".$time_offset."')) AS last_scanned, (6 - slots_available) AS level FROM gym";
		$result = $mysqli->query($req);

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
		$req = "SELECT gymdetails.name AS name, gymdetails.description AS description, gymdetails.url AS url, gym.team_id AS team,
					(CONVERT_TZ(gym.last_scanned, '+00:00', '".$time_offset."')) AS last_scanned, gym.guard_pokemon_id AS guard_pokemon_id, gym.total_cp AS total_cp, (6 - gym.slots_available) AS level
					FROM gymdetails
					LEFT JOIN gym ON gym.gym_id = gymdetails.gym_id
					WHERE gym.gym_id='".$gym_id."'";
		$result = $mysqli->query($req);

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

		$req = "SELECT DISTINCT gympokemon.pokemon_uid, pokemon_id, iv_attack, iv_defense, iv_stamina, MAX(cp) AS cp, gymmember.gym_id
					FROM gympokemon INNER JOIN gymmember ON gympokemon.pokemon_uid=gymmember.pokemon_uid
					GROUP BY gympokemon.pokemon_uid, pokemon_id, iv_attack, iv_defense, iv_stamina, gym_id
					HAVING gymmember.gym_id='".$gym_id."'
					ORDER BY cp DESC";
		$result = $mysqli->query($req);

		$i = 0;

		$gymData['infoWindow'] = '
			<div class="gym_defenders">
			';
		while ($data = $result->fetch_object()) {
			$gymData['gymDetails']['pokemons'][] = $data;
			if ($data != false) {
				$pokemon_id = $data->pokemon_id;
				if ($config->system->iv_numbers) {
					$gymData['infoWindow'] .= '
					<div style="text-align: center; width: 50px; display: inline-block; margin-right: 3px">
						<a href="pokemon/'.$data->pokemon_id.'">
						<img src="'.$pokemons->pokemon->$pokemon_id->img.'" height="50" style="display:inline-block" >
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
						<img src="'.$pokemons->pokemon->$pokemon_id->img.'" height="50" style="display:inline-block" >
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
				$pokemon_id = $gymData['gymDetails']['gymInfos']['guardPokemonId'];
				$gymData['infoWindow'] .= '
				<div style="text-align: center; width: 50px; display: inline-block; margin-right: 3px">
					<a href="pokemon/'.$gymData['gymDetails']['gymInfos']['guardPokemonId'].'">
					<img src="'.$pokemons->pokemon->$pokemon_id->img.'" height="50" style="display:inline-block" >
					</a>
					<p class="pkmn-name">???</p>
				</div>'
				;
			}
			$i++;
		}

		// check whether we could retrieve gym infos, otherwise use basic gym info
		if (!$gymData['gymDetails']['gymInfos']) {
			$req = "SELECT gym_id, team_id, guard_pokemon_id, latitude, longitude, (CONVERT_TZ(last_scanned, '+00:00', '".$time_offset."')) AS last_scanned, total_cp, (6 - slots_available) AS level
				FROM gym WHERE gym_id='".$gym_id."'";
			$result = $mysqli->query($req);
			$data = $result->fetch_object();

			$gymData['gymDetails']['gymInfos']['name'] = $locales->NOT_AVAILABLE;
			$gymData['gymDetails']['gymInfos']['description'] = $locales->NOT_AVAILABLE;
			$gymData['gymDetails']['gymInfos']['url'] = 'https://upload.wikimedia.org/wikipedia/commons/thumb/8/86/Solid_grey.svg/200px-Solid_grey.svg.png';
			$gymData['gymDetails']['gymInfos']['points'] = $data->total_cp;
			$gymData['gymDetails']['gymInfos']['level'] = $data->level;
			$gymData['gymDetails']['gymInfos']['last_scanned'] = $data->last_scanned;
			$gymData['gymDetails']['gymInfos']['team'] = $data->team_id;
			$gymData['gymDetails']['gymInfos']['guardPokemonId'] = $data->guard_pokemon_id;

			$pokemon_id = $data->guard_pokemon_id;
			$gymData['infoWindow'] .= '
				<div style="text-align: center; width: 50px; display: inline-block; margin-right: 3px">
					<a href="pokemon/'.$data->guard_pokemon_id.'">
					<img src="'.$pokemons->pokemon->$pokemon_id->img.'" height="50" style="display:inline-block" >
					</a>
					<p class="pkmn-name">???</p>
				</div>';
		}
		$gymData['infoWindow'] = $gymData['infoWindow'].'</div>';

		header('Content-Type: application/json');
		echo json_encode($gymData);

		break;


	case 'trainer':
		$name = "";
		$page = "0";
		$where = "";
		$order = "";
		$team = 0;
		$ranking = 0;
		if (isset($_GET['name'])) {
			$trainer_name = mysqli_real_escape_string($mysqli, $_GET['name']);
			$where = " HAVING name LIKE '%".$trainer_name."%'";
		}
		if (isset($_GET['team']) && $_GET['team'] != 0) {
			$team = mysqli_real_escape_string($mysqli, $_GET['team']);
			$where .= ($where == "" ? " HAVING" : " AND")." team = ".$team;
		}
		if (!empty($config->system->trainer_blacklist)) {
			$where .= ($where == "" ? " HAVING" : " AND")." name NOT IN ('".implode("','", $config->system->trainer_blacklist)."')";
		}
		if (isset($_GET['page'])) {
			$page = mysqli_real_escape_string($mysqli, $_GET['page']);
		}
		if (isset($_GET['ranking'])) {
			$ranking = mysqli_real_escape_string($mysqli, $_GET['ranking']);
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
				GROUP BY trainer.name ".$where.$order.$limit;

		$result = $mysqli->query($req);
		$trainers = array();
		while ($data = $result->fetch_object()) {
			$data->last_seen = date("Y-m-d", strtotime($data->last_seen));
			$trainers[$data->name] = $data;
		}
		foreach ($trainers as $trainer) {
			$reqRanking = "SELECT COUNT(1) AS rank FROM trainer WHERE level = ".$trainer->level;
			if (!empty($config->system->trainer_blacklist)) {
				$reqRanking .= " AND name NOT IN ('".implode("','", $config->system->trainer_blacklist)."')";
			}
			$resultRanking = $mysqli->query($reqRanking);
			while ($data = $resultRanking->fetch_object()) {
				$trainer->rank = $data->rank;
			}
			$req = "(SELECT DISTINCT gympokemon.pokemon_id, gympokemon.pokemon_uid, gympokemon.cp, DATEDIFF(UTC_TIMESTAMP(), gympokemon.last_seen) AS last_scanned, gympokemon.trainer_name, gympokemon.iv_defense, gympokemon.iv_stamina, gympokemon.iv_attack, filtered_gymmember.gym_id, CONVERT_TZ(filtered_gymmember.deployment_time, '+00:00', '".$time_offset."') as deployment_time, '1' AS active
					FROM gympokemon INNER JOIN
					(SELECT gymmember.pokemon_uid, gymmember.gym_id, gymmember.deployment_time FROM gymmember GROUP BY gymmember.pokemon_uid, gymmember.deployment_time, gymmember.gym_id HAVING gymmember.gym_id <> '') AS filtered_gymmember
					ON gympokemon.pokemon_uid = filtered_gymmember.pokemon_uid
					WHERE gympokemon.trainer_name='".$trainer->name."'
					ORDER BY gympokemon.cp DESC)";

			$resultPkms = $mysqli->query($req);
			$trainer->pokemons = array();
			$active_gyms = 0;
			$pkmCount = 0;
			while ($resultPkms && $dataPkm = $resultPkms->fetch_object()) {
				$active_gyms++;
				$trainer->pokemons[$pkmCount++] = $dataPkm;
			}
			$trainer->gyms = $active_gyms;

			$req = "(SELECT DISTINCT gympokemon.pokemon_id, gympokemon.pokemon_uid, gympokemon.cp, DATEDIFF(UTC_TIMESTAMP(), gympokemon.last_seen) AS last_scanned, gympokemon.trainer_name, gympokemon.iv_defense, gympokemon.iv_stamina, gympokemon.iv_attack, null AS gym_id, CONVERT_TZ(filtered_gymmember.deployment_time, '+00:00', '".$time_offset."') as deployment_time, '0' AS active
					FROM gympokemon LEFT JOIN
					(SELECT * FROM gymmember HAVING gymmember.gym_id <> '') AS filtered_gymmember
					ON gympokemon.pokemon_uid = filtered_gymmember.pokemon_uid
					WHERE filtered_gymmember.pokemon_uid IS NULL AND gympokemon.trainer_name='".$trainer->name."'
					ORDER BY gympokemon.cp DESC)";

			$resultPkms = $mysqli->query($req);
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

		$limit = " LIMIT ".($page * 10).",10";

		$req = "SELECT raid.gym_id, raid.level, raid.pokemon_id, raid.cp, raid.move_1, raid.move_2, CONVERT_TZ(raid.spawn, '+00:00', '".$time_offset."') AS spawn, CONVERT_TZ(raid.start, '+00:00', '".$time_offset."') AS start, CONVERT_TZ(raid.end, '+00:00', '".$time_offset."') AS end, CONVERT_TZ(raid.last_scanned, '+00:00', '".$time_offset."') AS last_scanned, gymdetails.name, gym.latitude, gym.longitude FROM raid
				JOIN gymdetails ON gymdetails.gym_id = raid.gym_id
				JOIN gym ON gym.gym_id = raid.gym_id
				WHERE raid.end > UTC_TIMESTAMP()
				ORDER BY raid.level DESC, raid.start".$limit;

		$result = $mysqli->query($req);
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
		$req = "SELECT MIN(disappear_time) AS min, MAX(disappear_time) AS max FROM pokemon";
		$result 	= $mysqli->query($req);
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
			$where = " WHERE pokemon_id = ".$pokemon_id." "
					. "AND disappear_time BETWEEN '".$start."' AND '".$end."'";
			$req 		= "SELECT latitude, longitude FROM pokemon".$where." ORDER BY disappear_time DESC LIMIT 10000";
			$result = $mysqli->query($req);
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
		$req = "SELECT MAX(latitude) AS max_latitude, MIN(latitude) AS min_latitude, MAX(longitude) AS max_longitude, MIN(longitude) as min_longitude FROM spawnpoint";
		$result = $mysqli->query($req);
		$coordinates = $result->fetch_object();

		header('Content-Type: application/json');
		echo json_encode($coordinates);

		break;


	case 'pokemon_graph_data':
		$json = "";
		if (isset($_GET['pokemon_id'])) {
			$pokemon_id = mysqli_real_escape_string($mysqli, $_GET['pokemon_id']);
			$req = "SELECT COUNT(*) AS total,
					HOUR(CONVERT_TZ(disappear_time, '+00:00', '".$time_offset."')) AS disappear_hour
					FROM (SELECT disappear_time FROM pokemon WHERE pokemon_id = '".$pokemon_id."' ORDER BY disappear_time LIMIT 10000) AS pokemonFiltered
					GROUP BY disappear_hour
					ORDER BY disappear_hour";
			$result = $mysqli->query($req);
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
				$pokemon_id = mysqli_real_escape_string($mysqli, $_POST['pokemon_id']);
				$inmap_pkms_filter = "";
				$where = " WHERE disappear_time >= UTC_TIMESTAMP() AND pokemon_id = ".$pokemon_id;

				$reqTestIv = "SELECT MAX(individual_attack) AS iv FROM pokemon ".$where;
				$resultTestIv = $mysqli->query($reqTestIv);
				$testIv = $resultTestIv->fetch_object();
				if (isset($_POST['inmap_pokemons']) && ($_POST['inmap_pokemons'] != "")) {
					foreach ($_POST['inmap_pokemons'] as $inmap) {
						$inmap_pkms_filter .= "'".$inmap."',";
					}
					$inmap_pkms_filter = rtrim($inmap_pkms_filter, ",");
					$where .= " AND encounter_id NOT IN (".$inmap_pkms_filter.") ";
				}
				if ($testIv->iv != null && isset($_POST['ivMin']) && ($_POST['ivMin'] != "")) {
					$ivMin = mysqli_real_escape_string($mysqli, $_POST['ivMin']);
					$where .= " AND ((100/45)*(individual_attack+individual_defense+individual_stamina)) >= (".$ivMin.") ";
				}
				if ($testIv->iv != null && isset($_POST['ivMax']) && ($_POST['ivMax'] != "")) {
					$ivMax = mysqli_real_escape_string($mysqli, $_POST['ivMax']);
					$where .= " AND ((100/45)*(individual_attack+individual_defense+individual_stamina)) <=(".$ivMax.") ";
				}
				$req = "SELECT pokemon_id, encounter_id, latitude, longitude, disappear_time,
						(CONVERT_TZ(disappear_time, '+00:00', '".$time_offset."')) AS disappear_time_real,
						individual_attack, individual_defense, individual_stamina, move_1, move_2
						FROM pokemon ".$where."
						ORDER BY disappear_time DESC
						LIMIT 5000";
				$result = $mysqli->query($req);
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
