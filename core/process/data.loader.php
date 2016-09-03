<?php
	
// Include & load the variables 
// ############################

$variables 	= realpath(dirname(__FILE__)).'/../json/variables.json';
$config 	= json_decode(file_get_contents($variables)); 


// Set default timezone
// #####################
date_default_timezone_set($config->system->timezone);


// Manage Time Interval
// #####################



$time_interval  = strlen($config->system->time_inverval); 

if($time_interval > 3){
	echo 'Bad formated time_interval in variables.json. Please use +X or -X format only (eg for Brussels : +2) without leading or ending space.';
	exit(); 
}


$time			= new stdClass();
$time->symbol 	= substr($config->system->time_inverval, 0,1);
$time->delay 	= substr($config->system->time_inverval, 1,1);

if($time->symbol == '+'){
	$time->symbol_reverse = '-';
}elseif($time->symbol == '-'){
	$time->symbol_reverse = '+';
}else{
	
	echo 'Bad formated time_interval in variables.json. Please use +X or -X format only (eg for Brussels : +2) without leading or ending space.';
	exit(); 
}

	
// Debug mode
#############

if(SYS_DEVELOPMENT_MODE){
	error_reporting(E_ALL);
}
	

// MySQL Connect
#################

 
$mysqli 	= new mysqli(SYS_DB_HOST, SYS_DB_USER, SYS_DB_PSWD, SYS_DB_NAME, SYS_DB_PORT);


if($mysqli->connect_error != ''){
	
	header('Location:/offline.html');
	exit();
	
}


// Perform some tests to be sure that we got datas and rights
// If not we lock the website (HA-HA-HA evil laught)
// Those test are performed once.  
##############################################################


if(!file_exists(SYS_PATH.'/install/website.lock')){
	
	if(!file_exists(SYS_PATH.'/install/done.lock')){
	
		if (version_compare(phpversion(), '5.3.10', '<')) {
			echo "Sorry, your PHP version isn't high enough and contain security hole. Please update";
			exit();  
		}

		
		include_once('install/tester.php');
		
		data_test();
		rights_test(); 
		
		if(file_exists(SYS_PATH.'/install/website.lock')){
			
			header('Location:/');
			exit(); 
			
		}
		else{
			
			$content = time(); 
			file_put_contents(SYS_PATH.'/install/done.lock', $content);
			
		}
	
	}
	
}
else{
	
	$content = file_get_contents(SYS_PATH.'/install/website.lock');
	echo $content; 
	
	exit();
	
}
 


// Language setting
###################

if(isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])){

	$browser_lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);

}else{

	$browser_lang = 'en';

}


// Search if language is available. 
 
foreach($config->lang as $id_lang => $lang_active){
			
	if($id_lang == $browser_lang){
		$lang = strtoupper($id_lang); 
	}
	
}


// If the language is available in variables just check if neeeded files exists.  
if(isset($lang)){
	 
	$pokedex = SYS_PATH.'/core/json/pokelist_'.$lang.'.json'; 
	
	// If there's no pokedex in languague we'll use the english one. 
	if(!file_exists($pokedex)){
		$pokedex = SYS_PATH.'/core/json/pokelist_EN.json'; 
	}
	 
	 
}else{
	$lang 		= 'EN';
	$pokedex 	= SYS_PATH.'/core/json/pokelist_EN.json';
}


// JSON files, based on language selection 
##########################################

$pokemon_file 		= file_get_contents($pokedex); 
$translation_file 	= file_get_contents(SYS_PATH.'/core/json/translations.json'); 



// Update the pokemon list for rarety
######################################
// ( for Brusselopole we use CRONTAB but as we're not sure that every had access to it we build this really simple false crontab system
// => check filemtime, if > 24h launch an update. ) 

$pokelist_filetime 	= filemtime($pokedex);
$now				= time(); 
$diff				= $now - $pokelist_filetime; 

// Update each 24h 
$update_delay		= (60*60)*24; 


if($diff > $update_delay){	
	include_once(SYS_PATH.'/core/cron/pokemon.rarety.php');
}




// Loading JSON files 
#####################

$pokemons			= json_decode($pokemon_file);
$locales 			= json_decode($translation_file); 




##########################
//
// Pages data loading 
//
########################## 

if(isset($_GET['page'])){
	$page = htmlentities($_GET['page']);
}else{
	$page = ''; 
}

if(!empty($page)){
	
	switch($page){
		
		
		// Single Pokemon 
		#################
		
		case 'pokemon':
		
			// Current Pokemon datas 
			// ---------------------
			
			
			$pokemon_id 				= mysqli_real_escape_string($mysqli,$_GET['id']);
			
			if(!is_object($pokemons->$pokemon_id)){
				
				header('Location:/404');
				exit(); 
				
			}
			
						
			$pokemon					= new stdClass(); 			 
			$pokemon					= $pokemons->$pokemon_id;
			$pokemon->id				= $pokemon_id;
			
			
			
			// Some math 
			// ----------
			
			$pokemon->max_cp_percent 	= percent(4145,$pokemon->max_cp);
			$pokemon->max_pv_percent 	= percent(407,$pokemon->max_pv);
			
			
			
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
			
			if($pokemon->total_spawn > 0){
			
				$req 		= "SELECT COUNT(*) as total, (disappear_time ".$time->symbol." INTERVAL ".$time->delay." HOUR) as disappear_time  FROM pokemon WHERE pokemon_id = '".$pokemon_id."' GROUP BY DAY(disappear_time ".$time->symbol." INTERVAL ".$time->delay." HOUR)";
				$result 	= $mysqli->query($req);
				
				$pokemon->total_days 	= $result->num_rows;
				$pokemon->spawn_rate 	= 	round($pokemon->total_spawn/$pokemon->total_days);
				
			}else{
				
				$pokemon->total_days 	= 0; 
				$pokemon->spawn_rate	= 0; 
			}
				
						
			// Last seen 
			
			$req 		= "SELECT (disappear_time ".$time->symbol." INTERVAL ".$time->delay." HOUR) as disappear_time, latitude, longitude FROM pokemon WHERE pokemon_id = '".$pokemon_id."' AND disappear_time < (NOW() ".$time->symbol_reverse." INTERVAL ".$time->delay." HOUR) ORDER BY disappear_time DESC LIMIT 0,1";
			$result 	= $mysqli->query($req);
			$data 		= $result->fetch_object();
						
			if(isset($data)){
			
				$last_spawn 			= $data;
				
				$pokemon->last_seen					= strtotime($data->disappear_time);
				$pokemon->last_position				= new stdClass();
				$pokemon->last_position->latitude 	= $data->latitude;
				$pokemon->last_position->longitude 	= $data->longitude; 
			
			}
			
			
			// Related Pokemons
			// ----------------
			
			foreach($pokemon->types as $type){
				$types[] = $type; 
			}
			
			$related = array(); 
			$i = 1; 
			
			foreach($pokemons as $test_pokemon){
				
				foreach($test_pokemon->types as $type){
					
					if(in_array($type, $types)){
						
						if(!in_array($i, $related)){
						
							$related[] = $i;
						
						}
						 
						
					}
					
				}
				
				$i++; 
							
			}
		
		break; 
		
		
		
		// Pokedex  
		##########
		
		case 'pokedex':
		
			
			// Pokemon List from the JSON file
			// --------------------------------
			
			
			$max 		= $config->system->max_pokemon; 
			$pokedex 	= new stdClass();
			
			for( $i= 1 ; $i <= $max ; $i++ ){
				
				$pokedex->$i				= new stdClass();
				$pokedex->$i->id 			= $i; 
				$pokedex->$i->permalink 	= 'pokemon/'.$i; 
				$pokedex->$i->img			= 'core/pokemons/'.$i.'.png'; 
				$pokedex->$i->name			= $pokemons->$i->name; 
				
				$req 		= "SELECT COUNT(*) as total FROM pokemon WHERE pokemon_id = '".$i."'";
				$result 	= $mysqli->query($req);
				$data 		= $result->fetch_object();
				
				$pokedex->$i->spawn			= $data->total; 
							
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
			
			$req 		= "SELECT COUNT(*) as total FROM pokestop WHERE lure_expiration > (NOW() ".$time->symbol_reverse." INTERVAL ".$time->delay." HOUR)";
			$result 	= $mysqli->query($req);
			$data 		= $result->fetch_object();
			
			$pokestop->lured = $data->total;
			
		
		
		break;


		// Gyms  
		########

		
		case 'gym':
		
			// 3 Teams (teamm rocket is neutral)
			// 1 Fight 
			
			$teams 						= new stdClass();
						
			$teams->mystic 				= new stdClass();
			$teams->mystic->guardians 	= new stdClass();
			$teams->mystic->id 			= 1;
			
			$teams->valor 				= new stdClass();
			$teams->valor->guardians 	= new stdClass();
			$teams->valor->id 			= 2;
			
			$teams->instinct 			= new stdClass();
			$teams->instinct->guardians = new stdClass();
			$teams->instinct->id 		= 3;

			$teams->rocket 				= new stdClass();
			$teams->rocket->guardians 	= new stdClass();
			$teams->rocket->id 			= 0;
			
			
			
			foreach($teams as $team_key => $team_values){
				
				
				// Team Guardians 
				
				$req 	= "SELECT COUNT(*) as total, guard_pokemon_id FROM gym WHERE team_id = '".$team_values->id."' GROUP BY guard_pokemon_id ORDER BY total DESC LIMIT 0,3 "; 	
				$result = $mysqli->query($req); 
				
				$i=0; 
				
				while($data = $result->fetch_object()){
					
					$gym['valor']['fav_pokemon'][] = $data->guard_pokemon_id;
					
					$teams->$team_key->guardians->$i	= $data->guard_pokemon_id;
					
					$i++; 
				
				}
				
				
				// Gym owned 
				
				$req 	= "SELECT count( DISTINCT(gym_id) ) as total FROM gym WHERE team_id = '".$team_values->id."'  "; 
				$result = $mysqli->query($req); 
				$data 	= $result->fetch_object();
			
				$teams->$team_key->gym_owned 			= $data->total;
				
				
				// Total Points 
				
				$req 	= "SELECT gym_points FROM gym WHERE team_id = '".$team_values->id."'  "; 
				$result = $mysqli->query($req); 
				
				$total_points=0; 
				
				while($data = $result->fetch_object()){
				
					$total_points = $total_points + $data->gym_points; 
					
				}
				
				$teams->$team_key->total_points 	= $total_points; 
				
				
				// Some math for the average 
				
				if($total_points > 0){
					$teams->$team_key->average 			= round($teams->$team_key->total_points / $teams->$team_key->gym_owned);
				}else{
					$teams->$team_key->average 			= 0;
				}
				
				
				
				
			}
						
						
					
		break;
		
	}
	
}


/////////////
// Homepage  
///////////// 

else{
	
	
	$home = new stdClass();
	
	// Right now 
	// ---------
	
	$req 		= "SELECT COUNT(*) as total FROM pokemon WHERE disappear_time > (NOW() ".$time->symbol_reverse." INTERVAL ".$time->delay." HOUR);";	
	$result 	= $mysqli->query($req);
	$data 		= $result->fetch_object();
	
	
	$home->pokemon_now 	= $data->total;
	 	
	
	
	// Lured stops 
	// -----------
	
	$req 		= "SELECT COUNT(*) as total FROM pokestop WHERE lure_expiration > (NOW() ".$time->symbol_reverse." INTERVAL ".$time->delay." HOUR);";	
	$result 	= $mysqli->query($req);
	$data 		= $result->fetch_object();
	
	$home->pokestop_lured = $data->total; 	
	
	
	// Gyms 
	// ----
	
	$req 		= "SELECT COUNT(*) as total FROM gym WHERE enabled = '1'";	
	$result 	= $mysqli->query($req);
	$data 		= $result->fetch_object();
	
	$home->gyms = $data->total; 	
	
	
	
	
	
	
	// Recent spawn
	// ------------
	
	$req 		= "SELECT DISTINCT pokemon_id, disappear_time FROM pokemon ORDER BY disappear_time DESC LIMIT 0,12";
	$result 	= $mysqli->query($req);
	$recents	= array(); 
	
	while($data = $result->fetch_object()){
		
		$recents[] = $data->pokemon_id;

	}
		
	
	
	$home->teams = new stdClass();
	
	// Team battle 
	// -----------
	
	$req 		= "SELECT count( DISTINCT(gym_id) ) as total FROM gym";
	$result 	= $mysqli->query($req); 
	$data 		= $result->fetch_object();
	
	$home->total_gyms 		= $data->total; 	
	
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
	
	
?>