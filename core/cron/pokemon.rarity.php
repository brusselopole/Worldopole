<?php


// This file is loaded once every 24h by data the loader. 
// ------------------------------------------------------

$req 		= "SELECT pokemon_id, COUNT(1) as total FROM pokemon GROUP BY pokemon_id ORDER BY pokemon_id ASC";
$result 	= $mysqli->query($req);

$total_pokemons = 0; 

while($data = $result->fetch_object()){
	
	$pokemon_id 	= $data->pokemon_id;
	$total_pokemons = $total_pokemons + $data->total;
	
	$pokelist[$pokemon_id]['id'] 		= $pokemon_id;
	$pokelist[$pokemon_id]['total'] 	= $data->total;

}


foreach($pokelist as $pokemon){
	
	$key = $pokemon['id'];
	
	$pourcent 			= ($pokemon['total']*100) / $total_pokemons; 
	$arrondis			= round($pourcent , 4); 
	$pokelist[$key]['rate'] 	= $arrondis; 
	
}

// create new array if file doesn't exist
// else use file content
if (!is_file($pokedex_rarity_file)) {
	$pokemons_rarity 	= new stdClass();
} else {
	$pokemons_rarity 	= json_decode($pokedex_rarity_file_content);
}
// use pokedex.json file for loop
// because pokemon.rarity.json might not exist yet
foreach($pokemons->pokemon as $pokemon_id => $notUsed){
	
	if(isset($pokelist[$pokemon_id])){
		$pokemons_rarity->$pokemon_id 	= $pokelist[$pokemon_id]['rate']; 
	}
	else{
		$pokemons_rarity->$pokemon_id 	= 0.0000;
	}
	
	
}

$file_content = json_encode($pokemons_rarity);
unset($pokemons_rarity);
file_put_contents($pokedex_rarity_file, $file_content);
	
?>
