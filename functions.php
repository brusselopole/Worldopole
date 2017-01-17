<?php

########################################################################
// Human Time Ago
// @param $timestamp	=> unix timestamp (mandatory)
// @param $locales	=> locales (mandatory)
//
// Return time ago at human format (eg: 2 hours ago)
########################################################################

function time_ago($timestamp, $locales)
{
	$then = new DateTime("@$timestamp");
	$now = new DateTime;
	$diff = $now->diff($then);

	$label_string = array(
		'y' => 'YEAR',
		'm' => 'MONTH',
		'd' => 'DAY',
		'h' => 'HOUR',
		'i' => 'MINUTE'
	);
	foreach ($label_string as $key => $label) {
		if ($diff->$key) {
			// DateInterval doesn't have weeks build in
			if (($key === 'd') && ($diff->$key >= 7)) {
				$difference_value = round($diff->$key / 7);
				$difference_label = 'WEEK';
			} else {
				$difference_value = $diff->$key;
				$difference_label = $label;
			}
			// plural
			if ($difference_value != 1) {
				$difference_label .= 'S';
			}
			break;
		}
	}

	if ($diff->invert != 1) {
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
