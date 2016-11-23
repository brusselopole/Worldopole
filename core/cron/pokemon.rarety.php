<?php


// This file is loaded once every 24h by data the loader. 
// ------------------------------------------------------


$pokemons_new	= json_decode($pokedex_file_content);

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


foreach($pokemons_new->pokemon as $pokemon_id => $pokemon_data){
	
	if(isset($pokelist[$pokemon_id])){
		$pokemon_data->spawn_rate 	= $pokelist[$pokemon_id]['rate']; 
	}
	else{
		$pokemon_data->spawn_rate 	= 0.0000;
	}
	
	
}

$file_content = json_encode($pokemons_new, JSON_PRETTY_PRINT);
unset($pokemons_new);
file_put_contents($pokedex_file, $file_content);
	
?>
