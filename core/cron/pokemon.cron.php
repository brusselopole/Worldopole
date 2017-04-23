<?php
	
// -----------------------------------------------------------------------------------------------------------
// Pokemons datas 
// Total pokemon available
// -----------------------------------------------------------------------------------------------------------


// This file is used to rank by rarity 

// Load the pokemons array
// crontabs.include.php forces english lang
include_once(SYS_PATH.'/core/process/locales.loader.php');


$pokemon_stats['timestamp'] = $timestamp;


$req 		= "SELECT COUNT(*) AS total FROM pokemon WHERE disappear_time >= UTC_TIMESTAMP()";
$result 	= $mysqli->query($req);
$data 		= $result->fetch_object();

$pokemon_stats['pokemon_now'] 	= $data->total;

$req 		= "SELECT pokemon_id FROM pokemon WHERE disappear_time >= UTC_TIMESTAMP()";
$result 	= $mysqli->query($req);

$rarityarray = array();
while ($data = $result->fetch_object()) {
	$poke_id 	= $data->pokemon_id;
	$rarity 	= $pokemons->pokemon->$poke_id->rarity;
	
	isset($rarityarray[$rarity]) ? $rarityarray[$rarity]++ : $rarityarray[$rarity] = 1;
}

// Set amount of Pokemon for each rarity to 0 if there weren't any at that time
isset($rarityarray['Very common']) ?: $rarityarray['Very common'] = 0;
isset($rarityarray['Common']) ?: $rarityarray['Common'] = 0;
isset($rarityarray['Rare']) ?: $rarityarray['Rare'] = 0;
isset($rarityarray['Mythic']) ?: $rarityarray['Mythic'] = 0;

$pokemon_stats['rarity_spawn'] = $rarityarray;


// Write to file
$pokedatas[] 	= $pokemon_stats;
file_put_contents($pokemonstats_file, json_encode($pokedatas));
