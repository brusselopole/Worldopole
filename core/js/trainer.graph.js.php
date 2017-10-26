<?php

# Send Javascript header 
header('Content-type: text/javascript');

# Load Config 
include_once('../../config.php');


// Include & load the variables 
// ############################

$variables = SYS_PATH.'/core/json/variables.json';
$config = json_decode(file_get_contents($variables));

// Load the locale elements
############################
include_once('../process/locales.loader.php');


# Connect MySQL 
$mysqli = new mysqli(SYS_DB_HOST, SYS_DB_USER, SYS_DB_PSWD, SYS_DB_NAME, SYS_DB_PORT);
if ($mysqli->connect_error != '') {
	exit('Error MySQL Connect');
}

# Chart Graph datas	 

$trainer_lvl = [];
# For all 3 teams
for ($teamid = 1; $teamid <= 3; $teamid++) {
	$req = "SELECT level, count(level) AS count FROM trainer WHERE team = '".$teamid."'";
	if (!empty($config->system->trainer_blacklist)) {
		$req .= " AND name NOT IN ('".implode("','", $config->system->trainer_blacklist)."')";
	}
	$req .= " GROUP BY level";
	if ($result = $mysqli->query($req)) {
		# build level=>count array
		$data = [];
		while ($row = $result->fetch_assoc()) {
			$data[$row['level']] = $row['count'];
		}
		
		# only if data isn't empty
		if (!empty($data)) {
			# fill empty levels counts with 0
			for ($i = 5; $i <= 40; $i++) {
				if (!isset($data[$i])) {
					$data[$i] = 0;
				}
			}
			# sort array again
			ksort($data);
			$trainer_lvl[$teamid] = $data;
		}
		
		$result->free();
	}
}
$mysqli->close();

?>

// Global Options
// --------------

Chart.defaults.global.legend.display = false;

var options = {
	scales: {
		yAxes: [{
			ticks: {
				beginAtZero: true // minimum value will be 0.
			}
		}],
		xAxes: [{
			ticks: {
				autoSkipPadding: 10
			}
		}]
	},
	responsive: true,
	maintainAspectRatio: false
};

// Trainer level
// -------------

var trainer_lvl = document.getElementById('trainer_lvl');

var data_trainer_lvl = {
	labels: [5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36, 37, 38, 39, 40],
	datasets: [{
			label: '<?= $locales->TRAINERS_LEVEL_MYSTIC ?>',
			backgroundColor: 'rgba(59,129,255,0.6)',
			borderColor: 'rgba(59,129,255,1)',
			borderWidth: 1,
			data: [<?php if (isset($trainer_lvl[1])) {
				echo implode(',', $trainer_lvl[1]);
} ?>],
		},
		{
			label: '<?= $locales->TRAINERS_LEVEL_VALOR ?>',
			backgroundColor: 'rgba(247,10,20,0.6)',
			borderColor: 'rgba(247,10,20,1)',
			borderWidth: 1,
			data: [<?php if (isset($trainer_lvl[2])) {
			echo implode(',', $trainer_lvl[2]);
} ?>],
		},
		{
			label: '<?= $locales->TRAINERS_LEVEL_INSTINCT ?>',
			backgroundColor: 'rgba(248,153,0,0.6)',
			borderColor: 'rgba(248,153,0,1)',
			borderWidth: 1,
			data: [<?php if (isset($trainer_lvl[3])) {
			echo implode(',', $trainer_lvl[3]);
} ?>],
		}
	]
};

var myBarChart = new Chart(trainer_lvl, {
	type: 'bar',
	data: data_trainer_lvl,
	options: options
});
