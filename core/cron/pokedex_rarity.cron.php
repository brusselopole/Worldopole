<?php

// get pokemon from last day to calculate the rarity for last 24h
// use last disappear_time as a starting point to overcome scan downtimes
$req = "SELECT pokemon_id, COUNT(*) AS spawns_last_day
		FROM pokemon
		WHERE disappear_time >= (SELECT MAX(disappear_time) FROM pokemon) - INTERVAL 1 DAY
		GROUP BY pokemon_id
		ORDER BY pokemon_id ASC";
$result = $mysqli->query($req);
$total_pokemon_last_day = 0;
while ($data = $result->fetch_object()) {
	$total_pokemon_last_day += $data->spawns_last_day;
	$pokelist[$data->pokemon_id]['spawns_last_day'] = $data->spawns_last_day;
}

// calc rarity for last 24h
foreach ($pokelist as &$pokemon) {
	$percent = $pokemon['spawns_last_day'] * 100 / $total_pokemon_last_day;
	$pokemon['rate'] = round($percent, 4);
}

// set values for all available pokemon
$maxpid = $config->system->max_pokemon;
$pokedex_rarity = new stdClass();
for ($pid = 1; $pid <= $maxpid; $pid++) {
	$pokedex_rarity->$pid = new stdClass();
	if (isset($pokelist[$pid])) {
		// seen
		$pokedex_rarity->$pid->rate = $pokelist[$pid]['rate'];
		$pokedex_rarity->$pid->per_day = (int) $pokelist[$pid]['spawns_last_day'];
	} else {
		// unseen
		$pokedex_rarity->$pid->rate = 0.0;
		$pokedex_rarity->$pid->per_day = 0;
	}
}

// Write to file
file_put_contents($pokedex_rarity_file, json_encode($pokedex_rarity));
