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
// File datetime
// @param $file		=> string (mandatory)
//
// Return last_modified file format timestamp
########################################################################

function file_datetime($file)
{
	$time = filemtime($file);
	return $time;
}

########################################################################
// File version (unix timestamp)
// @param $url		=> string (mandatory)
//
// Return $url with last_modified unix timestamp before suffix
########################################################################

function auto_ver($url)
{
	$path = pathinfo($url);
	$ver = '.'.filemtime(SYS_PATH.'/'.$url).'.';
	echo $path['dirname'].'/'.preg_replace('/\.(css|js)$/', $ver."$1", $path['basename']);
}
