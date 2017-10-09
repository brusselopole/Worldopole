<?php
	
// -----------------------------------------------------------------------------------------------------------
// Nests datas 
// 
// 
// -----------------------------------------------------------------------------------------------------------


$result = $mysqli->query(req_map_data());

while ($data = $result->fetch_object()) {
	$nests['pid'] = $data->pokemon_id;
	$nests['c'] = $data->total_pokemon;
	$nests['lat'] = $data->latitude;
	$nests['lng'] = $data->longitude;
    $starttime = $data->latest_seen - $data->duration;
	if ($starttime < 0) {
		$starttime = 3600 + $starttime;
	}
	$nests['st'] = sprintf('%02d', floor($starttime / 60));
	$nests['et'] = sprintf('%02d', floor($data->latest_seen / 60));

	// Add the data to array
	$nestsdatas[] = $nests;
}

// Write file
file_put_contents($nests_file, json_encode($nestsdatas));
