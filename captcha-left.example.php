<?php

	// allow CORS
	header('Access-Control-Allow-Origin: *');


	// connect to MySQL

	# EDIT ME PLEASE

	$mysql = new mysqli();
	$mysql->connect('#SYS_DB_HOST#', '#SYS_DB_USER#'', '#SYS_DB_PSWD#', '#SYS_DB_NAME#', '#SYS_DB_PORT#(MOSTLY 3306)');


	// get amount of accounts requiring a captcha
	$datetime = date("Y-m-d H:i:s", time() - (61*60));
	$query = "SELECT COUNT(*) as captcha_accs FROM workerstatus WHERE message LIKE '%encountering a captcha%' and last_modified > '$datetime'";
	$result = $mysql->query($query);
	$row = $result->fetch_assoc();
	$captchaed = $row['captcha_accs'];

	echo $captchaed;

?>
