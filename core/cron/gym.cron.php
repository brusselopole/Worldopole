<?php
	
// -----------------------------------------------------------------------------------------------------------
// Gym datas 
// Total gym
// Gym / team 
// Average level / team 
// -----------------------------------------------------------------------------------------------------------


$gym['timestamp']	= $timestamp; 

$req		= "SELECT count( DISTINCT(gym_id) ) as total FROM gym";
$result		= $mysqli->query($req); 
$data		= $result->fetch_object();

$gym['total'] 	= $data->total; 


// Mystic

$req		= "SELECT count( DISTINCT(gym_id) ) as total FROM gym WHERE team_id = '1'  "; 
$result		= $mysqli->query($req); 
$data		= $result->fetch_object();

$gym['team']['mystic']['gym_owned']	= $data->total; 



$req		= "SELECT gym_points FROM gym WHERE team_id = '1'  "; 
$result		= $mysqli->query($req); 

$total_points=0; 

while($data = $result->fetch_object()){

	$total_points = $total_points + $data->gym_points; 
	
}

$gym['team']['mystic']['average']		= round($total_points / $gym['team']['mystic']['gym_owned']);



// Valor

$req		= "SELECT count( DISTINCT(gym_id) ) as total FROM gym WHERE team_id = '2'  "; 
$result		= $mysqli->query($req); 
$data		= $result->fetch_object();

$gym['team']['valor']['gym_owned']		= $data->total; 



$req		= "SELECT gym_points FROM gym WHERE team_id = '2'  "; 
$result		= $mysqli->query($req); 

$total_points=0; 

while($data = $result->fetch_object()){

	$total_points = $total_points + $data->gym_points; 
	
}

$gym['team']['valor']['average']		= round($total_points / $gym['team']['valor']['gym_owned']);


// Instinct

$req		= "SELECT count( DISTINCT(gym_id) ) as total FROM gym WHERE team_id = '3'  "; 
$result		= $mysqli->query($req); 
$data		= $result->fetch_object();

$gym['team']['instinct']['gym_owned'] 		= $data->total; 



$req		= "SELECT gym_points FROM gym WHERE team_id = '3'  "; 
$result		= $mysqli->query($req); 

$total_points=0; 

while($data = $result->fetch_object()){

	$total_points = $total_points + $data->gym_points; 
	
}

$gym['team']['instinct']['average'] 		= round($total_points / $gym['team']['instinct']['gym_owned']);


// Add the datas in file

$gymsdatas[]		= $gym; 
$json			= json_encode($gymsdatas); 

file_put_contents($gym_file, $json);

?>
