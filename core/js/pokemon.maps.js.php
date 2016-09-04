<?php

# Test to check if the file is called properly 

if(!isset($_GET['id'])){
	http_response_code(400);
	echo 'Bad Request';
	exit(); 
}

# Send Javascript header 
header('Content-type: text/javascript');

# Load Config 
include_once('../../config.php');


# Connect MySQL 
$mysqli = new mysqli(SYS_DB_HOST, SYS_DB_USER, SYS_DB_PSWD, SYS_DB_NAME, SYS_DB_PORT);
if($mysqli->connect_error != ''){exit('Error MySQL Connect');}

# Heatmap datas  
$pokemon_id = mysqli_real_escape_string($mysqli,$_GET['id']);


?>



var map, heatmap;

function initMap() {
	
	$.getJSON( "core/json/variables.json", function( jsondata ) {
				
		var variables = jsondata; 
  				  				  		
  		var lattitude = Number(variables['system']['map_center_lat']); 
  		var longitude = Number(variables['system']['map_center_long']);
  		var zomm_level = Number(variables['system']['zomm_level']);
  		
	  		
		map = new google.maps.Map(document.getElementById('map'), {
			center: {lat: lattitude, lng: longitude},
		    zoom: zomm_level,
		    zoomControl: true,
			scaleControl: false,
			scrollwheel: true,
			disableDoubleClickZoom: false,
		});
		
		heatmap = new google.maps.visualization.HeatmapLayer({
		  data: getPoints(),
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
		
		
		map.set('styles',[{"featureType":"all","elementType":"labels.text.fill","stylers":[{"saturation":36},{"color":"#333333"},{"lightness":40}]},{"featureType":"all","elementType":"labels.text.stroke","stylers":[{"visibility":"on"},{"color":"#ffffff"},{"lightness":16}]},{"featureType":"all","elementType":"labels.icon","stylers":[{"visibility":"off"}]},{"featureType":"administrative","elementType":"geometry.fill","stylers":[{"color":"#fefefe"},{"lightness":20}]},{"featureType":"administrative","elementType":"geometry.stroke","stylers":[{"color":"#fefefe"},{"lightness":17},{"weight":1.2}]},{"featureType":"landscape","elementType":"geometry","stylers":[{"color":"#f5f5f5"},{"lightness":20}]},{"featureType":"poi","elementType":"geometry","stylers":[{"color":"#f5f5f5"},{"lightness":21}]},{"featureType":"poi.park","elementType":"geometry","stylers":[{"color":"#dedede"},{"lightness":21}]},{"featureType":"poi.park","elementType":"geometry.fill","stylers":[{"color":"#c2ffd7"}]},{"featureType":"road.highway","elementType":"geometry.fill","stylers":[{"color":"#ffffff"},{"lightness":17}]},{"featureType":"road.highway","elementType":"geometry.stroke","stylers":[{"color":"#ffffff"},{"lightness":29},{"weight":0.2}]},{"featureType":"road.arterial","elementType":"geometry","stylers":[{"color":"#ffffff"},{"lightness":18}]},{"featureType":"road.local","elementType":"geometry","stylers":[{"color":"#ffffff"},{"lightness":16}]},{"featureType":"transit","elementType":"geometry","stylers":[{"color":"#f2f2f2"},{"lightness":19}]},{"featureType":"water","elementType":"geometry","stylers":[{"color":"#e9e9e9"},{"lightness":17}]},{"featureType":"water","elementType":"geometry.fill","stylers":[{"color":"#b3d8f9"}]}]);
		
		});

}
		
		
		function getPoints() {
		return [
		  
		  <?php
		  
		  // As the map is rendering by the client, we do recommand to keep a limit on your request. 
		  // 10k is already alotof datas ;) 
		  
		  $req 	= "SELECT * FROM pokemon WHERE pokemon_id = '".$pokemon_id."' ORDER BY disappear_time DESC LIMIT 0,10000";
		  $result = $mysqli->query($req);   
		  	      
		  while($data = $result->fetch_object()){
			  
		      		        
		  ?>
		  
		   new google.maps.LatLng(<?= $data->latitude ?>, <?= $data->longitude ?>),
		  
		  <?php }?>
		  
		 
		];
		
	
}