<?php


// This file is loaded once every 24h by data.loader.php.
// -----------------------------------------------------

// get alltime pokemon count to set rarity at least to seen
$req = "SELECT pokemon_id, COUNT(*) as total FROM pokemon GROUP BY pokemon_id ORDER BY pokemon_id ASC";
$result = $mysqli->query($req);
while ($data = $result->fetch_object()) {
	$pokemon_id = $data->pokemon_id;
	$pokelist[$pokemon_id]['id'] = $pokemon_id;
	if ($data->total > 0) {
		// pokemon seen --> set rarity to at least mythic
		$pokelist[$pokemon_id]['total'] = 1;
	} else {
		// pokemon unseen
		$pokelist[$pokemon_id]['total'] = 0;
	}
}

// get pokemon from last 7 days to calculate the real rarity for the current week
$req = "SELECT pokemon_id, COUNT(*) as spawns_last_week FROM pokemon WHERE disappear_time >= UTC_TIMESTAMP() - INTERVAL 7 DAY GROUP BY pokemon_id ORDER BY pokemon_id ASC";
$result = $mysqli->query($req);
$total_pokemons = 0;
while ($data = $result->fetch_object()) {
	$total_pokemons += $data->spawns_last_week;
	// do not overwrite pokemon count with 0 (pokemon was seen in alltime query above maybe)
	if ($data->spawns_last_week > 0) {
		$pokelist[$data->pokemon_id]['total'] = $data->spawns_last_week;
	}
}

foreach ($pokelist as $pokemon) {
	$key = $pokemon['id'];
	
	$percent 			= ($pokemon['total']*100) / $total_pokemons;
	$rounded			= round($percent, 4);
	$pokelist[$key]['rate'] 	= $rounded;
}

// create new array if file doesn't exist
// else use file content
if (!is_file($pokedex_rarity_file)) {
	$pokemons_rarity 	= new stdClass();
} else {
	$pokemons_rarity 	= json_decode($pokedex_rarity_file_content);
}
// use pokedex.json file for loop
// because pokedex.rarity.json might not exist yet
foreach ($pokemons->pokemon as $pokemon_id => $notUsed) {
	if (isset($pokelist[$pokemon_id])) {
		$pokemons_rarity->$pokemon_id 	= $pokelist[$pokemon_id]['rate'];
	} else {
		$pokemons_rarity->$pokemon_id 	= 0.0000;
	}
}

$file_content = json_encode($pokemons_rarity);
unset($pokemons_rarity);
file_put_contents($pokedex_rarity_file, $file_content);
