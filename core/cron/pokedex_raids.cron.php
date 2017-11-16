<?php

// get raid counts since last update for pokemon page

$maxpid = $config->system->max_pokemon;
$newraiddatas = $raiddatas;

for ($pid = 1; $pid <= $maxpid; $pid++) {
	// Get count since update
	if (isset($raiddatas[$pid]['last_update'])) {
		$last_update = $raiddatas[$pid]['last_update'];
	} else {
		$last_update = 0;
	}

	$where = "WHERE pokemon_id = '".$pid."' && UNIX_TIMESTAMP(start) > '".$last_update."'";
	$req = "SELECT UNIX_TIMESTAMP(start) as start_timestamp, end, (CONVERT_TZ(end, '+00:00', '".$time_offset."')) AS end_time_real, latitude, longitude, count
                FROM raid r
                JOIN gym g
                JOIN (SELECT count(*) as count
                    FROM raid
                    " . $where."
                ) x
                ON r.gym_id = g.gym_id
                " . $where."
                ORDER BY start DESC
                LIMIT 0,1";
	$result = $mysqli->query($req);
	$data = $result->fetch_object();

	if (isset($data)) {
		$count = $data->count;
	} else {
		$count = 0;
	}

	if ($count != 0) {
		$newraiddatas[$pid]['count'] += $count;
		$newraiddatas[$pid]['last_update'] = $data->start_timestamp;
		$newraiddatas[$pid]['end_time'] = $data->end_time_real;
		$newraiddatas[$pid]['latitude'] = $data->latitude;
		$newraiddatas[$pid]['longitude'] = $data->longitude;
	} elseif (is_null($newraiddatas[$pid]['count'])) {
		$newraiddatas[$pid]['count'] = 0;
		$newraiddatas[$pid]['last_update'] = 0;
		$newraiddatas[$pid]['end_time'] = null;
		$newraiddatas[$pid]['latitude'] = null;
		$newraiddatas[$pid]['longitude'] = null;
	}
}

// Write to file
file_put_contents($pokedex_raids_file, json_encode($newraiddatas));
