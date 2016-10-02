<?php 

// This file only include other files to have only 1 entry in your crontabs. 
// ------------------------------------------------------------------------	

$filePath	= dirname(__FILE__);
$config_file	= $filePath.'/../../config.php'; 

include_once($config_file); 


# MySQL 
$mysqli		= new mysqli(SYS_DB_HOST, SYS_DB_USER, SYS_DB_PSWD, SYS_DB_NAME, SYS_DB_PORT);

if($mysqli->connect_error != ''){
	die('MySQL connect error');
}



$gym_file	= SYS_PATH.'/core/json/gym.stats.json';
$pokestop_file	= SYS_PATH.'/core/json/pokestop.stats.json';
$pokemon_file	= SYS_PATH.'/core/json/pokemon.stats.json';

$gymsdatas	= json_decode(file_get_contents($gym_file), true);
$stopdatas	= json_decode(file_get_contents($pokestop_file), true);
$pokedatas	= json_decode(file_get_contents($pokemon_file), true);


$timestamp	= time();

include_once(SYS_PATH.'/core/cron/gym.cron.php'); 
include_once(SYS_PATH.'/core/cron/pokemon.cron.php'); 
include_once(SYS_PATH.'/core/cron/pokestop.cron.php'); 

?>
