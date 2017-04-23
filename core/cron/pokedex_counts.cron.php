<?php

// get alltime pokemon counts for pokedex and pokemon pages

$maxpid = $config->system->max_pokemon;
$total_pokemon = 0;
$pokedex_counts = new stdClass();
for ($pid = 1; $pid <= $maxpid; $pid++) {
	$req = "SELECT COUNT(*) AS pokemon_spawns FROM pokemon WHERE pokemon_id = '".$pid."'";
	$result = $mysqli->query($req);
	$data = $result->fetch_object();
	
	$pokedex_counts->$pid = (int) $data->pokemon_spawns;
	$total_pokemon += $data->pokemon_spawns;
}

// add total count
$pokedex_counts->total = (int) $total_pokemon;

// Write to file
file_put_contents($pokedex_counts_file, json_encode($pokedex_counts));
