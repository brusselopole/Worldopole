<?php
	
	
// -----------------------------------------------------------------------------------------------------------
// Pokestops datas 
// Total pokestops
// Total lured
// -----------------------------------------------------------------------------------------------------------

$pokestop['timestamp'] = $timestamp;

$pokestop['total'] = $manager->getTotalPokestops()->total;

$pokestop['lured'] = $manager->getTotalLures()->total;


// Add the datas in file
$stopdatas[] = $pokestop;
file_put_contents($pokestop_file, json_encode($stopdatas));
