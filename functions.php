<?php


########################################################################
// Human Time Ago 
// @param $timestamp 	=> timestamp (mandatory)  
// @param $now 			=> timestamp (optionnal)
//
// Return time ago at human format (eg: 2 hours ago) 
########################################################################


function time_ago( $timestamp, $now = 0, $lang = 'EN' ) {
	
	$translations = json_decode(file_get_contents(SYS_PATH.'/core/json/translations.json'));
	
	
    // Set up our variables.
    $minute_in_seconds = 60;
    $hour_in_seconds   = $minute_in_seconds * 60;
    $day_in_seconds    = $hour_in_seconds * 24;
    $week_in_seconds   = $day_in_seconds * 7;
    $month_in_seconds  = $day_in_seconds * 30;
    $year_in_seconds   = $day_in_seconds * 365;

    // Get the current time if a reference point has not been provided.
    if ( 0 === $now ) {
        $now = time();
    }
    
    if ( $timestamp == 0 ) {
        $time_ago = 'We miss it';
    }
    else{
	    
	    // Make sure the timestamp to check is in the past.
	    if ( $timestamp > $now ) {
	        throw new Exception( 'Timestamp is in the future' );
	    }
	
	    // Calculate the time difference between the current time reference point and the timestamp we're comparing.
	    $time_difference = (int) abs( $now - $timestamp );
		
		
	    // Calculate the time ago using the smallest applicable unit.
	    if ( $time_difference < $hour_in_seconds ) {
	
	        $difference_value = round( $time_difference / $minute_in_seconds );
	        $difference_label = 'MINUTE';
	
	    } elseif ( $time_difference < $day_in_seconds ) {
	
	        $difference_value = round( $time_difference / $hour_in_seconds );
	        $difference_label = 'HOUR';
	
	    } elseif ( $time_difference < $week_in_seconds ) {
	
	        $difference_value = round( $time_difference / $day_in_seconds );
	        $difference_label = 'DAY';
	
	    } elseif ( $time_difference < $month_in_seconds ) {
	
	        $difference_value = round( $time_difference / $week_in_seconds );
	        $difference_label = 'WEEK';
	
	    } elseif ( $time_difference < $year_in_seconds ) {
	
	        $difference_value = round( $time_difference / $month_in_seconds );
	        $difference_label = 'MONTH';
	
	    } else {
	
	        $difference_value = round( $time_difference / $year_in_seconds );
	        $difference_label = 'YEAR';
	    }
	    
	
	    if ( $difference_value <= 1 ) {
	        
	        $difference_label = $difference_label.'S'; 
	        $time_ago = $difference_value.' '.$translations->$difference_label->$lang; 
	        
	    } else {
	       
	        $time_ago = $difference_value.' '.$translations->$difference_label->$lang;
	    }
	    
    }

    

    return $time_ago;
}


########################################################################
// Percent calculator 
// @param $val 			=> int (mandatory)  
// @param $val_total 	=> int (mandatory)
//
// Return pourcent from total
########################################################################


function percent($val, $val_total) {

	$count1 = $val_total / $val;
	$count2 = $count1 * 100;
	
	$count = number_format($count2, 0);
	
	return $count;
}

########################################################################
// File datetime 
// @param $file 			=> string (mandatory)  
//
// Return last_modified file format timestamp
########################################################################



function file_datetime($file){
	
	$time = filemtime($file); 
	return $time; 
}




 if (!function_exists('http_response_code')) {
	function http_response_code($code = NULL) {
	
	    if ($code !== NULL) {
	
	        switch ($code) {
	            case 100: $text = 'Continue'; break;
	            case 101: $text = 'Switching Protocols'; break;
	            case 200: $text = 'OK'; break;
	            case 201: $text = 'Created'; break;
	            case 202: $text = 'Accepted'; break;
	            case 203: $text = 'Non-Authoritative Information'; break;
	            case 204: $text = 'No Content'; break;
	            case 205: $text = 'Reset Content'; break;
	            case 206: $text = 'Partial Content'; break;
	            case 300: $text = 'Multiple Choices'; break;
	            case 301: $text = 'Moved Permanently'; break;
	            case 302: $text = 'Moved Temporarily'; break;
	            case 303: $text = 'See Other'; break;
	            case 304: $text = 'Not Modified'; break;
	            case 305: $text = 'Use Proxy'; break;
	            case 400: $text = 'Bad Request'; break;
	            case 401: $text = 'Unauthorized'; break;
	            case 402: $text = 'Payment Required'; break;
	            case 403: $text = 'Forbidden'; break;
	            case 404: $text = 'Not Found'; break;
	            case 405: $text = 'Method Not Allowed'; break;
	            case 406: $text = 'Not Acceptable'; break;
	            case 407: $text = 'Proxy Authentication Required'; break;
	            case 408: $text = 'Request Time-out'; break;
	            case 409: $text = 'Conflict'; break;
	            case 410: $text = 'Gone'; break;
	            case 411: $text = 'Length Required'; break;
	            case 412: $text = 'Precondition Failed'; break;
	            case 413: $text = 'Request Entity Too Large'; break;
	            case 414: $text = 'Request-URI Too Large'; break;
	            case 415: $text = 'Unsupported Media Type'; break;
	            case 500: $text = 'Internal Server Error'; break;
	            case 501: $text = 'Not Implemented'; break;
	            case 502: $text = 'Bad Gateway'; break;
	            case 503: $text = 'Service Unavailable'; break;
	            case 504: $text = 'Gateway Time-out'; break;
	            case 505: $text = 'HTTP Version not supported'; break;
	            default:
	                exit('Unknown http status code "' . htmlentities($code) . '"');
	            break;
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
	
?>