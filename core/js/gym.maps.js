function initMap() {
	$('.gym_details').hide();
	//ensure that gmaps is loaded before loading infobox (nasty but usefull trick) 
	$.getScript("//rawgit.com/googlemaps/v3-utility-library/master/infobox/src/infobox.js",function(){
		$.ajax({
		'async': false,
		'type': "GET",
		'global': false,
		'dataType': 'text',
		'url': "core/process/aru.php",
		'data': { 'request': "", 'target': 'arrange_url', 'method': 'method_target', 'type' : 'gym_map' },
		'success': function (data) {
			
			
			// Get website variables 
			
			$.getJSON( "core/json/variables.json", function( jsondata ) {
				
				var variables = jsondata; 
												
				var lattitude = Number(variables['system']['map_center_lat']); 
				var longitude = Number(variables['system']['map_center_long']);
				var zomm_level = Number(variables['system']['zomm_level']); 
				
				// Convert return to JSON Array 
				
				locations = jQuery.parseJSON(data);
				var arr = new Array();
				
				for (i = 0; i < locations.length; i++) { 
					activite = jQuery.parseJSON(locations[i]);
					//console.log(activite);
					arr.push(activite);	 
				} 
				
				
				var map = new google.maps.Map(document.getElementById('map'), {
					center: {lat: lattitude, lng: longitude},
						zoom: zomm_level,
						zoomControl: true,
						scaleControl: false,
						scrollwheel: true,
						disableDoubleClickZoom: false,
				});
				
				var infowindow = new InfoBox({
					content: document.getElementById("gym_details_template"),
					disableAutoPan: false,
					maxWidth: 350,
					pixelOffset: new google.maps.Size(-150, 0),
					zIndex: null,
					boxStyle: {
								background: "",
								opacity: 0.85,
								width: "325px",
						},
					closeBoxMargin: "12px 4px 2px 2px",
					closeBoxURL: "http://www.google.com/intl/en_us/mapfiles/close.gif",
					infoBoxClearance: new google.maps.Size(1, 1)
				});
				
				var marker, i;
			
				for (i = 0; i < arr.length; i++) { 
				 
					marker = new google.maps.Marker({
						position: new google.maps.LatLng(arr[i][2], arr[i][3]),
						map: map, 
						icon: 'core/img/'+arr[i][1]
					});
			
					google.maps.event.addListener(marker, 'click', (function(marker, i) {
						return function() {
							infowindow.setContent(arr[i][0]);
							infowindow.open(map, marker);
							$.ajax({
								'async': false,
								'type': "GET",
								'global': false,
								'dataType': 'json',
								'url': "core/process/aru.php",
								'data': { 
									'request': "", 
									'target': 'arrange_url',
									'method': 'method_target',
									'type' : 'gym_defenders',
									'gym_id' : arr[i][5] 
								},
								'success': function (data) {
									setGymDetails(data);
									infowindow.setContent($('#gym_details_template').html());
								}
							});
						}
					})(marker, i));
				
				}
				map.set('styles',[{"featureType":"all","elementType":"labels.text.fill","stylers":[{"saturation":36},{"color":"#333333"},{"lightness":40}]},{"featureType":"all","elementType":"labels.text.stroke","stylers":[{"visibility":"on"},{"color":"#ffffff"},{"lightness":16}]},{"featureType":"all","elementType":"labels.icon","stylers":[{"visibility":"off"}]},{"featureType":"administrative","elementType":"geometry.fill","stylers":[{"color":"#fefefe"},{"lightness":20}]},{"featureType":"administrative","elementType":"geometry.stroke","stylers":[{"color":"#fefefe"},{"lightness":17},{"weight":1.2}]},{"featureType":"landscape","elementType":"geometry","stylers":[{"color":"#f5f5f5"},{"lightness":20}]},{"featureType":"poi","elementType":"geometry","stylers":[{"color":"#f5f5f5"},{"lightness":21}]},{"featureType":"poi.park","elementType":"geometry","stylers":[{"color":"#dedede"},{"lightness":21}]},{"featureType":"poi.park","elementType":"geometry.fill","stylers":[{"color":"#c2ffd7"}]},{"featureType":"road.highway","elementType":"geometry.fill","stylers":[{"color":"#ffffff"},{"lightness":17}]},{"featureType":"road.highway","elementType":"geometry.stroke","stylers":[{"color":"#ffffff"},{"lightness":29},{"weight":0.2}]},{"featureType":"road.arterial","elementType":"geometry","stylers":[{"color":"#ffffff"},{"lightness":18}]},{"featureType":"road.local","elementType":"geometry","stylers":[{"color":"#ffffff"},{"lightness":16}]},{"featureType":"transit","elementType":"geometry","stylers":[{"color":"#f2f2f2"},{"lightness":19}]},{"featureType":"water","elementType":"geometry","stylers":[{"color":"#e9e9e9"},{"lightness":17}]},{"featureType":"water","elementType":"geometry.fill","stylers":[{"color":"#b3d8f9"}]}]);
			});
		}
	});
	
	});
	var locations;

	
}

function setGymDetails(gym) {
	$('#gym_details_template #circleImage').css("background", "url("+gym.gymDetails.gymInfos.url+") no-repeat center");
	$('#gym_details_template #gymName').html(gym.gymDetails.gymInfos.name);
	$('#gym_details_template #gymDescription').html(gym.gymDetails.gymInfos.description);
	$('#gym_details_template #gymLevelDisplay').html(gym.gymDetails.gymInfos.level);
	$('#gym_details_template #gymDefenders').html(gym.infoWindow);
	$('#gym_details_template #gymPrestigeDisplay').html(gym.gymDetails.gymInfos.points);
	$('#gym_details_template #gymLastModifiedDisplay').html(gym.gymDetails.gymInfos.last_modified);
	var teamColor = gym.gymDetails.gymInfos.team == "1" ? 'blue':gym.gymDetails.gymInfos.team == "2" ? 'red':gym.gymDetails.gymInfos.team == "3" ? 'yellow':'white';
	$('#gym_details_template #gymInfos').css("border-color", teamColor);
	$('#gym_details_template #gymDefenders').css("border-color", teamColor);
	
	$('#gym_details_template').show();
}