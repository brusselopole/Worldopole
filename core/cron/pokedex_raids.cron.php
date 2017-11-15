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

    $req = "SELECT Count(*) as count, UNIX_TIMESTAMP(MAX(start)) as start_timestamp, gym_id, end, (CONVERT_TZ(end, '+00:00', '".$time_offset."')) AS end_time_real
                FROM raid
                WHERE pokemon_id = '".$pid."' && UNIX_TIMESTAMP(start) > '".$last_update."'";
    $result = $mysqli->query($req);
    $data = $result->fetch_object();

    $count = $data->count;
    if ($count != 0) {
        $gym_id = $data->gym_id;
        $newraiddatas[$pid]['count'] += $count;
        $newraiddatas[$pid]['last_update'] = $data->start_timestamp;
        $newraiddatas[$pid]['end_time'] = $data->end_time_real;

        // Get data of latest gym
        $req = "SELECT latitude, longitude
                FROM gym
                WHERE gym_id = '".$gym_id."'";
        $result = $mysqli->query($req);
        $data = $result->fetch_object();
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
