<?php

// -----------------------------------------------------------------------------------------------------------
// Gym datas
// Total gym
// Gym / team
// Average level / team
// -----------------------------------------------------------------------------------------------------------


$gym['timestamp'] = $timestamp;

$result = $mysqli->query(req_gym_count());
$data = $result->fetch_object();

$gym['total'] = $data->total;


// Mystic

$result = $mysqli->query(req_gym_count_for_team(1));
$data = $result->fetch_object();

$gym['team']['mystic']['gym_owned'] = $data->total;
$gym['team']['mystic']['average'] = $data->average_points;


// Valor

$result = $mysqli->query(req_gym_count_for_team(2));
$data = $result->fetch_object();

$gym['team']['valor']['gym_owned'] = $data->total;
$gym['team']['valor']['average'] = $data->average_points;


// Instinct

$result = $mysqli->query(req_gym_count_for_team(3));
$data = $result->fetch_object();

$gym['team']['instinct']['gym_owned'] = $data->total;
$gym['team']['instinct']['average'] = $data->average_points;


// Add the datas in file
$gymsdatas[] = $gym;
file_put_contents($gym_file, json_encode($gymsdatas));
