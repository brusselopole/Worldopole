/** global: google */
/** global: pokemon_id */
var map, heatmap;
var pokemonMarkers = [];
var updateLiveTimeout;

var ivMin = 80;
var ivMax = 100;

function initMap() {
	
	$.getJSON( "core/json/variables.json", function( jsondata ) {

		var variables = jsondata;

		var lattitude = Number(variables['system']['map_center_lat']);
		var longitude = Number(variables['system']['map_center_long']);
		var zoom_level = Number(variables['system']['zoom_level']);
		var pokeimg_suffix=jsondata['system']['pokeimg_suffix'];

		map = new google.maps.Map(document.getElementById('map'), {
			center: {lat: lattitude, lng: longitude},
			zoom: zoom_level,
			zoomControl: true,
			scaleControl: false,
			scrollwheel: true,
			disableDoubleClickZoom: false,
		});
		
		map.set('styles',[
			{
				"featureType":"all",
				"elementType":"labels.text.fill",
				"stylers":[{"saturation":36},{"color":"#333333"},{"lightness":40}]
			},
			{
				"featureType":"all",
				"elementType":"labels.text.stroke",
				"stylers":[{"visibility":"on"},{"color":"#ffffff"},{"lightness":16}]
			},
			{
				"featureType":"all",
				"elementType":"labels.icon",
				"stylers":[{"visibility":"off"}]
			},
			{
				"featureType":"administrative",
				"elementType":"geometry.fill",
				"stylers":[{"color":"#fefefe"},{"lightness":20}]
			},
			{
				"featureType":"administrative",
				"elementType":"geometry.stroke",
				"stylers":[{"color":"#fefefe"},{"lightness":17},{"weight":1.2}]
			},
			{
				"featureType":"landscape",
				"elementType":"geometry",
				"stylers":[{"color":"#f5f5f5"},{"lightness":20}]
			},
			{
				"featureType":"poi",
				"elementType":"geometry",
				"stylers":[{"color":"#f5f5f5"},{"lightness":21}]
			},
			{
				"featureType":"poi.park",
				"elementType":"geometry",
				"stylers":[{"color":"#dedede"},{"lightness":21}]
			},
			{
				"featureType":"poi.park",
				"elementType":"geometry.fill",
				"stylers":[{"color":"#c2ffd7"}]
			},
			{
				"featureType":"road.highway",
				"elementType":"geometry.fill",
				"stylers":[{"color":"#ffffff"},{"lightness":17}]},
			{
				"featureType":"road.highway",
				"elementType":"geometry.stroke",
				"stylers":[{"color":"#ffffff"},{"lightness":29},{"weight":0.2}]},
			{
				"featureType":"road.arterial",
				"elementType":"geometry",
				"stylers":[{"color":"#ffffff"},{"lightness":18}]},
			{
				"featureType":"road.local",
				"elementType":"geometry",
				"stylers":[{"color":"#ffffff"},{"lightness":16}]},
			{
				"featureType":"transit",
				"elementType":"geometry",
				"stylers":[{"color":"#f2f2f2"},{"lightness":19}]},
			{
				"featureType":"water",
				"elementType":"geometry",
				"stylers":[{"color":"#e9e9e9"},{"lightness":17}]},
			{
				"featureType":"water",
				"elementType":"geometry.fill",
				"stylers":[{"color":"#b3d8f9"}]
			}
		]);
		initHeatmap();
		initSelector(pokeimg_suffix);
		});

}

function initSelector(pokeimg_suffix){
	$('#heatmapSelector').click(function(){
		hideLive();
		showHeatmap();
		$('#heatmapSelector').addClass('active');
		$('#liveSelector').removeClass('active');
	});
	$('#liveSelector').click(function(){
		hideHeatmap();
		initLive(pokeimg_suffix);
		
		
		$('#liveSelector').addClass('active');
		$('#heatmapSelector').removeClass('active');
	});
}

function initLive(pokeimg_suffix){
	showLive();
	$("#liveFilterSelector").rangeSlider({
		bounds:{
			min: 0,
			max: 100
		},
		defaultValues:{
			min: ivMin,
			max: ivMax
		}
	});
	
	$("#liveFilterSelector").bind("valuesChanged",function(e, data){
		clearTimeout(updateLiveTimeout);
		removePokemonMarkerByIv(data.values.min,data.values.max);
		ivMin = data.values.min;
		ivMax = data.values.max;
		updateLive(pokeimg_suffix);
	});
	updateLive(pokeimg_suffix);
	
}

function initHeatmap(){
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
			'type' : 'pokemon_slider_init'
		}
	}).done(function(bounds){
		initHeatmapData(bounds);
	});
	
}

function initHeatmapData(bounds){
	var boundMin = new Date(bounds.min.replace(/-/g, "/"));
	var boundMax = new Date(bounds.max.replace(/-/g, "/"));
	var selectorMax = boundMax;
	var selectorMin = boundMin;

	var maxMinus2Weeks = new Date(selectorMax.getTime() - 2 * 7 * 24 * 60 * 60 * 1000);
	if(selectorMin < maxMinus2Weeks){
		selectorMin = maxMinus2Weeks;
	}
	$("#timeSelector").dateRangeSlider({
		bounds:{
			min: boundMin,
			max: boundMax
		},
		defaultValues:{
			min: selectorMin,
			max: selectorMax
		}
	});
	createHeatmap();

}

function createHeatmap() {
	
	heatmap = new google.maps.visualization.HeatmapLayer({
		data: [],
		map: map
	});

	var gradient = [
		'rgba(0, 255, 255, 0)',
		'rgba(0, 255, 255, 1)',
		'rgba(0, 191, 255, 1)',
		'rgba(0, 127, 255, 1)',
		'rgba(0, 63, 255, 1)',
		'rgba(0, 0, 255, 1)',
		'rgba(0, 0, 223, 1)',
		'rgba(0, 0, 191, 1)',
		'rgba(0, 0, 159, 1)',
		'rgba(0, 0, 127, 1)',
		'rgba(63, 0, 91, 1)',
		'rgba(127, 0, 63, 1)',
		'rgba(191, 0, 31, 1)',
		'rgba(255, 0, 0, 1)'
	];
	heatmap.set('gradient', gradient);
	heatmap.setMap(map);
	$("#timeSelector").bind("valuesChanged",function(){updateHeatmap()});
	$("#timeSelector").dateRangeSlider("min"); // will trigger valuesChanged
}

function updateHeatmap() {
	var dateMin = $("#timeSelector").dateRangeSlider("min");
	var dateMax = $("#timeSelector").dateRangeSlider("max");
	$("#loaderContainer").show();
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
			'type' : 'pokemon_heatmap_points',
			'pokemon_id' : pokemon_id,
			'start' : Math.floor(dateMin.getTime()/1000),
			'end' : Math.floor(dateMax.getTime()/1000)
		}
	}).done(function(points){
		var googlePoints = [];
		for (var i = 0; i < points.length; i++) {
			googlePoints.push(new google.maps.LatLng(points[i].latitude,points[i].longitude))
		}
		var newPoints = new google.maps.MVCArray(googlePoints);
		heatmap.set('data', newPoints);
		$("#loaderContainer").hide();
	});
}

function hideHeatmap() {
	$("#timeFilterContainer").hide();
	heatmap.set('map', null);
}

function showHeatmap() {
	$("#timeFilterContainer").show();
	heatmap.set('map', map);
	hideLive();
}

function hideLive() {
	$("#liveFilterContainer").hide();
	clearTimeout(updateLiveTimeout);
	clearPokemonMarkers();
}

function showLive() {
	hideHeatmap();
	clearTimeout(updateLiveTimeout);
	$("#liveFilterContainer").show();
	
}

function updateLive(pokeimg_suffix){
	$.ajax({
		'async': true,
		'type': "POST",
		'global': false,
		'dataType': 'json',
		'url': "core/process/aru.php",
		'data': {
			'request': "",
			'target': 'arrange_url',
			'method': 'method_target',
			'type' : 'pokemon_live',
			'pokemon_id' : pokemon_id,
			'inmap_pokemons' : extractEncountersId(),
			'ivMin' : ivMin,
			'ivMax' : ivMax
		}
	}).done(function(pokemons){
		for (var i = 0; i < pokemons.length; i++) {
			addPokemonMarker(pokemons[i],pokeimg_suffix)
		}
		updateLiveTimeout=setTimeout(function(){ updateLive(pokeimg_suffix) },5000);
	});
}

function addPokemonMarker(pokemon,pokeimg_suffix) {
	var image = {
		url:'core/pokemons/'+pokemon.pokemon_id+pokeimg_suffix,
		scaledSize: new google.maps.Size(32, 32),
		origin: new google.maps.Point(0,0),
		anchor: new google.maps.Point(16, 16),
		labelOrigin : new google.maps.Point(16, 36)
	};
	var ivPercent = ((100/45)*(parseInt(pokemon.individual_attack)+parseInt(pokemon.individual_defense)+parseInt(pokemon.individual_stamina))).toFixed(2);
	var marker = new google.maps.Marker({
		position: {lat: parseFloat(pokemon.latitude), lng:parseFloat(pokemon.longitude)},
		map: map,
		icon: image,
		label:{
			color:getIvColor(ivPercent),
			text:ivPercent+"%"
		},
		encounterId: pokemon.encounter_id,
		ivPercent: ivPercent
	});
	var ivFormatted = ((100/45)*(parseInt(pokemon.individual_attack)+parseInt(pokemon.individual_defense)+parseInt(pokemon.individual_stamina))).toFixed(2);
	var contentString = '<div>'+
			'<h4> '+pokemon.name+' #'+pokemon.pokemon_id+' IV: '+ivFormatted+'% </h4>'+
			'<div id="bodyContent">'+
				'<p class="disappear_time_display text-center">'+pokemon.disappear_time_real+'<span class="disappear_time_display_timeleft"></span></p>'+
				
				'<p></p>'+
				'<div class="progress" style="height: 6px; width: 120px; margin-bottom: 10px; margin-top: 2px; margin-left: auto; margin-right: auto">'+
					'<div title="Attack IV: '+pokemon.individual_attack+'" class="progress-bar progress-bar-danger" role="progressbar" aria-valuenow="'+pokemon.individual_attack+'" aria-valuemin="0" aria-valuemax="45" style="width: '+(((100/15)*pokemon.individual_attack)/3)+'%">'+
						'<span class="sr-only">Attack IV: '+pokemon.individual_attack+'</span>'+
					'</div>'+
					'<div title="Defense IV: '+pokemon.individual_defense+'" class="progress-bar progress-bar-info" role="progressbar" aria-valuenow="'+pokemon.individual_defense+'" aria-valuemin="0" aria-valuemax="45" style="width: '+(((100/15)*pokemon.individual_defense)/3)+'%">'+
						'<span class="sr-only">Defense IV: '+pokemon.individual_defense+'</span>'+
					'</div>'+
					'<div title="Stamina IV: '+pokemon.individual_stamina+'" class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="'+pokemon.individual_stamina+'" aria-valuemin="0" aria-valuemax="45" style="width: '+(((100/15)*pokemon.individual_stamina)/3)+'%">'+
						'<span class="sr-only">Stamina IV: '+pokemon.individual_stamina+'</span>'+
					'</div>'+
				'</div>'+
				'<p class="text-center">('+pokemon.individual_attack+"/"+pokemon.individual_defense+"/"+pokemon.individual_stamina+')</p>'+
			'</div>'+
		'</div>';

	var infoWindow = new google.maps.InfoWindow({
		content: contentString
	});
	infoWindow.isClickOpen = false;
	marker.addListener('click', function() {
		infoWindow.isClickOpen = true;
		infoWindow.open(map, this);
	});
	google.maps.event.addListener(infoWindow,'closeclick',function(){
		this.isClickOpen = false;
	});
	marker.addListener('mouseover', function() {
		infoWindow.open(map, this);
	});

	// assuming you also want to hide the infowindow when user mouses-out
	marker.addListener('mouseout', function() {
		if(infoWindow.isClickOpen === false){
			infoWindow.close();
		}
	});
	pokemonMarkers.push(marker);
	var now = new Date().getTime();
	var endTime = new Date(pokemon.disappear_time_real.replace(/-/g, "/")).getTime();
	
	setTimeout(function(){ removePokemonMarker(pokemon.encounter_id) },endTime-now);
}


function getIvColor(ivPercent){
	var ivColor="rgba(0, 0, 0, 0)";
	if(ivPercent>80){
		ivColor="rgba(0, 0, 255, 0.70)";
	}
	if(ivPercent>90){
		ivColor="rgba(246, 178, 107,  0.90)";
	}
	if(ivPercent>99){
		ivColor="rgba(255, 0, 0, 1)";
	}
	return ivColor;
}

function clearPokemonMarkers() {
	for (var i = 0; i < pokemonMarkers.length; i++) {
		pokemonMarkers[i].setMap(null);
	}
	pokemonMarkers = [];
}
function removePokemonMarker(encounter_id) {
	for (var i = 0; i < pokemonMarkers.length; i++) {
		if(pokemonMarkers[i].encounterId == encounter_id){
			pokemonMarkers[i].setMap(null);
			pokemonMarkers.splice(i,1);
			break;
		}
		
	}
	
}

function removePokemonMarkerByIv(ivMin,ivMax) {
	var cleanMarkers=[];
	for (var i = 0; i < pokemonMarkers.length; i++) {
		if(pokemonMarkers[i].ivPercent < ivMin || pokemonMarkers[i].ivPercent > ivMax){
			pokemonMarkers[i].setMap(null);
		}
		else{
			cleanMarkers.push(pokemonMarkers[i]);
		}
		
	}
	pokemonMarkers = cleanMarkers;
}

function extractEncountersId(){
	var inmapEncounter = [];
	for (var i = 0; i < pokemonMarkers.length; i++) {
		inmapEncounter[i]=pokemonMarkers[i].encounterId;
	}
	
	return inmapEncounter;
}