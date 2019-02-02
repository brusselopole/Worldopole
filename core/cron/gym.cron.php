<?php

// -----------------------------------------------------------------------------------------------------------
// Gym datas
// Total gym
// Gym / team
// Average level / team
// -----------------------------------------------------------------------------------------------------------


$gym['timestamp'] = $timestamp;

$gym['total'] = $manager->getTotalGyms();


// Mystic

$data = $manager->getOwnedAndPoints(1);
$gym['team']['mystic']['gym_owned'] = $data->total;
$gym['team']['mystic']['average'] = $data->average_points;


// Valor

$data = $manager->getOwnedAndPoints(2);
$gym['team']['valor']['gym_owned'] = $data->total;
$gym['team']['valor']['average'] = $data->average_points;


// Instinct

$data = $manager->getOwnedAndPoints(3);
$gym['team']['instinct']['gym_owned'] = $data->total;
$gym['team']['instinct']['average'] = $data->average_points;


// Add the datas in file
$gymsdatas[] = $gym;
file_put_contents($gym_file, json_encode($gymsdatas));
