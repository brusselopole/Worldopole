<?php
// -----------------------------------------------------------------------------------------------------------
// Pokestops datas
// Total pokestops
// Total lured
// -----------------------------------------------------------------------------------------------------------

$captcha_file	= SYS_PATH.'/core/json/captcha.stats.json';
if (is_file($captcha_file)) {
	$capdatas	= json_decode(file_get_contents($captcha_file), true);
	// Trim json stats files to last 7 days of data
	$capdatas = trim_stats_json($capdatas, $timestamp_lastweek);
}


$variables_secret = SYS_PATH.'/core/json/variables.secret.json';
$config_secret = json_decode(file_get_contents($variables_secret));

if ($config_secret->captcha_key=="") {
	$captcha['timestamp'] = $timestamp;
	// get amount of accounts requiring a captcha
	$req = "SELECT SUM(accounts_captcha) AS total FROM mainworker";
	$result = $mysqli->query($req);
	$data = $result->fetch_object();
	$captcha['captcha_accs'] = $data->total;
	// Add the datas in file
	$capdatas[] = $captcha;
} else {
	if (!empty($capdatas)) {
		$lastCaptcha = array_pop($capdatas);
	} else {
		$lastCaptcha["timestamp"]=strtotime("-7 days", strtotime(date("Y-m-d")));
	}
	$lastCaptchaDate = date("Y-m-d", $lastCaptcha["timestamp"]);
	$startTime = strtotime($lastCaptchaDate);
	$endTime = strtotime(date("Y-m-d"))+date("Z");
	$timeDiff = abs($endTime - $startTime);
	$numberDays = intval($timeDiff/86400) ;  // 86400 seconds in one day
	if ($numberDays>7) {
		$numberDays=7;
	}
	while ($numberDays>=0) {
		$day = $endTime-($numberDays*86400);
		$captchaUrl =
				"http://2captcha.com/res.php?key=" .
				$config_secret->captcha_key . "&action=getstats&date=" . date("Y-m-d", $day);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $captchaUrl);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$fileContents = curl_exec($ch);
		if (curl_errno($ch)) {
			echo curl_error($ch);
			echo "\n<br />";
			$fileContents = '';
		} else {
			curl_close($ch);
		}

		if (!is_string($fileContents) || !strlen($fileContents)) {
			echo "Failed to get contents.";
			$fileContents = '';
		}
		$capXml = simplexml_load_string($fileContents);

		foreach ($capXml as $key => $value) {
			if (	($numberDays==0
				&& ((int)$value->Attributes()->hour >= (int)date("H", $lastCaptcha["timestamp"])
				&& ((int)$value->Attributes()->hour <= (int)date("H")))
				) || $numberDays>0) {
				$captcha['timestamp'] =
						strtotime(date("Y-m-d", $day) . " " . $value->Attributes()->hour . ":00")+date("Z");
				$captcha['captcha_accs'] = (string)$value->volume;
				$capdatas[] = $captcha;
			}
		}
		--$numberDays;
	}
}

// Write to file
file_put_contents($captcha_file, json_encode($capdatas));
