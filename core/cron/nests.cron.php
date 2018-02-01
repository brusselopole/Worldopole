<?php
	
// -----------------------------------------------------------------------------------------------------------
// Nests datas 
// 
// 
// -----------------------------------------------------------------------------------------------------------


$datas = $manager->getNestData();

$nestsdatas = array();
foreach ($datas as $data) {
	$nests['pid'] = $data->pokemon_id;
	$nests['c'] = $data->total_pokemon;
	$nests['lat'] = $data->latitude;
	$nests['lng'] = $data->longitude;
	$starttime = $data->latest_seen - $data->duration;

	$nests['st'] = date("i",$starttime);
	$nests['et'] = date("i",$data->latest_seen);

	// Add the data to array
	$nestsdatas[] = $nests;
}

// Write file
file_put_contents($nests_file, json_encode($nestsdatas));
