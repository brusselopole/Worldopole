<?php

// get alltime pokemon counts for pokedex and pokemon pages
for ( $pid = 1; $pid <= 251; $pid++ ) {
	
	$req = "SELECT COUNT(*) as pokemon_total FROM pokemon_web WHERE pokemon_id = '".$pid."'";
	$result = $mysqli->query($req);
	$data = $result->fetch_object();
	
	$total_spawns['pid'] = $pid;
	$total_spawns['total'] = $data->pokemon_total;
	
	$pokedex_counts[] = $total_spawns;	
	
}

// Write to file
file_put_contents($pokedex_counts_file, json_encode($pokedex_counts));