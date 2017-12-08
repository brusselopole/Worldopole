<?php

/**
 * @file
 * A single location to store configuration.
 */


# EDIT ME PLEASE

// mysql db name
define('SYS_DB_NAME', '#SYS_DB_NAME#');
// mysql username
define('SYS_DB_USER', '#SYS_DB_USER#');
// mysql password
define('SYS_DB_PSWD', '#SYS_DB_PSWD#');
// mysql server name
define('SYS_DB_HOST', '#SYS_DB_HOST#');
// mysql server port
define('SYS_DB_PORT', 3306);


# Please, do not touch me, I'm fine ;)

// full path
define('SYS_PATH', realpath(dirname(__FILE__)));
// user session variable name
define('SYS_USESS_VAR', 'usrSessVal');
// debug mode
define('SYS_DEVELOPMENT_MODE', false);


if (directory() != '') {
	$subdirectory = '/'.directory().'/';
} else {
	$subdirectory = '/';
}

if (isset($_SERVER['HTTP_HOST'])) {
	if (isset($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
		define('HOST_URL', $_SERVER['HTTP_X_FORWARDED_PROTO'].'://'.$_SERVER['HTTP_HOST'].$subdirectory);
	} else if (isset($_SERVER['REQUEST_SCHEME'])) {
		define('HOST_URL', $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].$subdirectory);
	} else {
		if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
			define('HOST_URL', 'https://'.$_SERVER['HTTP_HOST'].$subdirectory);
		} else {
			define('HOST_URL', 'http://'.$_SERVER['HTTP_HOST'].$subdirectory);
		}
	}
}

## Subdirectory trick
function directory()
{
	##https://stackoverflow.com/questions/2090723/how-to-get-the-relative-directory-no-matter-from-where-its-included-in-php
	return substr(str_replace('\\', '/', realpath(dirname(__FILE__))), strlen(str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT']))) + 1);
}
