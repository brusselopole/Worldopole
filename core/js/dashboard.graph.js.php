<?php

# Send Javascript header
header('Content-type: text/javascript');

# Load Config
include_once('../../config.php');


// Include & load the variables
// ############################

$variables = SYS_PATH.'/core/json/variables.json';
$config = json_decode(file_get_contents($variables));


// Include & load locales (because it's REALLY REALLY REALLY IMPORTANT TO HAVE A FULLY TRANSLATE DASHBOARD )
// #########################################################################################################

include_once(SYS_PATH.'/core/process/locales.loader.php');


// Check if there's a pokemon stat file
// ####################################

$stats_file = SYS_PATH.'/core/json/pokemon.stats.json';
$stats = json_decode(file_get_contents($stats_file));


// Manage Time Interval
// #####################

include_once('../process/timezone.loader.php');


$now = time();
$yesterday = $now - 86400;
$lastweek = $now - 604800;

$i = 0;
$labels_global = array();
$total = array();
$labels = array();
$veco = array();
$commo = array();
$rare = array();
$myth = array();
$labels_gym = array();

$mystic_average = array();
$mystic_owned = array();

$valor_average = array();
$valor_owned = array();

$instinct_average = array();
$instinct_owned = array();

$labels_stops = array();
$lure = array();

$labels_captcha = array();
$captcha_accs = array();

foreach ($stats as $data) {
	if ($data->timestamp > $lastweek) {
		$labels_global[] = '"'.date('D H:i', $data->timestamp).'"';
		$total[] = $data->pokemon_now;
	}

	if ($data->timestamp > $yesterday) {
		$labels[] = '"'.date('H:i', $data->timestamp).'"';

		if (isset($data->rarity_spawn->{'Very common'})) {
			$veco[]		= $data->rarity_spawn->{'Very common'};
		} else {
			$veco[]		= 0;
		}

		if (isset($data->rarity_spawn->Common)) {
			$commo[]	= $data->rarity_spawn->Common;
		} else {
			$commo[]	= 0;
		}

		if (isset($data->rarity_spawn->Rare)) {
			$rare[]		= $data->rarity_spawn->Rare;
		} else {
			$rare[]		= 0;
		}

		if (isset($data->rarity_spawn->Mythic)) {
			$myth[]		= $data->rarity_spawn->Mythic;
		} else {
			$myth[]		= 0;
		}
	}
}


$stats_file = SYS_PATH.'/core/json/gym.stats.json';
$stats = json_decode(file_get_contents($stats_file));


foreach ($stats as $data) {
	if ($data->timestamp > $lastweek) {
		$labels_gym[] = '"'.date('D H:i', $data->timestamp).'"';

		$mystic_average[] = $data->team->mystic->average;
		$mystic_owned[]			= $data->team->mystic->gym_owned;

		$valor_average[]		= $data->team->valor->average;
		$valor_owned[] = $data->team->valor->gym_owned;

		$instinct_average[] = $data->team->instinct->average;
		$instinct_owned[] = $data->team->instinct->gym_owned;
	}
}


$stats_file = SYS_PATH.'/core/json/pokestop.stats.json';
$stats = json_decode(file_get_contents($stats_file));


foreach ($stats as $data) {
	if ($data->timestamp > $lastweek) {
		$labels_stops[] = '"'.date('D H:i', $data->timestamp).'"';
		$lure[] = $data->lured;
	}
}


if ($config->system->captcha_support) {
	$stats_file = SYS_PATH.'/core/json/captcha.stats.json';
	$stats = json_decode(file_get_contents($stats_file));


	foreach ($stats as $data) {
		if ($data->timestamp > $lastweek) {
			$labels_captcha[] = '"'.date('D H:i', $data->timestamp).'"';
			$captcha_accs[] = $data->captcha_accs;
		}
	}
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
				autoSkipPadding: 10,
				fontFamily: 'monospace'
			}
		}]
	},
	responsive: true,
	maintainAspectRatio: false
};


// Total Spawn Graph
// -----------------

var ctx = $('#total_spawn');

var data = {
	labels: [<?= implode(',', $labels_global) ?>],
	datasets: [{
		label: '<?= $locales->DASHBOARD_SPAWN_TOTAL ?>',
		fill: true,
		lineTension: 0.1,
		backgroundColor: 'rgba(75,192,192,0.4)',
		borderColor: 'rgba(75,192,192,1)',
		borderCapStyle: 'butt',
		borderDash: [],
		borderDashOffset: 0.0,
		borderJoinStyle: 'miter',
		pointBorderColor: 'rgba(75,192,192,1)',
		pointBackgroundColor: '#fff',
		pointBorderWidth: 1,
		pointHoverRadius: 5,
		pointHoverBackgroundColor: 'rgba(75,192,192,1)',
		pointHoverBorderColor: 'rgba(0,0,0,1)',
		pointHoverBorderWidth: 2,
		pointRadius: 0,
		pointHitRadius: 10,
		data: [<?= implode(',', $total)?>],
		spanGaps: false,
	}]
};

var myLineChart = new Chart(ctx, {
	type: 'line',
	data: data,
	options: options
});


// Spawn Graph by type
// --------------------


var ctx_vc = $('#very_common');

var data_vc = {
	labels: [<?= implode(',', $labels) ?>],
	datasets: [{
		label: '<?= $locales->VERYCOMMON ?>',
		fill: false,
		lineTension: 0.1,
		backgroundColor: 'rgba(175,192,192,0.4)',
		borderColor: 'rgba(175,192,192,1)',
		borderCapStyle: 'butt',
		borderDash: [],
		borderDashOffset: 0.0,
		borderJoinStyle: 'miter',
		pointBorderColor: 'rgba(175,192,192,1)',
		pointBackgroundColor: '#fff',
		pointBorderWidth: 1,
		pointHoverRadius: 5,
		pointHoverBackgroundColor: 'rgba(175,192,192,1)',
		pointHoverBorderColor: 'rgba(0,0,0,1)',
		pointHoverBorderWidth: 2,
		pointRadius: 0,
		pointHitRadius: 10,
		data: [<?= implode(',', $veco)?>],
		spanGaps: false,
	}]
};


var myLineChart = new Chart(ctx_vc, {
	type: 'line',
	data: data_vc,
	options: options
});


var ctx_comm = $('#common');

var data_comm = {
	labels: [<?= implode(',', $labels) ?>],
	datasets: [{
		label: '<?= $locales->COMMON ?>',
		fill: false,
		lineTension: 0.1,
		backgroundColor: 'rgba(175,192,192,0.4)',
		borderColor: 'rgba(175,192,192,1)',
		borderCapStyle: 'butt',
		borderDash: [],
		borderDashOffset: 0.0,
		borderJoinStyle: 'miter',
		pointBorderColor: 'rgba(175,192,192,1)',
		pointBackgroundColor: '#fff',
		pointBorderWidth: 1,
		pointHoverRadius: 5,
		pointHoverBackgroundColor: 'rgba(175,192,192,1)',
		pointHoverBorderColor: 'rgba(0,0,0,1)',
		pointHoverBorderWidth: 2,
		pointRadius: 0,
		pointHitRadius: 10,
		data: [<?= implode(',', $commo)?>],
		spanGaps: false,
	}]
};


var myLineChart = new Chart(ctx_comm, {
	type: 'line',
	data: data_comm,
	options: options
});

var ctx_rare = $('#rare');

var data_rare = {
	labels: [<?= implode(',', $labels) ?>],
	datasets: [{
		label: '<?= $locales->RARE ?>',
		fill: false,
		lineTension: 0.1,
		backgroundColor: 'rgba(175,192,192,0.4)',
		borderColor: 'rgba(175,192,192,1)',
		borderCapStyle: 'butt',
		borderDash: [],
		borderDashOffset: 0.0,
		borderJoinStyle: 'miter',
		pointBorderColor: 'rgba(175,192,192,1)',
		pointBackgroundColor: '#fff',
		pointBorderWidth: 1,
		pointHoverRadius: 5,
		pointHoverBackgroundColor: 'rgba(175,192,192,1)',
		pointHoverBorderColor: 'rgba(0,0,0,1)',
		pointHoverBorderWidth: 2,
		pointRadius: 0,
		pointHitRadius: 10,
		data: [<?= implode(',', $rare)?>],
		spanGaps: false,
	}]
};


var myLineChart = new Chart(ctx_rare, {
	type: 'line',
	data: data_rare,
	options: options
});



var ctx_myth = $('#mythics');

var data_myth = {
	labels: [<?= implode(',', $labels) ?>],
	datasets: [{
		label: '<?= $locales->MYTHIC ?>',
		fill: false,
		lineTension: 0.1,
		backgroundColor: 'rgba(175,192,192,0.4)',
		borderColor: 'rgba(175,192,192,1)',
		borderCapStyle: 'butt',
		borderDash: [],
		borderDashOffset: 0.0,
		borderJoinStyle: 'miter',
		pointBorderColor: 'rgba(175,192,192,1)',
		pointBackgroundColor: '#fff',
		pointBorderWidth: 1,
		pointHoverRadius: 5,
		pointHoverBackgroundColor: 'rgba(175,192,192,1)',
		pointHoverBorderColor: 'rgba(0,0,0,1)',
		pointHoverBorderWidth: 2,
		pointRadius: 0,
		pointHitRadius: 10,
		data: [<?= implode(',', $myth)?>],
		spanGaps: false,
	}]
};


var myLineChart = new Chart(ctx_myth, {
	type: 'line',
	data: data_myth,
	options: options
});


// Team Prestige Average
// ---------------------


var team_av = $('#team_av');

var data_av = {
	labels: [<?= implode(',', $labels_gym) ?>],
	datasets: [{
			label: '<?= $locales->DASHBOARD_GRAPH_MYSTIC_PRESTIGE_AVERAGE ?>',
			fill: false,
			lineTension: 0.1,
			backgroundColor: 'rgba(59,129,255,0.4)',
			borderColor: 'rgba(59,129,255,1)',
			borderCapStyle: 'butt',
			borderDash: [],
			borderDashOffset: 0.0,
			borderJoinStyle: 'miter',
			pointBorderColor: 'rgba(59,129,255,1)',
			pointBackgroundColor: '#fff',
			pointBorderWidth: 1,
			pointHoverRadius: 5,
			pointHoverBackgroundColor: 'rgba(59,129,255,1)',
			pointHoverBorderColor: 'rgba(0,0,0,1)',
			pointHoverBorderWidth: 2,
			pointRadius: 0,
			pointHitRadius: 10,
			data: [<?= implode(',', $mystic_average)?>],
			spanGaps: false,
		},
		{
			label: '<?= $locales->DASHBOARD_GRAPH_VALOR_PRESTIGE_AVERAGE ?>',
			fill: false,
			lineTension: 0.1,
			backgroundColor: 'rgba(247,10,20,0.4)',
			borderColor: 'rgba(247,10,20,1)',
			borderCapStyle: 'butt',
			borderDash: [],
			borderDashOffset: 0.0,
			borderJoinStyle: 'miter',
			pointBorderColor: 'rgba(247,10,20,1)',
			pointBackgroundColor: '#fff',
			pointBorderWidth: 1,
			pointHoverRadius: 5,
			pointHoverBackgroundColor: 'rgba(247,10,20,1)',
			pointHoverBorderColor: 'rgba(0,0,0,1)',
			pointHoverBorderWidth: 2,
			pointRadius: 0,
			pointHitRadius: 10,
			data: [<?= implode(',', $valor_average)?>],
			spanGaps: false,
		},
		{
			label: '<?= $locales->DASHBOARD_GRAPH_INSTINCT_PRESTIGE_AVERAGE ?>',
			fill: false,
			lineTension: 0.1,
			backgroundColor: 'rgba(248,153,0,0.4)',
			borderColor: 'rgba(248,153,0,1)',
			borderCapStyle: 'butt',
			borderDash: [],
			borderDashOffset: 0.0,
			borderJoinStyle: 'miter',
			pointBorderColor: 'rgba(248,153,0,1)',
			pointBackgroundColor: '#fff',
			pointBorderWidth: 1,
			pointHoverRadius: 5,
			pointHoverBackgroundColor: 'rgba(248,153,0,1)',
			pointHoverBorderColor: 'rgba(0,0,0,1)',
			pointHoverBorderWidth: 2,
			pointRadius: 0,
			pointHitRadius: 10,
			data: [<?= implode(',', $instinct_average)?>],
			spanGaps: false,
		}
	]
};


var myLineChart = new Chart(team_av, {
	type: 'line',
	data: data_av,
	options: options
});



// Team Gym capture
// ----------------


var team_gym = $('#team_gym');

var data_team_gym = {
	labels: [<?= implode(',', $labels_gym) ?>],
	datasets: [{
			label: '<?= $locales->DASHBOARD_GRAPH_MYSTIC_GYM_OWNED ?>',
			fill: false,
			lineTension: 0.1,
			backgroundColor: 'rgba(59,129,255,0.4)',
			borderColor: 'rgba(59,129,255,1)',
			borderCapStyle: 'butt',
			borderDash: [],
			borderDashOffset: 0.0,
			borderJoinStyle: 'miter',
			pointBorderColor: 'rgba(59,129,255,1)',
			pointBackgroundColor: '#fff',
			pointBorderWidth: 1,
			pointHoverRadius: 5,
			pointHoverBackgroundColor: 'rgba(59,129,255,1)',
			pointHoverBorderColor: 'rgba(0,0,0,1)',
			pointHoverBorderWidth: 2,
			pointRadius: 0,
			pointHitRadius: 10,
			data: [<?= implode(',', $mystic_owned)?>],
			spanGaps: false,
		},
		{
			label: '<?= $locales->DASHBOARD_GRAPH_VALOR_GYM_OWNED ?>',
			fill: false,
			lineTension: 0.1,
			backgroundColor: 'rgba(247,10,20,0.4)',
			borderColor: 'rgba(247,10,20,1)',
			borderCapStyle: 'butt',
			borderDash: [],
			borderDashOffset: 0.0,
			borderJoinStyle: 'miter',
			pointBorderColor: 'rgba(247,10,20,1)',
			pointBackgroundColor: '#fff',
			pointBorderWidth: 1,
			pointHoverRadius: 5,
			pointHoverBackgroundColor: 'rgba(247,10,20,1)',
			pointHoverBorderColor: 'rgba(0,0,0,1)',
			pointHoverBorderWidth: 2,
			pointRadius: 0,
			pointHitRadius: 10,
			data: [<?= implode(',', $valor_owned)?>],
			spanGaps: false,
		},
		{
			label: '<?= $locales->DASHBOARD_GRAPH_INSTINCT_GYM_OWNED ?>',
			fill: false,
			lineTension: 0.1,
			backgroundColor: 'rgba(248,153,0,0.4)',
			borderColor: 'rgba(248,153,0,1)',
			borderCapStyle: 'butt',
			borderDash: [],
			borderDashOffset: 0.0,
			borderJoinStyle: 'miter',
			pointBorderColor: 'rgba(248,153,0,1)',
			pointBackgroundColor: '#fff',
			pointBorderWidth: 1,
			pointHoverRadius: 5,
			pointHoverBackgroundColor: 'rgba(248,153,0,1)',
			pointHoverBorderColor: 'rgba(0,0,0,1)',
			pointHoverBorderWidth: 2,
			pointRadius: 0,
			pointHitRadius: 10,
			data: [<?= implode(',', $instinct_owned)?>],
			spanGaps: false,
		}
	]
};

var myLineChart = new Chart(team_gym, {
	type: 'line',
	data: data_team_gym,
	options: options
});




// Pokestop lure
// -------------




var ctx_lure = $('#lures');

var data_lure = {
	labels: [<?= implode(',', $labels_stops) ?>],
	datasets: [{
		label: '<?= $locales->DASHBOARD_GRAPH_LURED_POKESTOPS ?>',
		fill: true,
		lineTension: 0.1,
		backgroundColor: 'rgba(124,0,210,0.4)',
		borderColor: 'rgba(124,0,210,1)',
		borderCapStyle: 'butt',
		borderDash: [],
		borderDashOffset: 0.0,
		borderJoinStyle: 'miter',
		pointBorderColor: 'rgba(124,0,210,1)',
		pointBackgroundColor: '#fff',
		pointBorderWidth: 1,
		pointHoverRadius: 5,
		pointHoverBackgroundColor: 'rgba(124,0,210,1)',
		pointHoverBorderColor: 'rgba(0,0,0,1)',
		pointHoverBorderWidth: 2,
		pointRadius: 0,
		pointHitRadius: 10,
		data: [<?= implode(',', $lure)?>],
		spanGaps: false,
	}]
};


var myLineChart = new Chart(ctx_lure, {
	type: 'line',
	data: data_lure,
	options: options
});


// Captcha
// -------------

<?php if ($config->system->captcha_support) { ?>
var ctx_captcha_accs = $('#captcha');

var data_captcha_accs = {
	labels: [<?= implode(',', $labels_captcha) ?>],
	datasets: [{
		label: '<?= $locales->DASHBOARD_CAPTCHA ?>',
		fill: true,
		lineTension: 0.1,
		backgroundColor: 'rgba(255,183,0,0.4)',
		borderColor: 'rgba(255,183,0,1)',
		borderCapStyle: 'butt',
		borderDash: [],
		borderDashOffset: 0.0,
		borderJoinStyle: 'miter',
		pointBorderColor: 'rgba(255,183,0,1)',
		pointBackgroundColor: '#fff',
		pointBorderWidth: 1,
		pointHoverRadius: 5,
		pointHoverBackgroundColor: 'rgba(255,183,0,1)',
		pointHoverBorderColor: 'rgba(0,0,0,1)',
		pointHoverBorderWidth: 2,
		pointRadius: 0,
		pointHitRadius: 10,
		data: [<?= implode(',', $captcha_accs)?>],
		spanGaps: false,
	}]
};


var myLineChart = new Chart(ctx_captcha_accs, {
	type: 'line',
	data: data_captcha_accs,
	options: options
});
<?php } ?>
