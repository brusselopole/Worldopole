<?php

# Well, this file can only be loaded by your own server
# as it contains json datas formatted 
# and you don't want to have other website to get your datas ;) 
# If you want to use this file as an "API" just remove the first condition. 

$pos = !empty($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'],getenv('HTTP_HOST'));

if($pos===false){
	http_response_code(401); 
	die('Restricted access');
}


include_once('../../config.php');



// Include & load the variables 
// ############################

$variables 	= realpath(dirname(__FILE__)).'/../json/variables.json';
$config 	= json_decode(file_get_contents($variables)); 



// Manage Time Interval
// #####################

include_once('timezone.loader.php');


// Load the locale elements
############################

include_once('locales.loader.php');


# MySQL 
$mysqli 	= new mysqli(SYS_DB_HOST, SYS_DB_USER, SYS_DB_PSWD, SYS_DB_NAME, SYS_DB_PORT);
if($mysqli->connect_error != ''){exit('Error MySQL Connect');}
$mysqli->set_charset('utf8');
$request 	= $_GET['type']; 

switch($request){
	
	
	
	############################
	//
	// Update datas on homepage
	// 
	############################
	
	case 'home_update':
	
		// Right now 
		// ---------
		
		$req 		= "SELECT COUNT(*) as total FROM pokemon WHERE disappear_time > (NOW() ".$time->symbol_reverse." INTERVAL ".$time->delay." HOUR);";	
		$result 	= $mysqli->query($req);
		$data 		= $result->fetch_object();
		
		$values[] 	= $data->total; 
		
		
		// Lured stops 
		// -----------
		
		$req 		= "SELECT COUNT(*) as total FROM pokestop WHERE lure_expiration > (NOW() ".$time->symbol_reverse." INTERVAL ".$time->delay." HOUR);";	
		$result 	= $mysqli->query($req);
		$data 		= $result->fetch_object();
		
		$values[] 	= $data->total;
		
		
		
		// Team battle 
		// -----------
		
		$req 		= "SELECT count( DISTINCT(gym_id) ) as total FROM gym";
		$result 	= $mysqli->query($req); 
		$data 		= $result->fetch_object();
		$total_gym 	= $data->total; 	
		
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
		
		if ($config->system->mythic_recents) {
			// get all mythic pokemon ids
			$mythic_pokemons = array();
			foreach($pokemons->pokemon as $id=>$pokemon) {
				if ($pokemon->spawn_rate < 0.01) {
					$mythic_pokemons[] = $id;
				}
			}

			// get last mythic pokemon
			$req		= "SELECT pokemon_id FROM pokemon
					   WHERE pokemon_id IN (".implode(",", $mythic_pokemons).")
					   ORDER BY disappear_time DESC LIMIT 0,1";
		} else {
			// get last pokemon
			$req		= "SELECT pokemon_id FROM pokemon ORDER BY disappear_time DESC LIMIT 0,1";
		}
		$result 	= $mysqli->query($req);
		$recents	= array(); 
		$data 		= $result->fetch_object();
		$pokeid 	= $data->pokemon_id;
		
		if($_GET['last_id'] != $pokeid){
			$html = '
		
			<div class="col-md-1 col-xs-4 pokemon-single" pokeid="'.$pokeid.'" style="display:none;">
						
				<a href="pokemon/'.$pokeid.'"><img src="core/pokemons/'.$pokeid.'.png" alt="'.$pokemons->pokemon->$pokeid->name.'" class="img-responsive"></a>
				<p class="pkmn-name"><a href="pokemon/'.$pokeid.'">'.$pokemons->pokemon->$pokeid->name.'</a></p>
			
			</div>	
				
			';
			
			
			echo $html; 
		}
	
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
		
		while($data = $result->fetch_object()){		
			
			if($data->lure_expiration != ''){
				$icon = 'pokestap_lured.png';
				$text = 'Lured expire @ '.date('h:i:s', strtotime($data->lure_expiration)+(3600*2)) ;
			}
			else{
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
		
		
		foreach($teams as $team_name => $team_id){
			
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
	
		$req 		= "SELECT gym_id, team_id, guard_pokemon_id, gym_points, latitude, longitude, (last_modified ".$time->symbol." INTERVAL ".$time->delay." HOUR) as last_modified FROM gym";
		$result 	= $mysqli->query($req); 
		
		
		$i=0; 
		
		while($data = $result->fetch_object()){		
			
			// Team 
			// 1 = bleu
			// 2 = rouge 
			// 3 = jaune 
			
			switch($data->team_id){
				
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
			if ($data->gym_points < 2000) { $data->gym_level=1;	}
			elseif ($data->gym_points < 4000) { $data->gym_level=2; }
			elseif ($data->gym_points < 8000) { $data->gym_level=3; }
			elseif ($data->gym_points < 12000) { $data->gym_level=4; }
			elseif ($data->gym_points < 16000) { $data->gym_level=5; }
			elseif ($data->gym_points < 20000) { $data->gym_level=6; }
			elseif ($data->gym_points < 30000) { $data->gym_level=7; }
			elseif ($data->gym_points < 40000) { $data->gym_level=8; }
			elseif ($data->gym_points < 50000) { $data->gym_level=9; }
			else { $data->gym_level=10; }

			## I know, I revert commit 6e8d2e7 from @kiralydavid but the way it was done broke the page. 
			if($data->team_id != 0){
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
		$req 		= "SELECT gymdetails.name as name, gymdetails.description as description, gym.gym_points as points, gymdetails.url as url, gym.team_id as team,  (gym.last_modified ".$time->symbol." INTERVAL ".$time->delay." HOUR) as last_modified, gym.guard_pokemon_id as guard_pokemon_id FROM gymdetails LEFT JOIN gym on gym.gym_id = gymdetails.gym_id WHERE gym.gym_id='".$gym_id."'";
		$result 	= $mysqli->query($req);
		$gymData['gymDetails']['gymInfos'] = false;
		while($data = $result->fetch_object()){
			$gymData['gymDetails']['gymInfos']['name'] = $data->name;
			$gymData['gymDetails']['gymInfos']['description'] = $data->description;
			if($data->url == null){
				$gymData['gymDetails']['gymInfos']['url'] = '';
			}
			else{
				$gymData['gymDetails']['gymInfos']['url'] = $data->url;
			}
			$gymData['gymDetails']['gymInfos']['points'] = $data->points;
			$gymData['gymDetails']['gymInfos']['level'] = 0;
			$gymData['gymDetails']['gymInfos']['last_modified'] = $data->last_modified;
			$gymData['gymDetails']['gymInfos']['team'] = $data->team;
			$gymData['gymDetails']['gymInfos']['guardPokemonId'] = $data->guard_pokemon_id;
			if ($data->points < 2000) { $gymData['gymDetails']['gymInfos']['level']=1;	}
			elseif ($data->points < 4000) { $gymData['gymDetails']['gymInfos']['level']=2; }
			elseif ($data->points < 8000) { $gymData['gymDetails']['gymInfos']['level']=3; }
			elseif ($data->points < 12000) { $gymData['gymDetails']['gymInfos']['level']=4; }
			elseif ($data->points < 16000) { $gymData['gymDetails']['gymInfos']['level']=5; }
			elseif ($data->points < 20000) { $gymData['gymDetails']['gymInfos']['level']=6; }
			elseif ($data->points < 30000) { $gymData['gymDetails']['gymInfos']['level']=7; }
			elseif ($data->points < 40000) { $gymData['gymDetails']['gymInfos']['level']=8; }
			elseif ($data->points < 50000) { $gymData['gymDetails']['gymInfos']['level']=9; }
			else { $gymData['gymDetails']['gymInfos']['level']=10; }
			
		
		}
		//print_r($gymData);
		$req 		= "SELECT * FROM gympokemon inner join gymmember on gympokemon.pokemon_uid=gymmember.pokemon_uid where gym_id='".$gym_id."' ORDER BY cp DESC";
		$result 	= $mysqli->query($req); 
		$i=0; 
		
		
		
		
		$gymData['infoWindow'] = '
			<div class="gym_defenders">			
			';
		while($data = $result->fetch_object()){
			$gymData['gymDetails']['pokemons'][] = $data;
			if($data != false) {
			$gymData['infoWindow'] .= '
				<div style="text-align: center; width: 50px; display: inline-block; margin-right: 3px">
					<a href="pokemon/'.$data->pokemon_id.'">
					<img src="core/pokemons/'.$data->pokemon_id.'.png" height="50" style="display:inline-block" >
					</a>
					<p class="pkmn-name">'.$data->cp.'</p>
					<div class="progress" style="height: 4px; width: 40px; margin-bottom: 10px; margin-top: 2px; margin-left: auto; margin-right: auto">
						<div title="IV Stamina: '.$data->iv_stamina.'" class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="'.$data->iv_stamina.'" aria-valuemin="0" aria-valuemax="45" style="width: '.(((100/15)*$data->iv_stamina)/3).'%">
							<span class="sr-only">Stamina IV : '.$data->iv_stamina.'</span>
						</div>
					
						<div title="IV Attack: '.$data->iv_attack.'" class="progress-bar progress-bar-danger" role="progressbar" aria-valuenow="'.$data->iv_attack.'" aria-valuemin="0" aria-valuemax="45" style="width: '.(((100/15)*$data->iv_attack)/3).'%">
							<span class="sr-only">Attack IV : '.$data->iv_attack.'</span>
						</div>

						<div title="IV Defense: '.$data->iv_defense.'" class="progress-bar progress-bar-info" role="progressbar" aria-valuenow="'.$data->iv_defense.'" aria-valuemin="0" aria-valuemax="45" style="width: '.(((100/15)*$data->iv_defense)/3).'%">
							<span class="sr-only">Defense IV : '.$data->iv_defense.'</span>
						</div>
					</div>
				</div>'
				;
			}
			else{
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
			if(isset($_GET['name'])){			
				$trainer_name = mysqli_real_escape_string($mysqli,$_GET['name']);
				$where = " WHERE name LIKE '%".$trainer_name."%'";
			}
			
			if(isset($_GET['page'])){			
				$page = mysqli_real_escape_string($mysqli,$_GET['page']);
			}
			
			$orderAndLimit = " ORDER BY level DESC LIMIT ".($page*10).",10 ";
			
			
			$req = "SELECT * FROM trainer ".$where.$orderAndLimit;
			
			
			$result = $mysqli->query($req);
	        	$trainers = array();
		        while($data = $result->fetch_object()){
				$data->last_seen = date("Y-m-d", strtotime($data->last_seen));
				$trainers[$data->name] = $data;
			};
			foreach($trainers as $trainer){
				$reqRanking = "SELECT count(1) as rank FROM trainer where trainer.level >= ".$trainer->level;
				$resultRanking = $mysqli->query($reqRanking);
				while($data = $resultRanking->fetch_object()){
					$trainer->rank = $data->rank ;
				}
				$req = "(SELECT DISTINCT gympokemon.pokemon_id, gympokemon.pokemon_uid, gympokemon.cp, gympokemon.trainer_name, gympokemon.iv_defense, gympokemon.iv_stamina, gympokemon.iv_attack, filtered_gymmember.gym_id, '1' as active ".
						"FROM gympokemon INNER JOIN ".
						"( SELECT  * FROM gymmember GROUP BY gymmember.pokemon_uid HAVING gymmember.gym_id <> '' ) as filtered_gymmember ".
						"ON gympokemon.pokemon_uid = filtered_gymmember.pokemon_uid ".
						"WHERE gympokemon.trainer_name='".$trainer->name."' ORDER BY gympokemon.cp DESC)";
							
				$resultPkms = $mysqli->query($req);
				$trainer->pokemons = array();
				$active_gyms=0;
				$pkmCount = 0;
				while($resultPkms && $dataPkm = $resultPkms->fetch_object()){
					$active_gyms++;
					$trainer->pokemons[$pkmCount++] = $dataPkm;
				}
				$trainer->gyms = $active_gyms;
				
				$req =  "(SELECT DISTINCT gympokemon.pokemon_id, gympokemon.pokemon_uid, gympokemon.cp, gympokemon.trainer_name, gympokemon.iv_defense, gympokemon.iv_stamina, gympokemon.iv_attack, null as gym_id, '0' as active ".
						"FROM gympokemon LEFT JOIN ".
						"( SELECT  * FROM gymmember HAVING gymmember.gym_id <> '' ) as filtered_gymmember ".
						"ON gympokemon.pokemon_uid = filtered_gymmember.pokemon_uid ".
						"WHERE filtered_gymmember.pokemon_uid is null AND gympokemon.trainer_name='".$trainer->name."' ORDER BY gympokemon.cp DESC ) ";
							
				$resultPkms = $mysqli->query($req);
				
				while($resultPkms && $dataPkm = $resultPkms->fetch_object()){
					$trainer->pokemons[$pkmCount++] = $dataPkm;
				}
				
			}
			// Sort for level first, then gyms
			foreach($trainers as $trainer){
				$level[] = $trainer->level;
				$gyms[] = $trainer->gyms;
			}
			if(!empty($trainers)){
				array_multisort($level, SORT_DESC, $gyms, SORT_DESC, $trainers);
			}
			$return = json_encode($trainers);
			
			echo $return;
			
 
		break; 
	
	default:
		
		echo "What do you mean?";
		exit();
		
	break; 
		
	
}






	
?>
