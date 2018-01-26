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

// Load Query Manager
// ###################

include_once __DIR__ . '/../process/queries/QueryManager.php';
$manager = QueryManager::current();

# Chart Graph datas	 

$trainer_lvl = [];
# For all 3 teams
for ($teamid = 1; $teamid <= 3; $teamid++) {
    $data = $manager->getTrainerLevelCount($teamid);
	$trainer_lvl[$teamid] = $data;
}

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
