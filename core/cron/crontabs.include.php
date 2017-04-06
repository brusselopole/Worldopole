<?php

// This file only include other files to have only 1 entry in your crontabs. 
// ------------------------------------------------------------------------	

$filePath	= dirname(__FILE__);
$config_file	= $filePath.'/../../config.php';

include_once($config_file);

// Load variables.json
$variables      = $filePath.'/../json/variables.json';
$config         = json_decode(file_get_contents($variables));
// force english language for all cron stuff
$config->system->forced_lang = 'en';

// Manage Time Interval
// #####################

include_once($filePath.'/../process/timezone.loader.php');


# MySQL 
$mysqli		= new mysqli(SYS_DB_HOST, SYS_DB_USER, SYS_DB_PSWD, SYS_DB_NAME, SYS_DB_PORT);

if ($mysqli->connect_error != '') {
	die('MySQL connect error');
}



$gym_file	= SYS_PATH.'/core/json/gym.stats.json';
$pokestop_file	= SYS_PATH.'/core/json/pokestop.stats.json';
$pokemonstats_file	= SYS_PATH.'/core/json/pokemon.stats.json';


if (is_file($gym_file)) {
	$gymsdatas	= json_decode(file_get_contents($gym_file), true);
}
if (is_file($pokestop_file)) {
	$stopdatas	= json_decode(file_get_contents($pokestop_file), true);
}
if (is_file($pokemonstats_file)) {
	$pokedatas	= json_decode(file_get_contents($pokemonstats_file), true);
}

$timestamp	= time();

include_once(SYS_PATH.'/core/cron/gym.cron.php');
include_once(SYS_PATH.'/core/cron/pokemon.cron.php');
include_once(SYS_PATH.'/core/cron/pokestop.cron.php');
include_once(SYS_PATH.'/core/cron/nests.cron.php');
if ($config->system->captcha_support) {
	include_once(SYS_PATH.'/core/cron/captcha.cron.php');
}
