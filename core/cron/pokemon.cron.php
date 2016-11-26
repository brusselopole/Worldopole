<?php
	
// -----------------------------------------------------------------------------------------------------------
// Pokemons datas 
// Total pokemon available
// -----------------------------------------------------------------------------------------------------------


// This file is used to rank by rarity 

// Load the pokemons array
// will load english one because language is not set
############################

include_once($filePath.'/../process/locales.loader.php');


$pokemon_stats['timestamp'] = $timestamp; 


$req 		= "SELECT COUNT(*) as total FROM pokemon WHERE disappear_time > (NOW() ".$time->symbol_reverse." INTERVAL ".$time->delay." HOUR)";
$result 	= $mysqli->query($req);
$data 		= $result->fetch_object();

$pokemon_stats['pokemon_now'] 	= $data->total;

$req 		= "SELECT pokemon_id FROM pokemon WHERE disappear_time > (NOW() ".$time->symbol_reverse." INTERVAL ".$time->delay." HOUR)";
$result 	= $mysqli->query($req);

$rarityarray = array();
while($data = $result->fetch_object()){
	
	$poke_id 	= $data->pokemon_id; 
	$rarity 	= $pokemons->pokemon->$poke_id->rarity;
	
	isset($rarityarray[$rarity]) ? $rarityarray[$rarity]++ : $rarityarray[$rarity] = 1;

}
$pokemon_stats['rarity_spawn'] = $rarityarray;


// Add the datas in file

$pokedatas[] 	= $pokemon_stats; 
$json 		= json_encode($pokedatas); 

file_put_contents($pokemonstats_file, $json);

?>
