<?php
	
// -----------------------------------------------------------------------------------------------------------
// Nests datas 
// 
// 
// -----------------------------------------------------------------------------------------------------------


$pokemon_exclude_sql = "";
if (!empty($config->system->nest_exclude_pokemon)) {
	$pokemon_exclude_sql = "AND pokemon_id NOT IN (".implode(",", $config->system->nest_exclude_pokemon).")";
}

$req = "SELECT spawnpoint_id, pokemon_id, max(latitude) AS latitude, max(longitude) AS longitude, count(pokemon_id) AS total_pokemon
        FROM pokemon
        WHERE disappear_time > (UTC_TIMESTAMP() - INTERVAL 24 HOUR)
        ".$pokemon_exclude_sql."
        GROUP BY spawnpoint_id, pokemon_id
        HAVING count(pokemon_id) >= 6
        ORDER BY pokemon_id";
$result = $mysqli->query($req);

while ($data = $result->fetch_object()) {
	$nests['pid'] = $data->pokemon_id;
	$nests['c'] = $data->total_pokemon;
	$nests['lat'] = $data->latitude;
	$nests['lng'] = $data->longitude;
	// $starttime = $data->latest_seen - substr_count($data->kind, "s") * 900;
	// if ($starttime < 0) {
	// 	$starttime = 3600 + $starttime;
	// }
	// $nests['st'] = sprintf('%02d', floor($starttime / 60));
	// $nests['et'] = sprintf('%02d', floor($data->latest_seen / 60));
	$nests['st'] = '00';
	$nests['et'] = '00';

	// Add the data to array
	$nestsdatas[] = $nests;
}

// Write file
file_put_contents($nests_file, json_encode($nestsdatas));
