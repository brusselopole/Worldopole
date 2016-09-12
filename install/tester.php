<?php
	
	
########################################################################
// Test function 
// This happend once to be sure database is still full of datas
// to avoid errors on website
########################################################################
	
function data_test(){
	
	
	$mysqli 	= new mysqli(SYS_DB_HOST, SYS_DB_USER, SYS_DB_PSWD, SYS_DB_NAME, SYS_DB_PORT);
		
	####################
	// Database tests 
	####################
	
	// Pokemon Test 

	$req 		= "SELECT COUNT(*) as total FROM pokemon";
	$result 	= $mysqli->query($req); 
	
	$lock_msg 	= ''; 
	
	if(!is_object($result)){
		
		$lock_msg .= "No Pokémon database found \r"; 
		
	}
	else{
		
		$data = $result->fetch_object();
		$total = $data->total; 
		
		if($total == 0){
			
			$lock_msg .= "No Pokémon found is your database \r";
		}
		
	}
	
	// Gym Test 
	
	$req 		= "SELECT COUNT(*) as total FROM gym";
	$result 	= $mysqli->query($req); 
	
	$lock_msg 	= ''; 
	
	if(!is_object($result)){
		
		$lock_msg .= "No Gym database found \r"; 
		
	}
	else{
		
		$data = $result->fetch_object();
		$total = $data->total; 
		
		if($total == 0){
			
			$lock_msg .= "No Gym found is your database \r";
		}
		
	}
	
	
	// Pokéstop Test 
	
	
	$req 		= "SELECT COUNT(*) as total FROM pokestop";
	$result 	= $mysqli->query($req); 
	
	$lock_msg 	= ''; 
	
	if(!is_object($result)){
		
		$lock_msg .= "No Pokestop database found \r"; 
		
	}
	else{
		
		$data = $result->fetch_object();
		$total = $data->total; 
		
		if($total == 0){
			
			$lock_msg .= "No Pokestop found is your database \r";
		}
		
	}

	
	
	if($lock_msg != ''){
		
		$lock_file = SYS_PATH.'/install/website.lock';
		file_put_contents($lock_file, $lock_msg); 
		
	}
	
	
	
}	



function rights_test(){
	
		
	// Can we write on install folder? 
	
	if(!is_writable(SYS_PATH.'/install/')){
		
		// Well, the all system is fucked ... exit exit exit ! 
		
		$lock_msg = "Install can not be complete. Please fix /install/ directory rights";
		
		echo $lock_msg; 
		exit(); 
		
	
	}else{
		
		
		// Can we read JSON file? 
		$json_test_file = SYS_PATH.'/core/json/pokelist_EN.json';
		$lock_msg		= ''; 
		
		if(!is_readable($json_test_file)){
			
			$lock_msg .= "JSon files are not readable. Please fix rights \r";
			
		}
		
		if(!is_writable($json_test_file)){
			
			$lock_msg .= "JSon files are not writable. Please fix rights \r";
		}

		
		if($lock_msg != ''){
		
			$lock_file = SYS_PATH.'/website.lock';
			file_put_contents($lock_file, $lock_msg); 
		
		}
		
	}
	
	
	
	
}


?>