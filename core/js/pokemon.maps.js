/** global: google */
/** global: pokemon_id */
var map, heatmap;

function initMap() {
	
	$.getJSON( "core/json/variables.json", function( jsondata ) {

		var variables = jsondata;

		var lattitude = Number(variables['system']['map_center_lat']);
		var longitude = Number(variables['system']['map_center_long']);
		var zoom_level = Number(variables['system']['zoom_level']);


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
		initSlider();
		});

}
function initSlider(){
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
	});
	
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
	updateHeatmap();
	$("#timeSelector").bind("valuesChanged",function(){updateHeatmap()});
}

function updateHeatmap() {
	var dateMin = $("#timeSelector").dateRangeSlider("min");
	var dateMax = $("#timeSelector").dateRangeSlider("max");
	
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
	});
}