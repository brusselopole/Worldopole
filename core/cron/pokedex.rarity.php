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

// get pokemon from last 7 days to calculate the real rarity for the current week
$req = "SELECT pokemon_id, COUNT(*) as spawns_last_week FROM pokemon WHERE disappear_time >= UTC_TIMESTAMP() - INTERVAL 7 DAY GROUP BY pokemon_id ORDER BY pokemon_id ASC";
$result = $mysqli->query($req);
$total_pokemon_last_week = 0;
while ($data = $result->fetch_object()) {
	$total_pokemon_last_week += $data->spawns_last_week;
	// do not overwrite pokemon count with 0 (pokemon was seen in alltime query above maybe)
	if ($data->spawns_last_week > 0) {
		$pokelist[$data->pokemon_id]['total'] = $data->spawns_last_week;
	}
}

foreach ($pokelist as &$pokemon) {
	// Use alltime count if there was no scan last 7 days
	$total_pokemon          = ($total_pokemon_last_week > 0) ? $total_pokemon_last_week : $total_pokemon_alltime;

	$percent                = ($pokemon['total']*100) / $total_pokemon;
	$rounded                = round($percent, 4);
	// do not round to 0 if there was a spawn. Set to min 0.0001.
	$rounded                = ($rounded == 0.0000 && $pokemon['total'] > 0) ? $rounded = 0.0001 : $rounded;
	$pokemon['rate']        = $rounded;
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
