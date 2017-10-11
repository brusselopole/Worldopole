<?php
	
	
// -----------------------------------------------------------------------------------------------------------
// Pokestops datas 
// Total pokestops
// Total lured
// -----------------------------------------------------------------------------------------------------------

// Load Queries
// #############

include_once('query.php');

$pokestop['timestamp'] = $timestamp;

$result = $mysqli->query(req_pokestop_count());
$data = $result->fetch_object();

$pokestop['total'] = $data->total;

$result = $mysqli->query(req_pokestop_lure_count());
$data = $result->fetch_object();

$pokestop['lured'] = $data->total;



// Add the datas in file
$stopdatas[] = $pokestop;
file_put_contents($pokestop_file, json_encode($stopdatas));
