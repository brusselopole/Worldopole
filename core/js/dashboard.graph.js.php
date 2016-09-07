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
		
		$instinct_average[] 	= $data->team->instinct->average;
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
            label: "<?= $locales->DASHBOARD_SPAWN_TOTAL->$lang ?>",
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
            label: "<?= $locales->DASHBOARD_VERYCOMMON->$lang ?>",
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
            label: "<?= $locales->DASHBOARD_COMMON->$lang ?>",
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
            label: "<?= $locales->DASHBOARD_RARE->$lang ?>",
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
            label: "<?= $locales->DASHBOARD_MYTHIC->$lang ?>",
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
            label: "<?= $locales->DASHBOARD_GRAPH_MYSTIC_PRESTIGE_AVERAGE->$lang ?>",
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
            label: "<?= $locales->DASHBOARD_GRAPH_VALOR_PRESTIGE_AVERAGE->$lang ?>",
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
            label: "<?= $locales->DASHBOARD_GRAPH_INSTINCT_PRESTIGE_AVERAGE->$lang ?>",
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
            label: "<?= $locales->DASHBOARD_GRAPH_MYSTIC_GYM_OWNED->$lang ?>",
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
            label: "<?= $locales->DASHBOARD_GRAPH_VALOR_GYM_OWNED->$lang ?>",
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
            label: "<?= $locales->DASHBOARD_GRAPH_INSTINCT_GYM_OWNED->$lang ?>",
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
            label: "<?= $locales->DASHBOARD_GRAPH_LURED_POKESTOPS->$lang ?>",
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
