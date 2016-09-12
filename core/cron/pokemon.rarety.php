<?php


// This file is loaded once every 24h by data the loader. 
// ------------------------------------------------------


$pokemons	= json_decode($pokemon_file);

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
	
	$pourcent 					= ($pokemon['total']*100) / $total_pokemons; 
	$arrondis					= round($pourcent , 4); 
	$pokelist[$key]['rate'] 	= $arrondis; 
	
	
	// + 1 = Very common
	// + 0.25 = Common 
	// + 0.05 = Rare
	// + 0.0001 = Mythic
	// Unseen 
	
	if($arrondis >= 1){
		
		$pokelist[$key]['status'] = 'Very common'; 
		
	}elseif($arrondis >= 0.25){
		
		$pokelist[$key]['status'] = 'Common';
		
	}elseif($arrondis >= 0.05){
		
		$pokelist[$key]['status'] = 'Rare';
		
	}elseif($arrondis >= 0.0001){
		
		$pokelist[$key]['status'] = 'Mythic';
		
	}else{
		
		$pokelist[$key]['status'] = 'Unseen';
		
	}
	
}


foreach($pokemons as $pokemon_id => $pokemon_data){
	
	if(isset($pokelist[$pokemon_id])){
		
		$pokemon_data->rarity 		= $pokelist[$pokemon_id]['status'];
		$pokemon_data->spawn_rate 	= $pokelist[$pokemon_id]['rate']; 
		
	}
	else{
		$pokemon_data->rarity 		= 'Unseen';
		$pokemon_data->spawn_rate 	= 0.0000;
		
	}
	
	
}

$file_content = json_encode($pokemons); 

file_put_contents($pokedex, $file_content);

	
?>