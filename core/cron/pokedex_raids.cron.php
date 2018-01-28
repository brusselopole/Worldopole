<?php

// get raid counts since last update for pokemon page

$maxpid = $config->system->max_pokemon;
if (is_null($raiddatas)) {
	$newraiddatas = array();
} else {
	$newraiddatas = $raiddatas;
}

for ($pid = 1; $pid <= $maxpid; $pid++) {
	if (!isset($newraiddatas[$pid])) {
		$emptyArray = array(
			"count" => 0,
			"last_update" => 0,
			"end_time" => null,
			"latitude" => null,
			"longitude" => null
		);
		$newraiddatas[$pid] = $emptyArray;
	}

	$last_update = $newraiddatas[$pid]['last_update'];

	if (!isset($newraiddatas[$pid])) {
		$newraiddatas[$pid] = array();
	}

	$data = $manager->getRaidsSinceLastUpdate($pid, $last_update);

	if (isset($data) && isset($data->count)) {
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
	}
}

// Write to file
file_put_contents($pokedex_raids_file, json_encode($newraiddatas));
