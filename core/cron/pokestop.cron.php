<?php
	
	
// -----------------------------------------------------------------------------------------------------------
// Pokestops datas 
// Total pokestops
// Total lured
// -----------------------------------------------------------------------------------------------------------

$pokestop['timestamp'] = $timestamp;

$req 		= "SELECT COUNT(*) as total FROM pokestop";
$result 	= $mysqli->query($req);
$data 		= $result->fetch_object();

$pokestop['total'] = $data->total;

$req 		= "SELECT COUNT(*) as total FROM pokestop WHERE lure_expiration >= UTC_TIMESTAMP()";
$result 	= $mysqli->query($req);
$data 		= $result->fetch_object();

$pokestop['lured'] = $data->total;



// Add the datas in file
$stopdatas[] 	= $pokestop;
file_put_contents($pokestop_file, json_encode($stopdatas));
