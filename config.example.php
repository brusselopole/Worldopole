<?php

/**
 * @file
 * A single location to store configuration.
 */
 
 

# EDIT ME PLEASE 

define('SYS_DB_NAME'			,'');															// mysql db name
define('SYS_DB_USER'			,'');															// mysql username
define('SYS_DB_PSWD'			,'');														// mysql password
define('SYS_DB_HOST'			,'');														// mysql server name
define('SYS_DB_PORT'			,3306);																	// mysql server port
define('TIME_DELAY' 			,'+ 2 HOURS');															// Time diff between u and GMT Time (eg: Brussels is + 2 HOURS from GMT) 	


# Please, do not touch me, I'm fine ;) 

define('SYS_PATH'				,realpath(dirname(__FILE__)));											// full path
define('SYS_USESS_VAR'			,'usrSessVal');															// user session variable name
define('SYS_DEVELOPMENT_MODE'	, false);																// debug mode 



if(directory() != ''){
	$subdirectory = '/'.directory().'/';
}else{
	$subdirectory = '/';
}
 
if(isset($_SERVER['REQUEST_SCHEME'])){
	
	define('HOST_URL'				, $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].''.$subdirectory);			// Host 	

}else{
	
	if(isset($_SERVER['HTTPS']) AND $_SERVER['HTTPS'] == 'on'){
		define('HOST_URL'				, 'https://'.$_SERVER['HTTP_HOST']);							// Host 
	}else{
		define('HOST_URL'				, 'http://'.$_SERVER['HTTP_HOST']);								// Host
	}
	
}






## Subdirectory trick 

function directory(){
    $root = $_SERVER['DOCUMENT_ROOT'];
    $filePath = dirname(__FILE__);
    

    if ($root == $filePath) {
       
        return ''; // installed in the root
    
    } else {
        
        $subdir_path 	= explode('/', $filePath); 
        $subdir 		= end($subdir_path);
        
        return $subdir; 
        
        
    }

}



?>