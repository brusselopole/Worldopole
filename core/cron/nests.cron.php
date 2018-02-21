<?php

// -----------------------------------------------------------------------------------------------------------
// Nests datas 
// 
// 
// -----------------------------------------------------------------------------------------------------------

// Get Parks from overpass-turbo
$bounds = $manager->getMapsCoords();
$req = '[timeout:600][out:json][date:"2016-07-17T00:00:00Z"][bbox:'.$bounds->min_latitude.','.$bounds->min_longitude.','.$bounds->max_latitude.','.$bounds->max_longitude.'];
		(
			way["leisure"="park"];
			way["landuse"="garden"];
			way["leisure"="golf_course"];
			way["boundary"="physiogeographical"];

			way["boundary"="national_park"];
			way["recreation_ground"];
			way["leisure"="playground"];
			way["leisure"="pitch"];

			way["landuse"="grass"];
			way["landuse"="meadow"];
			way["natural"="heath"];
			way["natural"="moor"];
			way["natural"="scrub"];

			way["landuse"="farmland"];
			way["landuse"="greenfield"];
			way["landuse"="recreation_ground"];
			way["landuse"="farmyard"];
			way["landuse"="vineyard"];
			
			rel["leisure"="park"];
			rel["landuse"="garden"];
			rel["leisure"="golf_course"];
			rel["boundary"="physiogeographical"];

			rel["boundary"="national_park"];
			rel["recreation_ground"];
			rel["leisure"="playground"];
			rel["leisure"="pitch"];

			rel["landuse"="grass"];
			rel["landuse"="meadow"];
			rel["natural"="heath"];
			rel["natural"="moor"];
			rel["natural"="scrub"];

			rel["landuse"="farmland"];
			rel["landuse"="greenfield"];
			rel["landuse"="recreation_ground"];
			rel["landuse"="farmyard"];
			rel["landuse"="vineyard"];
		);
		out meta geom;
		>;
		out skel qt;';
$endpoint = "http://overpass-api.de/api/interpreter";

$curl = curl_init($endpoint);

curl_setopt($curl, CURLOPT_POST, 1);
curl_setopt($curl, CURLOPT_POSTFIELDS, "data=".urlencode($req));
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($curl);
$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
curl_close($curl);

if ($status == 200) {
	$json =  json_decode($response, true);

	$parks = array();
	foreach ( $json["elements"] as $key => &$element) {
		$tempGeo = null;
		if (isset($element["type"]) && $element["type"] == "way" && isset($element["geometry"])) {
			$tempGeo = $element["geometry"];
		} else if (isset($element["type"]) && $element["type"] == "relation" && isset($element["members"]) ) {
			$outers = array();
			$members = $element["members"];
			foreach ($members as $member) {
				if (isset($member["role"]) && $member["role"] == "outer" && isset($member["geometry"])) {
					$outers[] = $member["geometry"];
				}
			}
			$tempGeo = combineOuter($outers);
		}
		if (!is_null($tempGeo)) {
			$data = array();
			$geo = array();
			foreach ($tempGeo as $ele) {
				$geo[] = array("lat" => $ele["lat"], "lng" => $ele["lon"]);
			}

			// Finish poly where we started
			$firstEle = $geo[0];
			$lastEle = $geo[count($geo) - 1];
			if ($firstEle !=  $lastEle) {
				$geo[] = $firstEle;
			}

			$data["geo"] = $geo;
			if (isset($element["tags"]) && isset($element["tags"]["name"])) {
				$data["name"] = $element["tags"]["name"];
			} else {
				$data["name"] = null;
			}
			$parks[] = $data;
		}
		unset($json[$key]);
	}

	// Get frequent spawn points
	$datas = $manager->getNestData($nestTime);
	$nestsdatas = array();
	foreach ($datas as $key => &$data) {
		$nests['pid'] = $data->pokemon_id;
		$nests['c'] = $data->total_pokemon;
		$nests['lat'] = $data->latitude;
		$nests['lng'] = $data->longitude;
		$starttime = $data->latest_seen - $data->duration;

		$nests['st'] = date("i",$starttime);
		$nests['et'] = date("i",$data->latest_seen);

		// Add the data to array
		$nestsdatas[] = $nests;
		unset($datas[$key]);
	}

	// Checking Parks for Spawnpoints. This will take a while
	$nestParks = array();
	foreach ($parks as $key => &$park) {
		$spawns = array();

		foreach ($nestsdatas as $spawnpoint) {
			$lat = $spawnpoint["lat"];
			$lng = $spawnpoint["lng"];
			$pid = $spawnpoint['pid'];

			if (pointIsInsidePolygon($lat, $lng, $park["geo"])) {
				if (!isset($spawns[$pid])) {
					$spawns[$pid] = 0;
				}
				$spawns[$pid] += 1;
			}

		}

		$mostPid = 0;
		$mostPidCount = 0;
		foreach ($spawns as $pid => $count) {
			if ($count > $mostPidCount) {
				$mostPidCount = $count;
				$mostPid = $pid;
			}
		}
		if ($mostPidCount != 0) {
			$park["pid"] = $mostPid;
			$park["count"] = $mostPidCount;
			$nestParks[] = $park;
		}
		unset($parks[$key]);
	}


	// Todo: Parks befüllen

	// Write files
	file_put_contents($nests_file, json_encode($nestsdatas));
	file_put_contents($nests_parks_file, json_encode($nestParks));
} else {
	echo "Error ".$status." while getting nests from overpass-turbo. Aborting Nest Update.";
	touch($nests_parks_file, $prevNestTime);
}
