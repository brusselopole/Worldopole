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

$req		= "SELECT count(DISTINCT(gym_id)) as total, ROUND(AVG(gym_points),0) as average_points FROM gym WHERE team_id = '1'";
$result		= $mysqli->query($req);
$data		= $result->fetch_object();

$gym['team']['mystic']['gym_owned']	= $data->total;
$gym['team']['mystic']['average']	= $data->average_points;


// Valor

$req		= "SELECT count(DISTINCT(gym_id)) as total, ROUND(AVG(gym_points),0) as average_points FROM gym WHERE team_id = '2'";
$result		= $mysqli->query($req);
$data		= $result->fetch_object();

$gym['team']['valor']['gym_owned']	= $data->total;
$gym['team']['valor']['average']	= $data->average_points;


// Instinct

$req		= "SELECT count(DISTINCT(gym_id)) as total, ROUND(AVG(gym_points),0) as average_points FROM gym WHERE team_id = '3'";
$result		= $mysqli->query($req);
$data		= $result->fetch_object();

$gym['team']['instinct']['gym_owned'] 	= $data->total;
$gym['team']['instinct']['average'] 	= $data->average_points;


// Add the datas in file

$gymsdatas[]		= $gym; 
$json			= json_encode($gymsdatas); 

file_put_contents($gym_file, $json);

?>
