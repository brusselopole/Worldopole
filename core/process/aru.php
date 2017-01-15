<?php
# Well, this file can only be loaded by your own server
# as it contains json datas formatted
# and you don't want to have other website to get your datas ;)
# If you want to use this file as an "API" just remove the first condition.

$pos = !empty($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], getenv('HTTP_HOST'));

if ($pos===false) {
	http_response_code(401);
	die('Restricted access');
}


include_once('../../config.php');



// Include & load the variables
// ############################

$variables 	= SYS_PATH.'/core/json/variables.json';
$config 	= json_decode(file_get_contents($variables));



// Manage Time Interval
// #####################

include_once('timezone.loader.php');


// Load the locale elements
############################

include_once('locales.loader.php');


# MySQL
$mysqli 	= new mysqli(SYS_DB_HOST, SYS_DB_USER, SYS_DB_PSWD, SYS_DB_NAME, SYS_DB_PORT);
if ($mysqli->connect_error != '') {
	exit('Error MySQL Connect');
}
$mysqli->set_charset('utf8');
$request 	= $_GET['type'];

switch ($request) {
	############################
	//
	// Update datas on homepage
	//
	############################

	case 'home_update':
		// Right now
		// ---------

		$req 		= "SELECT COUNT(*) as total FROM pokemon WHERE disappear_time >= UTC_TIMESTAMP()";
		$result 	= $mysqli->query($req);
		$data 		= $result->fetch_object();

		$values[] 	= $data->total;


		// Lured stops
		// -----------

		$req 		= "SELECT COUNT(*) as total FROM pokestop WHERE lure_expiration >= UTC_TIMESTAMP()";
		$result 	= $mysqli->query($req);
		$data 		= $result->fetch_object();

		$values[] 	= $data->total;



		// Team battle
		// -----------

		$req 		= "SELECT count( DISTINCT(gym_id) ) as total FROM gym";
		$result 	= $mysqli->query($req);
		$data 		= $result->fetch_object();

		$values[] 	= $data->total;

		// Team
		// 1 = bleu
		// 2 = rouge
		// 3 = jaune

		$req	= "SELECT count( DISTINCT(gym_id) ) as total FROM gym WHERE team_id = '2'  ";
		$result	= $mysqli->query($req);
		$data	= $result->fetch_object();

		// Red
		$values[] = $data->total;


		$req	= "SELECT count( DISTINCT(gym_id) ) as total FROM gym WHERE team_id = '1'  ";
		$result	= $mysqli->query($req);
		$data	= $result->fetch_object();

		// Blue
		$values[] = $data->total;


		$req	= "SELECT count( DISTINCT(gym_id) ) as total FROM gym WHERE team_id = '3'  ";
		$result	= $mysqli->query($req);
		$data	= $result->fetch_object();

		// Yellow
		$values[] = $data->total;

		$req	= "SELECT count( DISTINCT(gym_id) ) as total FROM gym WHERE team_id = '0'  ";
		$result	= $mysqli->query($req);
		$data	= $result->fetch_object();

		// Neutral
		$values[] = $data->total;


		header('Content-Type: application/json');
		$json = json_encode($values);

		echo $json;


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
		if ($config->system->mythic_recents) {
			// get all mythic pokemon ids
			$mythic_pokemons = array();
			foreach ($pokemons->pokemon as $id => $pokemon) {
				if ($pokemon->spawn_rate < 0.01) {
					$mythic_pokemons[] = $id;
				}
			}

			// get last mythic pokemon
			$req		= "SELECT pokemon_id, encounter_id, disappear_time, last_modified, (CONVERT_TZ(disappear_time, '+00:00', '".$time_offset."')) as disappear_time_real, latitude, longitude, individual_attack, individual_defense, individual_stamina FROM pokemon
                        WHERE pokemon_id IN (".implode(",", $mythic_pokemons).")
                        ORDER BY last_modified DESC LIMIT 0,12";
		} else {
			// get last pokemon
			$req		= "SELECT pokemon_id, encounter_id, disappear_time, last_modified, (CONVERT_TZ(disappear_time, '+00:00', '".$time_offset."')) as disappear_time_real, latitude, longitude, individual_attack, individual_defense, individual_stamina FROM pokemon ORDER BY last_modified DESC LIMIT 0,12";
		}
		$result = $mysqli->query($req);
		while ($data = $result->fetch_object()) {
			$new_spawn = array();
			$pokeid = $data->pokemon_id;
			$pokeuid = $data->encounter_id;

			if ($last_uid_param != $pokeuid) {
				$last_seen = strtotime($data->disappear_time_real);

				$last_location = new stdClass();
				$last_location->latitude = $data->latitude;
				$last_location->longitude = $data->longitude;

				if ($config->system->recents_show_iv) {
					$iv = new stdClass();
					$iv->attack = $data->individual_attack;
					$iv->defense = $data->individual_defense;
					$iv->stamina = $data->individual_stamina;
					if (isset($iv->attack) && isset($iv->defense) && isset($iv->stamina)) {
						$iv->available = true;
					} else {
						$iv->available = false;
					}
				}

				$html = '
			    <div class="col-md-1 col-xs-4 pokemon-single" data-pokeid="'.$pokeid.'" data-pokeuid="'.$pokeuid.'" style="display: none;">
				<a href="pokemon/'.$pokeid.'"><img src="core/pokemons/'.$pokeid.'.png" alt="'.$pokemons->pokemon->$pokeid->name.'" class="img-responsive"></a>
				<a href="pokemon/'.$pokeid.'"><p class="pkmn-name">'.$pokemons->pokemon->$pokeid->name.'</p></a>
				<a href="https://maps.google.com/?q='.$last_location->latitude.','.$last_location->longitude.'&ll='.$last_location->latitude.','.$last_location->longitude.'&z=16" target="_blank">
				    <small class="pokemon-timer">00:00:00</small>
				</a>';
				if ($config->system->recents_show_iv) {
					if ($iv->available) {
						$html .= '
					<div class="progress" style="height: 6px; width: 80%; margin: 5px auto 0 auto;">
					    <div title="Stamina IV: '. $iv->stamina .'" class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="'. $iv->stamina .'" aria-valuemin="0" aria-valuemax="45" style="width: '. ((100/15)*$iv->stamina)/3 .'%">
						<span class="sr-only">Stamina IV: '. $iv->stamina .'</span>
					    </div>
					    <div title="Attack IV: '. $iv->attack .'" class="progress-bar progress-bar-danger" role="progressbar" aria-valuenow="'. $iv->attack .'" aria-valuemin="0" aria-valuemax="45" style="width: '. ((100/15)*$iv->attack)/3 .'%">
						<span class="sr-only">Attack IV: '. $iv->attack .'</span>
					    </div>
					    <div title="Defense IV: '. $iv->defense .'" class="progress-bar progress-bar-info" role="progressbar" aria-valuenow="'. $iv->defense .'" aria-valuemin="0" aria-valuemax="45" style="width: '. ((100/15)*$iv->defense)/3 .'%">
						<span class="sr-only">Defense IV: '. $iv->defense .'</span>
					    </div>
					</div>';

					} else {
						$html .= '
					    <div class="progress" style="height: 6px; width: 80%; margin: 5px auto 15px auto;">
						    <div title="IV not available" class="progress-bar" role="progressbar" style="width: 100%; background-color: rgba(240,240,240,1);" aria-valuenow="1" aria-valuemin="0" aria-valuemax="1">
							    <span class="sr-only">IV not available</span>
						    </div>
                        <small class="pokemon-timer">00:00:00</small>
					    </div>';
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
		$req 		= "SELECT latitude, longitude, lure_expiration FROM pokestop";
		$result 	= $mysqli->query($req);

		$i=0;

		while ($data = $result->fetch_object()) {
			if ($data->lure_expiration != '') {
				$icon = 'pokestap_lured.png';
				$text = 'Lured expire @ '.date('h:i:s', strtotime($data->lure_expiration)+(3600*2)) ;
			} else {
				$icon = 'pokestap.png';
				$text = 'Normal stop';
			}

			$temp[$i][] = $text;
			$temp[$i][] = $icon;
			$temp[$i][] = $data->latitude;
			$temp[$i][] = $data->longitude;
			$temp[$i][] = $i;

			$temp_json[] = json_encode($temp[$i]);


			$i++;
		}

		$return = json_encode($temp_json);

		echo $return;

		break;



	####################################
	//
	// Update data for the gym battle
	//
	####################################

	case 'update_gym':
		$teams			= new stdClass();
		$teams->mystic 		= 1;
		$teams->valor 		= 2;
		$teams->instinct 	= 3;


		foreach ($teams as $team_name => $team_id) {
			$req	= "SELECT COUNT(DISTINCT(gym_id)) as total, ROUND(AVG(gym_points),0) as average_points FROM gym WHERE team_id = '".$team_id."'  ";
			$result	= $mysqli->query($req);
			$data	= $result->fetch_object();

			$return[] 	= $data->total;
			$return[]	= $data->average_points;
		}

		$json = json_encode($return);

		header('Content-Type: application/json');
		echo $json;


		break;

	####################################
	//
	// Get datas for the gym map
	//
	####################################


	case 'gym_map':
		$req 		= "SELECT gym_id, team_id, guard_pokemon_id, gym_points, latitude, longitude, (CONVERT_TZ(last_modified, '+00:00', '".$time_offset."')) as last_modified FROM gym";
		$result 	= $mysqli->query($req);


		$i=0;

		while ($data = $result->fetch_object()) {
			// Team
			// 1 = bleu
			// 2 = rouge
			// 3 = jaune

			switch ($data->team_id) {
				case 0:
					$icon	= 'map_white.png';
					$team	= 'No Team (yet)';
					$color	= 'rgba(0, 0, 0, .6)';
					break;

				case 1:
					$icon	= 'map_blue_';
					$team	= 'Team Mystic';
					$color	= 'rgba(74, 138, 202, .6)';
					break;

				case 2:
					$icon	= 'map_red_';
					$team	= 'Team Valor';
					$color	= 'rgba(240, 68, 58, .6)';
					break;

				case 3:
					$icon	= 'map_yellow_';
					$team	= 'Team Instinct';
					$color	= 'rgba(254, 217, 40, .6)';
					break;
			}

			// Set gym level
			$data->gym_level=0;
			if ($data->gym_points < 2000) {
				$data->gym_level=1;
			} elseif ($data->gym_points < 4000) {
				$data->gym_level=2;
			} elseif ($data->gym_points < 8000) {
				$data->gym_level=3;
			} elseif ($data->gym_points < 12000) {
				$data->gym_level=4;
			} elseif ($data->gym_points < 16000) {
				$data->gym_level=5;
			} elseif ($data->gym_points < 20000) {
				$data->gym_level=6;
			} elseif ($data->gym_points < 30000) {
				$data->gym_level=7;
			} elseif ($data->gym_points < 40000) {
				$data->gym_level=8;
			} elseif ($data->gym_points < 50000) {
				$data->gym_level=9;
			} else {
				$data->gym_level=10;
			}

			## I know, I revert commit 6e8d2e7 from @kiralydavid but the way it was done broke the page.
			if ($data->team_id != 0) {
				$icon .= $data->gym_level.".png";
			}
			$img = 'core/pokemons/'.$data->guard_pokemon_id.'.png';
			$html = '
			<div style="text-align:center">
				<p>Gym owned by:</p>
				<p style="font-weight:400;color:'.$color.'">'.$team.'</p>
				<p>Protected by</p>
				<a href="pokemon/'.$data->guard_pokemon_id.'"><img src="'.$img.'" height="40" style="display:inline-block;margin-bottom:10px;" alt="Guard Pokemon image"></a>
				<p>Level : '.$data->gym_level.' | Prestige : '.$data->gym_points.'<br>
				Last modified : '.$data->last_modified.'</p>
			</div>

			';



			$temp[$i][] = $html;
			$temp[$i][] = $icon;
			$temp[$i][] = $data->latitude;
			$temp[$i][] = $data->longitude;
			$temp[$i][] = $i;
			$temp[$i][] = $data->gym_id;
			$temp[$i][] = $data->gym_level;

			$temp_json[] = json_encode($temp[$i]);


			$i++;
		}

		$return = json_encode($temp_json);

		echo $return;

		break;


	####################################
	//
	// Get datas for gym defenders
	//
	####################################

	case 'gym_defenders':
		$gym_id = $mysqli->real_escape_string($_GET['gym_id']);
		$req 		= "SELECT gymdetails.name as name, gymdetails.description as description, gym.gym_points as points, gymdetails.url as url, gym.team_id as team, (CONVERT_TZ(gym.last_modified, '+00:00', '".$time_offset."')) as last_modified, gym.guard_pokemon_id as guard_pokemon_id FROM gymdetails LEFT JOIN gym on gym.gym_id = gymdetails.gym_id WHERE gym.gym_id='".$gym_id."'";
		$result 	= $mysqli->query($req);
		$gymData['gymDetails']['gymInfos'] = false;
		while ($data = $result->fetch_object()) {
			$gymData['gymDetails']['gymInfos']['name'] = $data->name;
			$gymData['gymDetails']['gymInfos']['description'] = $data->description;
			if ($data->url == null) {
				$gymData['gymDetails']['gymInfos']['url'] = '';
			} else {
				$gymData['gymDetails']['gymInfos']['url'] = $data->url;
			}
			$gymData['gymDetails']['gymInfos']['points'] = $data->points;
			$gymData['gymDetails']['gymInfos']['level'] = 0;
			$gymData['gymDetails']['gymInfos']['last_modified'] = $data->last_modified;
			$gymData['gymDetails']['gymInfos']['team'] = $data->team;
			$gymData['gymDetails']['gymInfos']['guardPokemonId'] = $data->guard_pokemon_id;
			if ($data->points < 2000) {
				$gymData['gymDetails']['gymInfos']['level']=1;
			} elseif ($data->points < 4000) {
				$gymData['gymDetails']['gymInfos']['level']=2;
			} elseif ($data->points < 8000) {
				$gymData['gymDetails']['gymInfos']['level']=3;
			} elseif ($data->points < 12000) {
				$gymData['gymDetails']['gymInfos']['level']=4;
			} elseif ($data->points < 16000) {
				$gymData['gymDetails']['gymInfos']['level']=5;
			} elseif ($data->points < 20000) {
				$gymData['gymDetails']['gymInfos']['level']=6;
			} elseif ($data->points < 30000) {
				$gymData['gymDetails']['gymInfos']['level']=7;
			} elseif ($data->points < 40000) {
				$gymData['gymDetails']['gymInfos']['level']=8;
			} elseif ($data->points < 50000) {
				$gymData['gymDetails']['gymInfos']['level']=9;
			} else {
				$gymData['gymDetails']['gymInfos']['level']=10;
			}
		}
		//print_r($gymData);
		$req 		= "SELECT * FROM gympokemon inner join gymmember on gympokemon.pokemon_uid=gymmember.pokemon_uid where gym_id='".$gym_id."' ORDER BY cp DESC";
		$result 	= $mysqli->query($req);
		$i=0;




		$gymData['infoWindow'] = '
			<div class="gym_defenders">
			';
		while ($data = $result->fetch_object()) {
			$gymData['gymDetails']['pokemons'][] = $data;
			if ($data != false) {
				$gymData['infoWindow'] .= '
				<div style="text-align: center; width: 50px; display: inline-block; margin-right: 3px">
					<a href="pokemon/'.$data->pokemon_id.'">
					<img src="core/pokemons/'.$data->pokemon_id.'.png" height="50" style="display:inline-block" >
					</a>
					<p class="pkmn-name">'.$data->cp.'</p>
					<div class="progress" style="height: 4px; width: 40px; margin-bottom: 10px; margin-top: 2px; margin-left: auto; margin-right: auto">
						<div title="Stamina IV: '.$data->iv_stamina.'" class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="'.$data->iv_stamina.'" aria-valuemin="0" aria-valuemax="45" style="width: '.(((100/15)*$data->iv_stamina)/3).'%">
							<span class="sr-only">Stamina IV: '.$data->iv_stamina.'</span>
						</div>

						<div title="Attack IV: '.$data->iv_attack.'" class="progress-bar progress-bar-danger" role="progressbar" aria-valuenow="'.$data->iv_attack.'" aria-valuemin="0" aria-valuemax="45" style="width: '.(((100/15)*$data->iv_attack)/3).'%">
							<span class="sr-only">Attack IV: '.$data->iv_attack.'</span>
						</div>

						<div title="Defense IV: '.$data->iv_defense.'" class="progress-bar progress-bar-info" role="progressbar" aria-valuenow="'.$data->iv_defense.'" aria-valuemin="0" aria-valuemax="45" style="width: '.(((100/15)*$data->iv_defense)/3).'%">
							<span class="sr-only">Defense IV: '.$data->iv_defense.'</span>
						</div>
					</div>
				</div>'
				;
			} else {
				$gymData['infoWindow'] .= '
				<div style="text-align: center; width: 50px; display: inline-block; margin-right: 3px">
					<a href="pokemon/'.$gymData['gymDetails']['gymInfos']['guardPokemonId'].'">
					<img src="core/pokemons/'.$gymData['gymDetails']['gymInfos']['guardPokemonId'].'.png" height="50" style="display:inline-block" >
					</a>
					<p class="pkmn-name">???</p>
				</div>'
				;
			}
			$i++;
		}
		$gymData['infoWindow'] = $gymData['infoWindow'].'</div>';
		$return = json_encode($gymData);

		echo $return;


		break;

	case 'trainer':
		$name = "";
		$page = "0";
		$where = "";
		$order="";
		$team=0;
		$ranking=0;
		if (isset($_GET['name'])) {
			$trainer_name = mysqli_real_escape_string($mysqli, $_GET['name']);
			$where = " HAVING name LIKE '%".$trainer_name."%'";
		}
		if (isset($_GET['team']) && $_GET['team']!=0) {
			$team = mysqli_real_escape_string($mysqli, $_GET['team']);
			$where .= ($where==""?" HAVING":"AND ")." team = ".$team;
		}
		if (isset($_GET['page'])) {
			$page = mysqli_real_escape_string($mysqli, $_GET['page']);
		}
		if (isset($_GET['ranking'])) {
			$ranking = mysqli_real_escape_string($mysqli, $_GET['ranking']);
		}

		switch ($ranking) {
			case 1:
				$order=" ORDER BY active DESC ";
				break;
			case 2:
				$order=" ORDER BY maxCp DESC ";
				break;
			default:
				$order=" ORDER BY level DESC, active DESC ";
		}

		$limit = " LIMIT ".($page*10).",10 ";


		$req = "SELECT trainer.*, count(actives_pokemons.trainer_name) as active, max(actives_pokemons.cp) as maxCp ".
				"FROM trainer LEFT JOIN (SELECT DISTINCT gympokemon.pokemon_id, gympokemon.pokemon_uid, gympokemon.trainer_name, gympokemon.cp, DATEDIFF(now(), gympokemon.last_seen) AS last_scanned ".
					"FROM gympokemon INNER JOIN ( SELECT gymmember.pokemon_uid, gymmember.gym_id FROM gymmember GROUP BY gymmember.pokemon_uid, gymmember.gym_id HAVING gymmember.gym_id <> '' ) as filtered_gymmember ".
				"ON gympokemon.pokemon_uid = filtered_gymmember.pokemon_uid) as actives_pokemons on actives_pokemons.trainer_name = trainer.name ".
				"GROUP BY trainer.name ".$where.$order.$limit;

		$result = $mysqli->query($req);
		$trainers = array();
		while ($data = $result->fetch_object()) {
			$data->last_seen = date("Y-m-d", strtotime($data->last_seen));
			$trainers[$data->name] = $data;
		};
		foreach ($trainers as $trainer) {
			$reqRanking = "SELECT count(1) as rank FROM trainer where trainer.level >= ".$trainer->level;
			$resultRanking = $mysqli->query($reqRanking);
			while ($data = $resultRanking->fetch_object()) {
				$trainer->rank = $data->rank ;
			}
			$req = "(SELECT DISTINCT gympokemon.pokemon_id, gympokemon.pokemon_uid, gympokemon.cp, DATEDIFF(now(), gympokemon.last_seen) AS last_scanned, gympokemon.trainer_name, gympokemon.iv_defense, gympokemon.iv_stamina, gympokemon.iv_attack, filtered_gymmember.gym_id, '1' as active ".
				"FROM gympokemon INNER JOIN ".
				"( SELECT gymmember.pokemon_uid, gymmember.gym_id FROM gymmember GROUP BY gymmember.pokemon_uid, gymmember.gym_id HAVING gymmember.gym_id <> '' ) as filtered_gymmember ".
				"ON gympokemon.pokemon_uid = filtered_gymmember.pokemon_uid ".
				"WHERE gympokemon.trainer_name='".$trainer->name."' ORDER BY gympokemon.cp DESC)";

			$resultPkms = $mysqli->query($req);
			$trainer->pokemons = array();
			$active_gyms=0;
			$pkmCount = 0;
			while ($resultPkms && $dataPkm = $resultPkms->fetch_object()) {
				$active_gyms++;
				$trainer->pokemons[$pkmCount++] = $dataPkm;
			}
			$trainer->gyms = $active_gyms;

			$req = "(SELECT DISTINCT gympokemon.pokemon_id, gympokemon.pokemon_uid, gympokemon.cp, DATEDIFF(now(), gympokemon.last_seen) AS last_scanned, gympokemon.trainer_name, gympokemon.iv_defense, gympokemon.iv_stamina, gympokemon.iv_attack, null as gym_id, '0' as active ".
				"FROM gympokemon LEFT JOIN ".
				"( SELECT * FROM gymmember HAVING gymmember.gym_id <> '' ) as filtered_gymmember ".
				"ON gympokemon.pokemon_uid = filtered_gymmember.pokemon_uid ".
				"WHERE filtered_gymmember.pokemon_uid is null AND gympokemon.trainer_name='".$trainer->name."' ORDER BY gympokemon.cp DESC ) ";

			$resultPkms = $mysqli->query($req);

			while ($resultPkms && $dataPkm = $resultPkms->fetch_object()) {
				$trainer->pokemons[$pkmCount++] = $dataPkm;
			}
		}
			$return = json_encode($trainers);

			echo $return;

		break;

	default:
		echo "What do you mean?";
		exit();

	break;
}
