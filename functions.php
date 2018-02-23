<?php

########################################################################
// Human Time Ago
// @param $timestamp	=> timestamp (mandatory)
// @param $locales	=> locales (mandatory)
//
// Return time ago at human format (eg: 2 hours ago)
########################################################################

function time_ago($timestamp, $locales)
{

	// Set up our variables.
	$minute_in_seconds = 60;
	$hour_in_seconds   = $minute_in_seconds * 60;
	$day_in_seconds	   = $hour_in_seconds * 24;
	$week_in_seconds   = $day_in_seconds * 7;
	$month_in_seconds  = $day_in_seconds * 30;
	$year_in_seconds   = $day_in_seconds * 365;

	// current time
	$now = time();

	// Calculate the time difference between the current time reference point and the timestamp we're comparing.
	// The difference is defined negative, when in the future.
	$time_difference = $now - $timestamp;

	// Calculate the time ago using the smallest applicable unit.
	if ($time_difference < $hour_in_seconds) {
		$difference_value = abs(round($time_difference / $minute_in_seconds));
		$difference_label = 'MINUTE';
	} elseif ($time_difference < $day_in_seconds) {
		$difference_value = abs(round($time_difference / $hour_in_seconds));
		$difference_label = 'HOUR';
	} elseif ($time_difference < $week_in_seconds) {
		$difference_value = abs(round($time_difference / $day_in_seconds));
		$difference_label = 'DAY';
	} elseif ($time_difference < $month_in_seconds) {
		$difference_value = abs(round($time_difference / $week_in_seconds));
		$difference_label = 'WEEK';
	} elseif ($time_difference < $year_in_seconds) {
		$difference_value = abs(round($time_difference / $month_in_seconds));
		$difference_label = 'MONTH';
	} else {
		$difference_value = abs(round($time_difference / $year_in_seconds));
		$difference_label = 'YEAR';
	}

	// plural
	if ($difference_value != 1) {
		$difference_label = $difference_label.'S';
	}

	if ($time_difference <= 0) {
		// Present
		return sprintf($locales->TIME_LEFT, $difference_value.' '.$locales->$difference_label);
	} else {
		// Past
		return sprintf($locales->TIME_AGO, $difference_value.' '.$locales->$difference_label);
	}
}


########################################################################
// Percent calculator
// @param $val		=> int (mandatory)
// @param $val_total	=> int (mandatory)
//
// Return pourcent from total
########################################################################

function percent($val, $val_total)
{
	$count1 = $val_total / $val;
	$count2 = $count1 * 100;

	$count = number_format($count2, 0);

	return $count;
}


########################################################################
// File version (unix timestamp)
// @param $url		=> string (mandatory)
//
// Return $url with last_modified unix timestamp before suffix
########################################################################

function auto_ver($url)
{
	if (is_file(SYS_PATH.'/'.$url)) {
		$path = pathinfo($url);
		$ver = '.'.filemtime(SYS_PATH.'/'.$url).'.';
		echo $path['dirname'].'/'.preg_replace('/\.(css|js|json)$/', $ver."$1", $path['basename']);
	} else {
		echo $url;
	}
}


########################################################################
// File age in secs
// @param $filepath     => string (mandatory)
//
// Return file age of file in secs, PHP_INT_MAX if file doesn't exist
########################################################################

function file_update_ago($filepath)
{
	if (is_file($filepath)) {
		$filemtime = filemtime($filepath);
		$now = time();
		$diff = $now - $filemtime;
		return $diff;
	}
	// file doesn't exist yet!
	return PHP_INT_MAX;
}


########################################################################
// Only keep data after $timestamp in $array (compared to 'timestamp' key)
// @param $array     => array (mandatory)
// @param $timestamp => int (mandatory)
//
// Return trimmed array
########################################################################

function trim_stats_json($array, $timestamp)
{
	foreach ($array as $key => $value) {
		if ($value['timestamp'] < $timestamp) {
			unset($array[$key]);
		}
	}
	return $array;
}


########################################################################
// gym level from prestige value
// @param $prestige => int (mandatory)
//
// Return gym level
########################################################################

function gym_level($prestige)
{
	if ($prestige == 0) {
		$gym_level = 0;
	} elseif ($prestige < 2000) {
		$gym_level = 1;
	} elseif ($prestige < 4000) {
		$gym_level = 2;
	} elseif ($prestige < 8000) {
		$gym_level = 3;
	} elseif ($prestige < 12000) {
		$gym_level = 4;
	} elseif ($prestige < 16000) {
		$gym_level = 5;
	} elseif ($prestige < 20000) {
		$gym_level = 6;
	} elseif ($prestige < 30000) {
		$gym_level = 7;
	} elseif ($prestige < 40000) {
		$gym_level = 8;
	} elseif ($prestige < 50000) {
		$gym_level = 9;
	} else {
		$gym_level = 10;
	}

	return $gym_level;
}

########################################################################
// depth of array
// @param $arr     => array (mandatory)
//
// Retruns max depth of array
########################################################################
function get_depth($arr) {
	$it = new RecursiveIteratorIterator(new RecursiveArrayIterator($arr));
	$depth = 0;
	foreach ($it as $v) {
		$it->getDepth() > $depth && $depth = $it->getDepth();
	}
	return $depth;
}

########################################################################
// tree for at depth
// @param $trees     => array (mandatory)
// @param $depth => int (mandatory)
// @param $max_pokemon => int (mandatory)
// @param $currentDepth => int (optional)
//
// Return all pokemon with data at a certain tree depth
########################################################################
function get_tree_at_depth($trees, $depth, $max_pokemon, $currentDepth = 0) {
	if ($depth == $currentDepth) { // Found depth
		return tree_remove_bellow($trees, $max_pokemon);
	} else { // Go deeper
		$arr = array();
		foreach ($trees as $temp) { // Go into all trees
			$tree = $temp->evolutions;
			$results = tree_remove_bellow(get_tree_at_depth($tree, $depth, $max_pokemon, $currentDepth + 1), $max_pokemon);
			$arr = tree_check_array($results, $arr, $depth - $currentDepth == 1);
		}
		return $arr;
	}
}

########################################################################
// used in get_tree_at_depth
########################################################################
function tree_check_array($array_check, $array_add, $correct_arrow) {
	$count = count($array_check);
	$i = 0;
	if (!is_null($array_check)) { // check if exists
		foreach ($array_check as $res) { // Check if above, equal or bellow center
			if ($count != 1 && $correct_arrow) { // only add arrow once
				$num = $i / ($count - 1);
				if ($num < 0.5) {
					$res->array_sufix = "_up";
				} elseif ($num > 0.5) {
					$res->array_sufix = "_down";
				} else {
					$res->array_sufix = "";
				}
			} else if (!isset($res->array_sufix)) {
				$res->array_sufix = "";
			}
			$array_add[] = $res;
			$i++;
		}
	}
	return $array_add;
}

########################################################################
// used in get_tree_at_depth
########################################################################
function tree_remove_bellow($tree, $max_pokemon)
{
	if (is_null($tree)) {
		return null;
	}
	$arr = array();
	foreach ($tree as $item) { // Check if above, equal or bellow center
		if ($item->id <= $max_pokemon) {
			$arr[] = $item;
		}
	}
	return $arr;
}

########################################################################
// generation
########################################################################
function generation($id)
{
	switch ($id) {
		case ($id >= 1 && $id <= 151):
			return [1, "Kanto"];
		case ($id >= 152 && $id <= 251):
			return [2, "Johto"];
		case ($id >= 252 && $id <= 386):
			return [3, "Hoenn"];
		case ($id >= 387 && $id <= 493):
			return [4, "Sinnoh"];
		case ($id >= 494 && $id <= 649):
			return [5, "Teselia"];
		case ($id >= 650 && $id <= 721):
			return [6, "Kalos"];
		case ($id >= 722 && $id <= 802):
			return [7, "Alola"];
	}
}

########################################################################
// check if point is inside porygon
########################################################################
function pointIsInsidePolygon($lat, $lng, $geos, $bounds) {
	if ($lat >= $bounds["minlat"] && $lat <= $bounds["maxlat"] && $lng >= $bounds["minlon"] && $lng <= $bounds["maxlon"]) {

		$intersections = 0;
		$geos_count = count($geos);

		for ($i = 1; $i < $geos_count; $i++) {
			$geo1 = $geos[$i - 1];
			$geo2 = $geos[$i];
			if ($geo1['lng'] == $geo2['lng'] and $geo1['lng'] == $lng and $lat > min($geo1['lat'], $geo2['lat']) and $lat < max($geo1['lat'], $geo2['lat'])) { // Check if point is on an horizontal polygon boundary
				return true;
			}
			if ($lng > min($geo1['lng'], $geo2['lng']) and $lng <= max($geo1['lng'], $geo2['lng']) and $lat <= max($geo1['lat'], $geo2['lat']) and $geo1['lng'] != $geo2['lng']) {
				$xinters = ($lng - $geo1['lng']) * ($geo2['lat'] - $geo1['lat']) / ($geo2['lng'] - $geo1['lng']) + $geo1['lat'];
				if ($xinters == $lat) { // Check if point is on the polygon boundary (other than horizontal)
					return true;
				}
				if ($geo1['lat'] == $geo2['lat'] || $lat <= $xinters) {
					$intersections++;
				}
			}
		}
		// If the number of edges we passed through is odd, then it's in the polygon.
		if ($intersections % 2 != 0) {
			return true;
		} else {
			return false;
		}
	} else {
		return false; // outside bounds
	}
}

########################################################################
// check if $boundsIn is inside (or equal to) $boundsOut
########################################################################
function polyIsInsidePolygon($geoIn, $boundsIn, $geoOut, $boundsOut) {
	if ($boundsIn["minlat"] >= $boundsOut["minlat"] && $boundsIn["maxlat"] <= $boundsOut["maxlat"] && $boundsIn["minlon"] >= $boundsOut["minlon"] && $boundsIn["maxlon"] <= $boundsOut["maxlon"]) {
		foreach ($geoIn as $coord) {
			if (!pointIsInsidePolygon($coord["lat"], $coord["lng"], $geoOut, $boundsOut)) {
				return false; // at least 1 point outside
			}
		}
		return true; // all points inside
	} else {
		return false; // bounds outside
	}
}

########################################################################
// compine outer ways into porygon
########################################################################
function combineOuter($outers) {
	$polygon = array();
	foreach ($outers as $key => $outer) {
		if ($key == 0) {
			$polygon = $outer;
		} else {
			$firstEle = $outer[0];
			$lastEle = $outer[count($outer) - 1];
			$lastElePoly = $polygon[count($polygon) - 1];
			if ($firstEle == $lastElePoly) {
				$polygon = array_merge($polygon, $outer);
			} else if ($lastEle == $lastElePoly) {
				$polygon = array_merge($polygon, array_reverse($outer));
			}
		}
	}
	return $polygon;
}
