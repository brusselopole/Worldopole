<?php

// Manage Time Interval
// #####################

// Include and load variables if not set
if (!isset($config)) {
	$variables      = realpath(dirname(__FILE__)).'/../json/variables.json';
	$config         = json_decode(file_get_contents($variables));
}

// Set default timezone
date_default_timezone_set($config->system->timezone);

// Get symbol and delay
$time_interval = strlen($config->system->time_interval);

if ($time_interval > 3) {
	echo 'Bad formated time_interval in variables.json. Please use +X or -X format only (eg for Brussels : +2) without leading or ending space.';
	exit();
}

$time 		= new stdClass();
$time->symbol	= substr($config->system->time_interval, 0, 1);
$time->delay	= str_replace($time->symbol, '', $config->system->time_interval);

if ($time->symbol == '+') {
	$time->symbol_reverse = '-';
} elseif ($time->symbol == '-') {
	$time->symbol_reverse = '+';
} else {
	echo 'Bad formated time_interval in variables.json. Please use +X or -X format only (eg for Brussels : +2) without leading or ending space.';
	exit();
}
