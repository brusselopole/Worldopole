<?php

// -----------------------------------------------------------------------------------------------------------
// Gym datas
// Total gym
// Gym / team
// Average level / team
// -----------------------------------------------------------------------------------------------------------


$gym['timestamp']	= $timestamp;

$req		= "SELECT COUNT(DISTINCT(gym_id)) AS total FROM gym";
$result		= $mysqli->query($req);
$data		= $result->fetch_object();

$gym['total'] 	= $data->total;


// Mystic

$req		= "SELECT COUNT(DISTINCT(gym_id)) AS total, (SELECT COUNT(DISTINCT pokemon_uid) FROM gymmember AS gm JOIN gym ON gm.gym_id=gym.gym_id WHERE team_id = '1') AS members FROM gym WHERE team_id = '1'";
$result		= $mysqli->query($req);
$data		= $result->fetch_object();

$gym['team']['mystic']['gym_owned']	= $data->total;
$gym['team']['mystic']['average']	= round($data->members / $data->total);


// Valor

$req		= "SELECT COUNT(DISTINCT(gym_id)) AS total, (SELECT COUNT(DISTINCT pokemon_uid) FROM gymmember AS gm JOIN gym ON gm.gym_id=gym.gym_id WHERE team_id = '2') AS members FROM gym WHERE team_id = '2'";
$result		= $mysqli->query($req);
$data		= $result->fetch_object();

$gym['team']['valor']['gym_owned']	= $data->total;
$gym['team']['valor']['average']	= round($data->members / $data->total);


// Instinct

$req		= "SELECT COUNT(DISTINCT(gym_id)) AS total, (SELECT COUNT(DISTINCT pokemon_uid) FROM gymmember AS gm JOIN gym ON gm.gym_id=gym.gym_id WHERE team_id = '3') AS members FROM gym WHERE team_id = '3'";
$result		= $mysqli->query($req);
$data		= $result->fetch_object();

$gym['team']['instinct']['gym_owned'] 	= $data->total;
$gym['team']['instinct']['average'] 	= round($data->members / $data->total);


// Add the datas in file
$gymsdatas[]		= $gym;
file_put_contents($gym_file, json_encode($gymsdatas));
