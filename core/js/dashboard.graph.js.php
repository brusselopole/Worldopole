<?php

# Send Javascript header 
header('Content-type: text/javascript');

# Load Config 
include_once('../../config.php');


// Include & load the variables 
// ############################

$variables 	= realpath(dirname(__FILE__)).'/../json/variables.json';
$config 	= json_decode(file_get_contents($variables)); 


// Check if there's a pokemon stat file 
// ####################################

$stats_file	= SYS_PATH.'/core/json/pokemon.stats.json';
$stats 		= json_decode(file_get_contents($stats_file)); 


$now		= time(); 
$yesterday 	= $now-86400; 

$i=0; 

foreach($stats as $data){
	
	$labels_global[]	= '"'.date('d/m h:i a', $data->timestamp ).'"';
	$total[] 			= $data->pokemon_now;
	
	if($data->timestamp > $yesterday){
		
		$labels[] = '"'.date('h:i a', $data->timestamp ).'"'; 
		

		$datas['global'][$i]['global'] = $data->pokemon_now; 
		
		
		if(!empty($data->rarity_spawn->{'Very common'})){
			$veco[] 	= $data->rarity_spawn->{'Very common'};
		}
		else{
			$veco[]		= 0; 
		}
		
	
		if(!empty($data->rarity_spawn->Common)){
			$commo[]	= $data->rarity_spawn->Common;
		}
		else{
			$commo[]	= 0; 
		}
		
	
		if(!empty($data->rarity_spawn->Mythic)){
			$rare[]		= $data->rarity_spawn->Rare;
		}
		else{
			$rare[]		= 0; 
		}
		
	
		if(!empty($data->rarity_spawn->Mythic)){
			$myth[]		= $data->rarity_spawn->Mythic;
		}
		else{
			$myth[]		= 0; 
	}
	
	}
		
		 
	
}	



$stats_file	= SYS_PATH.'/core/json/gym.stats.json';
$stats 		= json_decode(file_get_contents($stats_file)); 


foreach($stats as $data){
	
	if($data->timestamp > $yesterday){
	
		$labels_gym[] 			= '"'.date('h:i a', $data->timestamp ).'"';
		
		$mystic_average[] 		= $data->team->mystic->average;
		$mystic_owned[]			= $data->team->mystic->gym_owned;

		$valor_average[] 		= $data->team->valor->average;
		$valor_owned[]			= $data->team->valor->gym_owned;
		
		$instinct_average[]		= $data->team->instinct->average;
		$instinct_owned[]		= $data->team->instinct->gym_owned;
	
	}
		
}


$stats_file	= SYS_PATH.'/core/json/pokestop.stats.json';
$stats 		= json_decode(file_get_contents($stats_file)); 


foreach($stats as $data){
	
	//if($data->timestamp > $yesterday){
	
		$labels_stops[]			= '"'.date('d/m h:i a', $data->timestamp ).'"';
		$lure[]				= $data->lured; 
	
	//}

}

# Connect to MySQL for trainer level stats
$mysqli = new mysqli(SYS_DB_HOST, SYS_DB_USER, SYS_DB_PSWD, SYS_DB_NAME, SYS_DB_PORT);
if($mysqli->connect_error != ''){exit('Error MySQL Connect');}

$trainer_lvl = [];
# For all 3 teams
for ($teamid = 1; $teamid <= 3; $teamid++) {
	$req    = "SELECT level, count(level) AS count FROM trainer WHERE team = '".$teamid."' GROUP BY level";
	
	if ($result = $mysqli->query($req)) {
		# build level=>count array
		$data = [];
		while ($row = $result->fetch_assoc()) {
			$data[$row["level"]] = $row["count"];
		}

		# fill empty levels counts with 0
		for ($i = 5; $i <= 40; $i++) {
			if (!isset($data[$i])) {
				$data[$i]=0;
			}
		}
		# sort array again
		ksort($data);
		$trainer_lvl[$teamid] = $data;
		
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
            display: true,
            ticks: {
                suggestedMin: 0,    // minimum will be 0, unless there is a lower value.
                // OR //
                beginAtZero: true   // minimum value will be 0.
            }
        }]
    }
};


// Total Spawn Graph
// -----------------

var ctx = $("#total_spawn");

var data = {
    labels: [<?= implode(',', $labels_global) ?>],
    datasets: [
        {
            label: "Total Spawn",
            fill: true,
            lineTension: 0.1,
            backgroundColor: "rgba(75,192,192,0.4)",
            borderColor: "rgba(75,192,192,1)",
            borderCapStyle: 'butt',
            borderDash: [],
            borderDashOffset: 0.0,
            borderJoinStyle: 'miter',
            pointBorderColor: "rgba(75,192,192,1)",
            pointBackgroundColor: "#fff",
            pointBorderWidth: 1,
            pointHoverRadius: 5,
            pointHoverBackgroundColor: "rgba(75,192,192,1)",
            pointHoverBorderColor: "rgba(220,220,220,1)",
            pointHoverBorderWidth: 2,
            pointRadius: 1,
            pointHitRadius: 10,
            data: [<?= implode(',', $total )?>],
            spanGaps: false,
        }
    ]
};

var myLineChart = new Chart(ctx, {
    type: 'line',
    data: data, 
    options : options
});


// Spawn Graph by type
// --------------------


var ctx_vc = $("#very_common");

var data_vc = {
    labels: [<?= implode(',', $labels) ?>],
    datasets: [
        {
            label: "Very Common",
            fill: false,
            lineTension: 0.1,
            backgroundColor: "rgba(175,192,192,0.4)",
            borderColor: "rgba(175,192,192,1)",
            borderCapStyle: 'butt',
            borderDash: [],
            borderDashOffset: 0.0,
            borderJoinStyle: 'miter',
            pointBorderColor: "rgba(175,192,192,1)",
            pointBackgroundColor: "#fff",
            pointBorderWidth: 1,
            pointHoverRadius: 5,
            pointHoverBackgroundColor: "rgba(175,192,192,1)",
            pointHoverBorderColor: "rgba(220,220,220,1)",
            pointHoverBorderWidth: 2,
            pointRadius: 1,
            pointHitRadius: 10,
            data: [<?= implode(',', $veco )?>],
            spanGaps: false,
        }
    ]
};


var myLineChart = new Chart(ctx_vc, {
    type: 'line',
    data: data_vc, 
    options : options
});


var ctx_comm = $("#common");

var data_comm = {
    labels: [<?= implode(',', $labels) ?>],
    datasets: [
        {
            label: "Common",
            fill: false,
            lineTension: 0.1,
            backgroundColor: "rgba(175,192,192,0.4)",
            borderColor: "rgba(175,192,192,1)",
            borderCapStyle: 'butt',
            borderDash: [],
            borderDashOffset: 0.0,
            borderJoinStyle: 'miter',
            pointBorderColor: "rgba(175,192,192,1)",
            pointBackgroundColor: "#fff",
            pointBorderWidth: 1,
            pointHoverRadius: 5,
            pointHoverBackgroundColor: "rgba(175,192,192,1)",
            pointHoverBorderColor: "rgba(220,220,220,1)",
            pointHoverBorderWidth: 2,
            pointRadius: 1,
            pointHitRadius: 10,
            data: [<?= implode(',', $commo )?>],
            spanGaps: false,
        }
    ]
};


var myLineChart = new Chart(ctx_comm, {
    type: 'line',
    data: data_comm, 
    options : options
});

var ctx_rare = $("#rare");

var data_rare = {
    labels: [<?= implode(',', $labels) ?>],
    datasets: [
        {
            label: "Rare",
            fill: false,
            lineTension: 0.1,
            backgroundColor: "rgba(175,192,192,0.4)",
            borderColor: "rgba(175,192,192,1)",
            borderCapStyle: 'butt',
            borderDash: [],
            borderDashOffset: 0.0,
            borderJoinStyle: 'miter',
            pointBorderColor: "rgba(175,192,192,1)",
            pointBackgroundColor: "#fff",
            pointBorderWidth: 1,
            pointHoverRadius: 5,
            pointHoverBackgroundColor: "rgba(175,192,192,1)",
            pointHoverBorderColor: "rgba(220,220,220,1)",
            pointHoverBorderWidth: 2,
            pointRadius: 1,
            pointHitRadius: 10,
            data: [<?= implode(',', $rare )?>],
            spanGaps: false,
        }
    ]
};


var myLineChart = new Chart(ctx_rare, {
    type: 'line',
    data: data_rare, 
    options : options
});



var ctx_myth = $("#mythics");

var data_myth = {
    labels: [<?= implode(',', $labels) ?>],
    datasets: [
        {
            label: "Mythic",
            fill: false,
            lineTension: 0.1,
            backgroundColor: "rgba(175,192,192,0.4)",
            borderColor: "rgba(175,192,192,1)",
            borderCapStyle: 'butt',
            borderDash: [],
            borderDashOffset: 0.0,
            borderJoinStyle: 'miter',
            pointBorderColor: "rgba(175,192,192,1)",
            pointBackgroundColor: "#fff",
            pointBorderWidth: 1,
            pointHoverRadius: 5,
            pointHoverBackgroundColor: "rgba(175,192,192,1)",
            pointHoverBorderColor: "rgba(220,220,220,1)",
            pointHoverBorderWidth: 2,
            pointRadius: 1,
            pointHitRadius: 10,
            data: [<?= implode(',', $myth )?>],
            spanGaps: false,
        }
    ]
};


var myLineChart = new Chart(ctx_myth, {
    type: 'line',
    data: data_myth, 
    options : options
});


// Team Prestige Average
// ---------------------


var team_av = $("#team_av");

var data_av = {
    labels: [<?= implode(',', $labels_gym) ?>],
    datasets: [
        {
            label: "Mystic Prestige Average",
            fill: false,
            lineTension: 0.1,
            backgroundColor: "rgba(59,129,255,0.4)",
            borderColor: "rgba(59,129,255,1)",
            borderCapStyle: 'butt',
            borderDash: [],
            borderDashOffset: 0.0,
            borderJoinStyle: 'miter',
            pointBorderColor: "rgba(59,129,255,1)",
            pointBackgroundColor: "#fff",
            pointBorderWidth: 1,
            pointHoverRadius: 5,
            pointHoverBackgroundColor: "rgba(59,129,255,1)",
            pointHoverBorderColor: "rgba(59,129,255,1)",
            pointHoverBorderWidth: 2,
            pointRadius: 1,
            pointHitRadius: 10,
            data: [<?= implode(',', $mystic_average )?>],
            spanGaps: false,
        }, 
        {
            label: "Valor Prestige Average",
            fill: false,
            lineTension: 0.1,
            backgroundColor: "rgba(247,10,20,0.4)",
            borderColor: "rgba(247,10,20,1)",
            borderCapStyle: 'butt',
            borderDash: [],
            borderDashOffset: 0.0,
            borderJoinStyle: 'miter',
            pointBorderColor: "rgba(247,10,20,1)",
            pointBackgroundColor: "#fff",
            pointBorderWidth: 1,
            pointHoverRadius: 5,
            pointHoverBackgroundColor: "rgba(247,10,20,1)",
            pointHoverBorderColor: "rgba(247,10,20,1)",
            pointHoverBorderWidth: 2,
            pointRadius: 1,
            pointHitRadius: 10,
            data: [<?= implode(',', $valor_average )?>],
            spanGaps: false,
        }, 
        {
            label: "Instinct Prestige Average",
            fill: false,
            lineTension: 0.1,
            backgroundColor: "rgba(248,153,0,0.4)",
            borderColor: "rgba(248,153,0,1)",
            borderCapStyle: 'butt',
            borderDash: [],
            borderDashOffset: 0.0,
            borderJoinStyle: 'miter',
            pointBorderColor: "rgba(248,153,0,1)",
            pointBackgroundColor: "#fff",
            pointBorderWidth: 1,
            pointHoverRadius: 5,
            pointHoverBackgroundColor: "rgba(248,153,0,1)",
            pointHoverBorderColor: "rgba(248,153,0,1)",
            pointHoverBorderWidth: 2,
            pointRadius: 1,
            pointHitRadius: 10,
            data: [<?= implode(',', $instinct_average )?>],
            spanGaps: false,
        }
    ]
};


var myLineChart = new Chart(team_av, {
    type: 'line',
    data: data_av, 
    options : options
});



// Team Gym capture
// ----------------



var team_gym = $("#team_gym");

var data_team_gym = {
    labels: [<?= implode(',', $labels_gym) ?>],
    datasets: [
        {
            label: "Mystic Gym Owned",
            fill: false,
            lineTension: 0.1,
            backgroundColor: "rgba(59,129,255,0.4)",
            borderColor: "rgba(59,129,255,1)",
            borderCapStyle: 'butt',
            borderDash: [],
            borderDashOffset: 0.0,
            borderJoinStyle: 'miter',
            pointBorderColor: "rgba(59,129,255,1)",
            pointBackgroundColor: "#fff",
            pointBorderWidth: 1,
            pointHoverRadius: 5,
            pointHoverBackgroundColor: "rgba(59,129,255,1)",
            pointHoverBorderColor: "rgba(59,129,255,1)",
            pointHoverBorderWidth: 2,
            pointRadius: 1,
            pointHitRadius: 10,
            data: [<?= implode(',', $mystic_owned )?>],
            spanGaps: false,
        },
        {
            label: "Valor Gym Owned",
            fill: false,
            lineTension: 0.1,
            backgroundColor: "rgba(247,10,20,0.4)",
            borderColor: "rgba(247,10,20,1)",
            borderCapStyle: 'butt',
            borderDash: [],
            borderDashOffset: 0.0,
            borderJoinStyle: 'miter',
            pointBorderColor: "rgba(247,10,20,1)",
            pointBackgroundColor: "#fff",
            pointBorderWidth: 1,
            pointHoverRadius: 5,
            pointHoverBackgroundColor: "rgba(247,10,20,1)",
            pointHoverBorderColor: "rgba(247,10,20,1)",
            pointHoverBorderWidth: 2,
            pointRadius: 1,
            pointHitRadius: 10,
            data: [<?= implode(',', $valor_owned )?>],
            spanGaps: false,
        },
        {
            label: "Instinct Gym Owned",
            fill: false,
            lineTension: 0.1,
            backgroundColor: "rgba(248,153,0,0.4)",
            borderColor: "rgba(248,153,0,1)",
            borderCapStyle: 'butt',
            borderDash: [],
            borderDashOffset: 0.0,
            borderJoinStyle: 'miter',
            pointBorderColor: "rgba(248,153,0,1)",
            pointBackgroundColor: "#fff",
            pointBorderWidth: 1,
            pointHoverRadius: 5,
            pointHoverBackgroundColor: "rgba(248,153,0,1)",
            pointHoverBorderColor: "rgba(248,153,0,1)",
            pointHoverBorderWidth: 2,
            pointRadius: 1,
            pointHitRadius: 10,
            data: [<?= implode(',', $instinct_owned )?>],
            spanGaps: false,
        }
    ]
};

var myLineChart = new Chart(team_gym, {
    type: 'line',
    data: data_team_gym, 
    options : options
});




// Pokestop lure
// -------------




var ctx_lure = $("#lures");

var data_lure = {
    labels: [<?= implode(',', $labels_stops) ?>],
    datasets: [
        {
            label: "Pokestop lured",
            fill: true,
            lineTension: 0.1,
            backgroundColor: "rgba(124,0,210,0.4)",
            borderColor: "rgba(124,0,210,1)",
            borderCapStyle: 'butt',
            borderDash: [],
            borderDashOffset: 0.0,
            borderJoinStyle: 'miter',
            pointBorderColor: "rgba(124,0,210,1)",
            pointBackgroundColor: "#fff",
            pointBorderWidth: 1,
            pointHoverRadius: 5,
            pointHoverBackgroundColor: "rgba(124,0,210,1)",
            pointHoverBorderColor: "rgba(124,0,210,1)",
            pointHoverBorderWidth: 2,
            pointRadius: 1,
            pointHitRadius: 10,
            data: [<?= implode(',', $lure )?>],
            spanGaps: false,
        }
    ]
};


var myLineChart = new Chart(ctx_lure, {
    type: 'line',
    data: data_lure, 
    options : options
});



// Trainer level
// -------------

// Hide trainer level graph if there is no result (gym-info not active in PokemonGo-Map)
<?php if (!$trainer_lvl) { ?> document.getElementById('trainer_lvl_graph').style.display = 'none'; <?php } ?>

var trainer_lvl = $("#trainer_lvl");

var data_trainer_lvl = {
    labels: [5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35,36,37,38,39,40],
    datasets: [
        {
            label: "Mystic Trainer Level count",
            backgroundColor: "rgba(59,129,255,0.4)",
            borderColor: "rgba(59,129,255,1)",
            borderCapStyle: 'butt',
            borderDash: [],
            borderDashOffset: 0.0,
            borderJoinStyle: 'miter',
            pointBorderColor: "rgba(59,129,255,1)",
            pointBackgroundColor: "#fff",
            pointBorderWidth: 1,
            pointHoverRadius: 5,
            pointHoverBackgroundColor: "rgba(59,129,255,1)",
            pointHoverBorderColor: "rgba(59,129,255,1)",
            pointHoverBorderWidth: 2,
            pointRadius: 1,
            pointHitRadius: 10,
            data: [<?= implode(',', $trainer_lvl[1] )?>],
        },
        {
            label: "Valor Trainer Level count",
            backgroundColor: "rgba(247,10,20,0.4)",
            borderColor: "rgba(247,10,20,1)",
            borderCapStyle: 'butt',
            borderDash: [],
            borderDashOffset: 0.0,
            borderJoinStyle: 'miter',
            pointBorderColor: "rgba(247,10,20,1)",
            pointBackgroundColor: "#fff",
            pointBorderWidth: 1,
            pointHoverRadius: 5,
            pointHoverBackgroundColor: "rgba(247,10,20,1)",
            pointHoverBorderColor: "rgba(247,10,20,1)",
            pointHoverBorderWidth: 2,
            pointRadius: 1,
            pointHitRadius: 10,
            data: [<?= implode(',', $trainer_lvl[2] )?>],
        },
        {
            label: "Instinct Trainer Level count",
            backgroundColor: "rgba(248,153,0,0.4)",
            borderColor: "rgba(248,153,0,1)",
            borderCapStyle: 'butt',
            borderDash: [],
            borderDashOffset: 0.0,
            borderJoinStyle: 'miter',
            pointBorderColor: "rgba(248,153,0,1)",
            pointBackgroundColor: "#fff",
            pointBorderWidth: 1,
            pointHoverRadius: 5,
            pointHoverBackgroundColor: "rgba(248,153,0,1)",
            pointHoverBorderColor: "rgba(248,153,0,1)",
            pointHoverBorderWidth: 2,
            pointRadius: 1,
            pointHitRadius: 10,
            data: [<?= implode(',', $trainer_lvl[3] )?>],
        }
    ]
};

var myLineChart = new Chart(trainer_lvl, {
    type: 'bar',
    data: data_trainer_lvl,
    options : options
});
