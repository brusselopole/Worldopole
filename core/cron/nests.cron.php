<?php

// -----------------------------------------------------------------------------------------------------------
// Nests datas
//
//
// -----------------------------------------------------------------------------------------------------------

$bounds = $manager->getMapsCoords();
$totalMaxLat = $bounds->max_latitude + 0.01;
$totalMinLat = $bounds->min_latitude - 0.01;
$totalMaxLon = $bounds->max_longitude + 0.01;
$totalMinLon = $bounds->min_longitude - 0.01;
$countLat = ceil(($totalMaxLat - $totalMinLat) / 0.5);
$countLng = ceil(($totalMaxLon - $totalMinLon) / 0.5);

$allNestSpawns = array();
$allNestParks = array();

for ($iLat = 0; $iLat < $countLat;) {
    for ($iLng = 0; $iLng < $countLng;) {
        $minLatitude = $totalMinLat + $iLat * 0.5;
        $maxLatitude = min($minLatitude + 0.5, $totalMaxLat);
        $minLongitude = $totalMinLon + $iLng * 0.5;
        $maxLongitude = min($minLongitude + 0.5, $totalMaxLon);

        if (0 == $manager->getSpawnpointCount($minLatitude, $maxLatitude, $minLongitude, $maxLongitude)->total) { //Skip empty areas
            ++$iLng;
            continue;
        }

        // Get Parks from overpass
        $req = '[timeout:600][out:json][date:"2017-01-22T00:00:00Z"][bbox:'.$minLatitude.','.$minLongitude.','.$maxLatitude.','.$maxLongitude.'];
				(
					way["leisure"="park"];
					way["leisure"="garden"];
					way["leisure"="golf_course"];
					way["leisure"="recreation_ground"];
					way["leisure"="playground"];
					way["leisure"="pitch"];
					way["leisure"="nature_reserve"];
		
					way["boundary"="physiogeographical"];
					way["boundary"="national_park"];
		
					way["natural"="heath"];
					way["natural"="moor"];
					way["natural"="scrub"];
	
					way["landuse"="grass"];
					way["landuse"="meadow"];
					way["landuse"="farmland"];
					way["landuse"="greenfield"];
					way["landuse"="recreation_ground"];
					way["landuse"="farmyard"];
					way["landuse"="vineyard"];
					way["landuse"="orchard"];
					
					rel["leisure"="park"];
					rel["leisure"="garden"];
					rel["leisure"="golf_course"];
					rel["leisure"="recreation_ground"];
					rel["leisure"="playground"];
					rel["leisure"="pitch"];
					rel["leisure"="nature_reserve"];
					
					rel["boundary"="physiogeographical"];
					rel["boundary"="national_park"];
		
					rel["natural"="heath"];
					rel["natural"="moor"];
					rel["natural"="scrub"];
		
					rel["landuse"="grass"];
					rel["landuse"="meadow"];
					rel["landuse"="farmland"];
					rel["landuse"="greenfield"];
					rel["landuse"="recreation_ground"];
					rel["landuse"="farmyard"];
					rel["landuse"="vineyard"];
					rel["landuse"="orchard"];
				);
				out meta geom;
				>;
				out skel qt;';
        $endpoint = 'https://overpass-api.de/api/interpreter';

        $curl = curl_init($endpoint);

        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, 'data='.urlencode($req));
        curl_setopt($curl, CURLOPT_USERAGENT, 'Worldopole/NestUpdater '.$config->infos->site_name);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 60);
        curl_setopt($curl, CURLOPT_TIMEOUT, 660);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($curl);
        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if (200 == $status) {
            $json = json_decode($response, true);

            $parks = array();
            foreach ($json['elements'] as $key => &$element) {
                $tempGeos = array();
                if (isset($element['type']) && 'way' == $element['type'] && isset($element['geometry'])) {
                    $tempGeos = array($element['geometry']);
                } elseif (isset($element['type']) && 'relation' == $element['type'] && isset($element['members'])) {
                    $outers = array();
                    $members = $element['members'];
                    foreach ($members as $member) {
                        if (isset($member['role']) && 'outer' == $member['role'] && isset($member['geometry'])) {
                            $outers[] = $member['geometry'];
                        }
                    }
                    $tempGeos = combineOuter($outers);
                }
                foreach ($tempGeos as $key => $tempGeo) {
                    if (!is_null($tempGeo) && 0 != count($tempGeo)) {
                        $data = array();
                        $geo = array();
                        foreach ($tempGeo as $ele) {
                            $geo[] = array('lat' => $ele['lat'], 'lng' => $ele['lon']);
                        }

                        // Finish poly where we started
                        $firstEle = $geo[0];
                        $lastEle = $geo[count($geo) - 1];
                        if ($firstEle != $lastEle) {
                            $geo[] = $firstEle;
                        }

                        $data['geo'] = $geo;
                        if (isset($element['tags']) && isset($element['tags']['name'])) {
                            $data['name'] = $element['tags']['name'];
                        } else {
                            $data['name'] = null;
                        }
                        $data['id'] = $element['id'].'#'.$key;
                        $data['bounds'] = $element['bounds'];
                        $parks[] = $data;
                    }
                }
                unset($json[$key]);
            }

            // Get frequent spawn points
            $datas = $manager->getNestData($nestTime, $minLatitude, $maxLatitude, $minLongitude, $maxLongitude);
            $nestsdatas = array();
            foreach ($datas as $key => &$data) {
                $nests['pid'] = $data->pokemon_id;
                $nests['c'] = $data->total_pokemon;
                $nests['lat'] = $data->latitude;
                $nests['lng'] = $data->longitude;
                // $starttime = $data->latest_seen - $data->duration;

                $nests['st'] = '00'; // date('i', $starttime);
                $nests['et'] = '00'; // date('i', $data->latest_seen);

                // Add the data to array
                $nestsdatas[] = $nests;
                $allNestSpawns[] = $nests;
                unset($datas[$key]);
            }

            // Checking Parks for Spawnpoints.
            foreach ($parks as $key => &$park) {
                $spawns = array();

                foreach ($nestsdatas as $spawnpoint) {
                    $lat = $spawnpoint['lat'];
                    $lng = $spawnpoint['lng'];
                    $pid = $spawnpoint['pid'];

                    if (pointIsInsidePolygon($lat, $lng, $park['geo'], $park['bounds'])) {
                        if (!isset($spawns[$pid])) {
                            $spawns[$pid] = 0;
                        }
                        ++$spawns[$pid];
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
                if (0 != $mostPidCount && (!isset($config->system->nest_min_count) || $mostPidCount >= $config->system->nest_min_count)) {
                    $park['pid'] = $mostPid;
                    $park['count'] = $mostPidCount;

                    $parkId = $park['id'];
                    if (!array_key_exists($parkId, $allNestParks)) {
                        $allNestParks[$parkId] = $park;
                        unset($park['id']);
                    } else {
                        $savedPark = $allNestParks[$parkId];
                        if ($park['pid'] == $savedPark['pid']) {
                            $savedPark['count'] += $park['count'];
                        } elseif ($park['count'] > $savedPark['count']) {
                            $savedPark['count'] = $park['count'];
                            $savedPark['pid'] = $park['pid'];
                        }
                        $allNestParks[$parkId] = $savedPark;
                    }
                }
                unset($parks[$key]);
            }
            ++$iLng;
        } elseif (429 == $status) {
            echo "Got Error 429: Trying again in 5 Minutes...\n";
            sleep(300);
        } else {
            echo 'Error '.$status." while getting nests from overpass-turbo. Aborting Nest Update.\n";
            touch($nests_parks_file, $prevNestTime);
            exit();
        }
    }
    ++$iLat;
}

// Check for nests in nests of same pokemon
foreach ($allNestParks as $keyA => $parkA) {
    foreach ($allNestParks as $keyB => $parkB) {
        if ($keyA != $keyB && $parkA['pid'] == $parkB['pid']) {
            if (polyIsInsidePolygon($parkA['geo'], $parkA['bounds'], $parkB['geo'], $parkB['bounds'])) {
                unset($allNestParks[$keyA]);
            } elseif (polyIsInsidePolygon($parkB['geo'], $parkB['bounds'], $parkA['geo'], $parkA['bounds'])) {
                unset($allNestParks[$keyB]);
            }
        }
    }
}

// Write files
file_put_contents($nests_file, json_encode($allNestSpawns));
file_put_contents($nests_parks_file, json_encode(array_values($allNestParks)));
