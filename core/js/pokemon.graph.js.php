<?php

# Test to check if the file is called properly

if (!isset($_GET['id'])) {
	http_response_code(400);
	echo 'Bad Request';
	exit();
}

# Send Javascript header
header('Content-type: text/javascript');

# Load Config
include_once('../../config.php');


// Manage Time Interval
// #####################

include_once('../process/timezone.loader.php');


# Connect MySQL
$mysqli = new mysqli(SYS_DB_HOST, SYS_DB_USER, SYS_DB_PSWD, SYS_DB_NAME, SYS_DB_PORT);
if ($mysqli->connect_error != '') {
	exit('Error MySQL Connect');
}

# Chart Graph datas

$pokemon_id = mysqli_real_escape_string($mysqli, $_GET['id']);

// Create the h24 array with associated values
for ($i= 0; $i <= 23; $i++) {
	if ($i < 10) {
		$key = '0'.$i;
	} else {
		$key = $i;
	}

	if (isset($array[$key])) {
		$spawn[$key] = $array[$key];
	} else {
		$spawn[$key] = 0;
	}
}

// Result for midnight are at the end in format PM
if (isset($spawn['00'])) {
	$spawn[] = $spawn['00'];
	unset($spawn['00']);

	$spawn = array_values($spawn);
} else {
	$spawn = array_values($spawn);
}

$data = implode(',', $spawn);
$data = '['.$data.']';



# Polar Graph datas

$pokemon_file	= file_get_contents(SYS_PATH.'/core/json/pokedex.json');
$pokemons		= json_decode($pokemon_file);

$atk			= $pokemons->pokemon->$pokemon_id->atk;
$def			= $pokemons->pokemon->$pokemon_id->def;
$stam			= $pokemons->pokemon->$pokemon_id->stam;


?>

var pokemon_id = '<?= (int)$pokemon_id ?>';

Chart.defaults.global.legend.display = false;


function drawSpawnGraph(data){
	var ctx = $("#spawn_chart");

	var data = {
		labels: ["1am","2am","3am","4am","5am","6am","7am","8am","9am","10am","11am","12am","1pm","2pm","3pm","4pm","5pm","6pm","7pm","8pm","9pm","10pm","11pm","12pm"],
		datasets: [
			{
				backgroundColor: 'rgba(199, 255, 215, 1)',
				borderColor: 'rgba(0,255,73,1)',
				borderWidth: 1,
				data: data
			}
		]
	};

	var myBarChart = new Chart(ctx, {
		type: 'bar',
		data: data,
		options: ''
	});
}

drawSpawnGraph();

$.ajax({
		'async': true,
		'type': "GET",
		'global': false,
		'dataType': 'json',
		'url': "core/process/aru.php",
		'data': {
			'request': "",
			'target': 'arrange_url',
			'method': 'method_target',
			'type' : 'pokemon_graph_data',
			'pokemon_id' : pokemon_id
		}
}).done(function (data) {

	drawSpawnGraph(data);
});

var ctx2 = $("#polar_chart");

var data2 = {
	datasets: [{
		data: [
			<?= $atk ?>,
			<?= $def ?>,
			<?= $stam ?>
		],
		backgroundColor: [
			"rgba(249,96,134,0.8)",
			"rgba(88,194,193,0.8)",
			"rgba(92,184,92,0.8)"
		],
		label: 'My dataset' // for legend
	}],
	labels: [
		"Attack",
		"Defense",
		"Stamina"
	]
};


new Chart(ctx2, {
	data: data2,
	type: 'polarArea'
});
