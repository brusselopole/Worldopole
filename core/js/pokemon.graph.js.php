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

// Load the locale elements
############################
include_once('../process/locales.loader.php');


# Chart Graph datas
$pokemon_id = $_GET['id'];

# Polar Graph datas

$pokemon_file = file_get_contents(SYS_PATH.'/core/json/pokedex.json');
$pokemons = json_decode($pokemon_file);

$atk		= $pokemons->pokemon->$pokemon_id->atk;
$def		= $pokemons->pokemon->$pokemon_id->def;
$sta		= $pokemons->pokemon->$pokemon_id->sta;

?>

var pokemon_id = '<?= (int) $pokemon_id ?>';

Chart.defaults.global.legend.display = false;

function drawSpawnGraph(data) {
	var ctx = $('#spawn_chart');

	var data = {
		labels: ['01:00', '02:00', '03:00', '04:00', '05:00', '06:00', '07:00', '08:00', '09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00', '17:00', '18:00', '19:00', '20:00', '21:00', '22:00', '23:00', '24:00'],
		datasets: [{
			backgroundColor: 'rgba(199, 255, 215, 1)',
			borderColor: 'rgba(0,255,73,1)',
			borderWidth: 1,
			data: data
		}]
	};

	var myBarChart = new Chart(ctx, {
		type: 'bar',
		data: data,
		options: ''
	});
}

drawSpawnGraph();

$.ajax({
	'type': 'GET',
	'global': false,
	'dataType': 'json',
	'url': 'core/process/aru.php',
	'data': {
		'type': 'pokemon_graph_data',
		'pokemon_id': pokemon_id
	}
}).done(function(data) {
	drawSpawnGraph(data);
});

var ctx2 = $('#polar_chart');

var data2 = {
	datasets: [{
		data: [
			<?= $atk ?>,
			<?= $def ?>,
			<?= $sta ?>
		],
		backgroundColor: [
			'rgba(249,96,134,0.8)',
			'rgba(88,194,193,0.8)',
			'rgba(92,184,92,0.8)'
		]
	}],
	labels: [
		' <?= $locales->ATTACK ?>',
		' <?= $locales->DEFENSE ?>',
		' <?= $locales->STAMINA ?>'
	]
};

new Chart(ctx2, {
	data: data2,
	type: 'polarArea'
});