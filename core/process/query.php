<?php

$variables = realpath(dirname(__FILE__)) . '/../json/variables.json';
$config = json_decode(file_get_contents($variables));

include_once('queries/' . ($config->system->db_type ?: 'rocketmap') . '.php');