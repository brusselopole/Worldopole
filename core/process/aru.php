<?php

# Well, this file can only be loaded by your own server
# as it contains json datas formatted 
# and you don't want to have other website to get your datas ;) 
# If you want to use this file as an "API" just remove the first condition. 

$pos = strpos($_SERVER['HTTP_REFERER'],getenv('HTTP_HOST'));

if($pos===false){
	http_response_code(401); 
	die('Restricted access');
}


include_once('../../config.php');


# MySQL 
$mysqli 	= new mysqli(SYS_DB_HOST, SYS_DB_USER, SYS_DB_PSWD, SYS_DB_NAME, SYS_DB_PORT);
if($mysqli->connect_error != ''){exit('Error MySQL Connect');}

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
		
		$req 		= "SELECT COUNT(*) as total FROM pokemon WHERE disappear_time > (NOW() - INTERVAL 2 HOUR);";	
		$result 	= $mysqli->query($req);
		$data 		= $result->fetch_object();
		
		$values[] 	= $data->total; 
		
		
		// Lured stops 
		// -----------
		
		$req 		= "SELECT COUNT(*) as total FROM pokestop WHERE lure_expiration > (NOW() - INTERVAL 2 HOUR);";	
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
		
		$req = "SELECT count( DISTINCT(gym_id) ) as total FROM gym WHERE team_id = '2'  "; 
		$result = $mysqli->query($req); 
		$data 	= $result->fetch_object();
		
		// Red
		$values[] = $data->total; 
		
		
		$req = "SELECT count( DISTINCT(gym_id) ) as total FROM gym WHERE team_id = '1'  "; 
		$result = $mysqli->query($req); 
		$data 	= $result->fetch_object();
		
		// Blue
		$values[] = $data->total; 
		
		
		$req = "SELECT count( DISTINCT(gym_id) ) as total FROM gym WHERE team_id = '3'  "; 
		$result = $mysqli->query($req); 
		$data 	= $result->fetch_object();
		
		// Yellow
		$values[] = $data->total; 
		
		$req = "SELECT count( DISTINCT(gym_id) ) as total FROM gym WHERE team_id = '0'  "; 
		$result = $mysqli->query($req); 
		$data 	= $result->fetch_object();
		
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
		
		$req 		= "SELECT pokemon_id FROM pokemon ORDER BY disappear_time DESC LIMIT 0,1";
		$result 	= $mysqli->query($req);
		$recents	= array(); 
		$data 		= $result->fetch_object();
		$pokeid 	= $data->pokemon_id;
		
		$pokelist_file		= SYS_PATH.'/core/json/pokelist_EN.json'; 
		$pokemon_file 		= file_get_contents($pokelist_file); 
		$pokemons			= json_decode($pokemon_file);
	
		
		
		if($_GET['last_id'] != $pokeid){
		
			$html = '
		
			<div class="col-md-1 col-xs-4 wow " pokeid="'.$pokeid.'" style="display:none;">
						
				<a href="/pokemon/'.$pokeid.'"><img src="core/pokemons/'.$pokeid.'.png" class="img-responsive"></a>
				<p class="pkmn-name"><a href="/pokemon/'.$pokeid.'">'.$pokemons->$pokeid->name.'</a></p>
			
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
	
		$req 		= "SELECT * FROM pokestop";
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
	
		
		$teams 	= new stdClass();
		$teams->mystic 		= 1;
		$teams->valor 		= 2;
		$teams->instinct 	= 3; 
		
		
		foreach($teams as $team_name => $team_id){
			
			
			$req = "SELECT count( DISTINCT(gym_id) ) as total FROM gym WHERE team_id = '".$team_id."'  "; 
			$result = $mysqli->query($req); 
			$data 	= $result->fetch_object();
			
			$total_gym 	= $data->total;
			$return[] 	= $data->total; 
			
			
			
			$req 	= "SELECT gym_points FROM gym WHERE team_id = '".$team_id."'  "; 
			$result = $mysqli->query($req); 
			
			$total_points=0; 
			
			while($data = $result->fetch_object()){
			
				$total_points = $total_points + $data->gym_points; 
				
			}
			
			$average  = round($total_points / $total_gym); 
			$return[] = $average;
			
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
	
		$req 		= "SELECT * FROM gym";
		$result 	= $mysqli->query($req); 
		
		
		$i=0; 
		
		while($data = $result->fetch_object()){		
			
			// Team 
			// 1 = bleu
			// 2 = rouge 
			// 3 = jaune 
			
			switch($data->team_id){
				
				case 0:
					$icon = 'map_white.png';
					$team = 'No Team (yet)';
					$color 	= 'rgba(0, 0, 0, .6)'; 
				break;
				
				case 1:
					$icon = 'map_blue.png';
					$team = 'Team Mystic';
					$color 	= 'rgba(74, 138, 202, .6)';
				break;
				
				case 2:
					$icon 	= 'map_red.png';
					$team 	= 'Team Valor';
					$color 	= 'rgba(240, 68, 58, .6)';
				break;
				
				case 3:
					$icon = 'map_yellow.png';
					$team = 'Team Instinct';
					$color 	= 'rgba(254, 217, 40, .6)';
				break;
				
			}
			
			## I know, I revert commit 6e8d2e7 from @kiralydavid but the way it was done broke the page. 
			
			$img = '/core/pokemons/'.$data->guard_pokemon_id.'.png';
			$html = '
			
			<div style="text-align:center">
				<p>Gym owned by:</p>
				<p style="font-weight:400;color:'.$color.'">'.$team.'</p>
				<p>Protected by</p>
				<a href="/pokemon/'.$data->guard_pokemon_id.'"><img src="'.$img.'" height="40" style="display:inline-block;margin-bottom:10px;"></a>
				<p>Level : '.substr($data->gym_points,0,1).' | Prestige : '.$data->gym_points.'</p>
			</div>
	
			';

			
			
			$temp[$i][] = $html;
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
	
	
	case '':
	break; 
	
	
	default:
		
		echo "What do you mean?";
		exit();
		
	break; 
		
	
}






	
?>
