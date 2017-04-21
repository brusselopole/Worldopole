<?php

// get alltime pokemon count to set rarity at least to seen
$req = "SELECT pokemon_id, COUNT(*) as spawns_total FROM pokemon GROUP BY pokemon_id ORDER BY pokemon_id ASC";
$result = $mysqli->query($req);
$total_pokemon_alltime = 0;
while ($data = $result->fetch_object()) {
	$total_pokemon_alltime += $data->spawns_total;
	$pokemon_id = $data->pokemon_id;
	if ($data->spawns_total > 0) {
		// pokemon seen --> set rarity to at least mythic
		$pokelist[$pokemon_id]['total'] = $data->spawns_total;
	} else {
		// pokemon unseen
		$pokelist[$pokemon_id]['total'] = 0;
	}
}

// get pokemon from last day to calculate the real rarity for last 24h
$req = "SELECT pokemon_id, COUNT(*) as spawns_last_day FROM pokemon WHERE disappear_time >= UTC_TIMESTAMP() - INTERVAL 1 DAY GROUP BY pokemon_id ORDER BY pokemon_id ASC";
$result = $mysqli->query($req);
$total_pokemon_last_day = 0;
while ($data = $result->fetch_object()) {
	$total_pokemon_last_day += $data->spawns_last_day;
	// do not overwrite pokemon count with 0 (pokemon was seen in alltime query above maybe)
	if ($data->spawns_last_day > 0) {
		$pokelist[$data->pokemon_id]['total'] = $data->spawns_last_day;
	}
}

foreach ($pokelist as &$pokemon) {
	// Use alltime count if there was no spawn last 24h
	$total_pokemon          = ($total_pokemon_last_day > 0) ? $total_pokemon_last_day : $total_pokemon_alltime;

	$percent                = ($pokemon['total']*100) / $total_pokemon;
	$pokemon['rate']        = $percent;
}

// use pokedex.json file for loop because we want to have entries for all pokemon
$pokemons_rarity = new stdClass();
foreach ($pokemons->pokemon as $pokemon_id => $notUsed) {
	if (isset($pokelist[$pokemon_id])) {
		$pokemons_rarity->$pokemon_id = $pokelist[$pokemon_id]['rate'];
	} else {
		$pokemons_rarity->$pokemon_id = 0.0000;
	}
}

// Write to file
file_put_contents($pokedex_rarity_file, json_encode($pokemons_rarity));
