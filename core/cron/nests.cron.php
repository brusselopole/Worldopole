<?php
	
	
// -----------------------------------------------------------------------------------------------------------
// Nests datas 
// 
// 
// -----------------------------------------------------------------------------------------------------------

include_once(SYS_PATH.'/core/process/locales.loader.php');

$req = "SELECT pokemon.pokemon_id, count(pokemon.pokemon_id) as total_pokemon, pokemon.latitude, pokemon.longitude FROM pokemon INNER JOIN spawnpoint ON (spawnpoint.id = pokemon.spawnpoint_id) WHERE pokemon.disappear_time > UTC_TIMESTAMP - INTERVAL 24 HOUR AND pokemon.pokemon_id IN(1, 4, 7, 23, 25, 27, 29, 32, 37, 43, 50, 56, 58, 60, 63, 66, 69, 74, 77, 79, 81, 84, 86, 90, 92, 95, 98, 100, 102, 104, 108, 109, 111, 114, 116, 120, 123, 124, 125, 126, 127, 138, 140, 152, 155, 158, 170, 183, 185, 187, 190, 191, 193, 194, 200, 202, 203, 204, 206, 207, 209, 211, 213, 215, 216, 218, 223, 226, 227, 228, 231, 234, 241) 
GROUP BY pokemon.spawnpoint_id, pokemon.pokemon_id HAVING total_pokemon > 6 ORDER BY pokemon.pokemon_id ";
$result = $mysqli->query($req);

while ($data = $result->fetch_object()) {
	$pokeid = $data->pokemon_id;
	$nests['pokemon_id'] = $data->pokemon_id;
	$nests['total_pokemon'] = $data->total_pokemon;
	$nests['latitude'] = $data->latitude;
	$nests['longitude'] = $data->longitude;

	// Add the datas in file
	$nestsdatas[] = $nests;
	$json = json_encode($nestsdatas);

	file_put_contents(SYS_PATH.'/core/json/nests.stats.json', $json);
}
