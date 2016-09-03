<?php
	
// -----------------------------------------------------------------------------------------------------------
// Pokemons datas 
// Total pokemon available
// -----------------------------------------------------------------------------------------------------------


// We're using the EN version as far as we know, it's the only valable version of Pokelist for now. 
// This file is used to rank by rarety 

$pokemon_list_file 	= file_get_contents(SYS_PATH.'/core/json/pokelist_EN.json'); 
$pokemons			= json_decode($pokemon_list_file);


$pokemon_stats['timestamp'] =  $timestamp; 


$req 		= "SELECT COUNT(*) as total FROM pokemon WHERE disappear_time > (NOW() - INTERVAL 2 HOUR);";	
$result 	= $mysqli->query($req);
$data 		= $result->fetch_object();

$pokemon_stats['pokemon_now'] 	= $data->total;

$req 		= "SELECT pokemon_id FROM pokemon WHERE disappear_time > (NOW() - INTERVAL 2 HOUR);";
$result 	= $mysqli->query($req);

while($data = $result->fetch_object()){
	
	$poke_id 	= $data->pokemon_id; 
	$rarity 	= $pokemons->$poke_id->rarity;
	
	@$type[$rarity] = $type[$rarity]+1; 
	
	
}

$pokemon_stats['rarity_spawn'] = $type; 



// Add the datas in file

$pokedatas[] 	= $pokemon_stats; 
$json 			= json_encode($pokedatas); 


file_put_contents($pokemon_file, $json);


?>