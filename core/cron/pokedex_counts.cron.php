<?php

// get alltime pokemon counts for pokedex and pokemon pages
$maxpid = $config->system->max_pokemon
for ( $pid = 1; $pid <= $maxpid; $pid++ ) {
	
	$req = "SELECT COUNT(*) as pokemon_total FROM pokemon WHERE pokemon_id = '".$pid."'";
	$result = $mysqli->query($req);
	$data = $result->fetch_object();
	
	$total_spawns['pid'] = $pid;
	$total_spawns['c'] = $data->pokemon_total;
	
	$pokedex_counts[] = $total_spawns;	
	
}

// Write to file
file_put_contents($pokedex_counts_file, json_encode($pokedex_counts));
