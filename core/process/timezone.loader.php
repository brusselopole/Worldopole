<?php

// Manage Time Interval
// #####################

// Include and load variables if not set
if (!isset($config)) {
    $variables = realpath(dirname(__FILE__)).'/../json/variables.json';
    $config = json_decode(file_get_contents($variables));
}

// Set default timezone
date_default_timezone_set($config->system->timezone);

// Get time offset to UTC in '+00:00' format
$time_offset = date('P');
