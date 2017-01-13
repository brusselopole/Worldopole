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

if (!function_exists('http_response_code')) {
	function http_response_code($code = null)
	{
		if ($code !== null) {
			$messages = array(
				// Informational 1xx
				100 => 'Continue',
				101 => 'Switching Protocols',
			
				// Success 2xx
				200 => 'OK',
				201 => 'Created',
				202 => 'Accepted',
				203 => 'Non-Authoritative Information',
				204 => 'No Content',
				205 => 'Reset Content',
				206 => 'Partial Content',
			
				// Redirection 3xx
				300 => 'Multiple Choices',
				301 => 'Moved Permanently',
				302 => 'Found', // 1.1
				303 => 'See Other',
				304 => 'Not Modified',
				305 => 'Use Proxy',
				// 306 is deprecated but reserved
				307 => 'Temporary Redirect',
			
				// Client Error 4xx
				400 => 'Bad Request',
				401 => 'Unauthorized',
				402 => 'Payment Required',
				403 => 'Forbidden',
				404 => 'Not Found',
				405 => 'Method Not Allowed',
				406 => 'Not Acceptable',
				407 => 'Proxy Authentication Required',
				408 => 'Request Timeout',
				409 => 'Conflict',
				410 => 'Gone',
				411 => 'Length Required',
				412 => 'Precondition Failed',
				413 => 'Request Entity Too Large',
				414 => 'Request-URI Too Long',
				415 => 'Unsupported Media Type',
				416 => 'Requested Range Not Satisfiable',
				417 => 'Expectation Failed',
			
				// Server Error 5xx
				500 => 'Internal Server Error',
				501 => 'Not Implemented',
				502 => 'Bad Gateway',
				503 => 'Service Unavailable',
				504 => 'Gateway Timeout',
				505 => 'HTTP Version Not Supported',
				509 => 'Bandwidth Limit Exceeded'
			);
			
			if (isset($messages[$code])) {
				$text = $messages[$code];
			} else {
				exit('Unknown http status code "' . htmlentities($code) . '"');
			}

			$protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');
			header($protocol . ' ' . $code . ' ' . $text);
			$GLOBALS['http_response_code'] = $code;
		} else {
			$code = (isset($GLOBALS['http_response_code']) ? $GLOBALS['http_response_code'] : 200);
		}
		
		return $code;
	}
}
