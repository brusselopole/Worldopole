function initMap() {
	var locations;
	$('.gym_details').hide();
	//ensure that gmaps is loaded before loading infobox (nasty but usefull trick) 
	$.getScript("//rawgit.com/googlemaps/v3-utility-library/master/infobox/src/infobox.js").done(function(){
		$.ajax({
		'async': true,
		'type': "GET",
		'global': false,
		'dataType': 'text',
		'url': "core/process/aru.php",
		'data': { 'request': "", 'target': 'arrange_url', 'method': 'method_target', 'type' : 'gym_map' }}
		).done(function (data) {
			
			
			// Get website variables 
			
			$.getJSON( "core/json/variables.json", function( jsondata ) {
				
				var variables = jsondata; 
												
				var lattitude = Number(variables['system']['map_center_lat']); 
				var longitude = Number(variables['system']['map_center_long']);
				var zoom_level = Number(variables['system']['zoom_level']); 
				
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
						zoom: zoom_level,
						zoomControl: true,
						scaleControl: false,
						scrollwheel: true,
						disableDoubleClickZoom: false,
				});
				
				var infowindow = new InfoBox({
					content: document.getElementById("gym_details_template"),
					disableAutoPan: false,
					pixelOffset: new google.maps.Size(-35, 30),
					zIndex: null,
					closeBoxURL: "",
					infoBoxClearance: new google.maps.Size(1, 1)
				});
				
				google.maps.event.addListener(map, "click", function(event) {
					infowindow.close();
				});
				
				var marker, i;
			
				for (i = 0; i < arr.length; i++) { 
				 
					marker = new google.maps.Marker({
						position: new google.maps.LatLng(arr[i][2], arr[i][3]),
						map: map, 
						icon: 'core/img/'+arr[i][1],
					});
					
					
					google.maps.event.addListener(marker, 'click', (function(marker, i) {
						return function() {
							infowindow.setContent(arr[i][0]);
							infowindow.open(map, marker);
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
		});
	});
	var locations;

	
}

function setGymDetails(gym) {
	// replace http with https to fix mixed content
	var imgurl = gym.gymDetails.gymInfos.url;
	imgurl = imgurl.replace(/^http:\/\//i, 'https://');
	$('#gym_details_template #circleImage').css("background", "url("+imgurl+") no-repeat center");
	$('#gym_details_template #gymName').html(gym.gymDetails.gymInfos.name);
	$('#gym_details_template #gymDescription').html(gym.gymDetails.gymInfos.description);
	$('#gym_details_template #gymDefenders').html(gym.infoWindow);
	$('#gym_details_template #gymPrestigeDisplay').html(gym.gymDetails.gymInfos.points);
	
	$('#gym_details_template #gymLastModifiedDisplay').html(gym.gymDetails.gymInfos.last_modified);
	var teamColor = gym.gymDetails.gymInfos.team == "1" ? 'rgb(0, 170, 255)':gym.gymDetails.gymInfos.team == "2" ? 'rgb(255, 118, 118)':gym.gymDetails.gymInfos.team == "3" ? 'rgb(255, 190, 8)':'white';
	$('#gym_details_template #gymInfos').css("border-color", teamColor);
	var gymPrestige = gym.gymDetails.gymInfos.points;
	//Set rank positions (50000 = 90% for rank 10 to be visible)
	var gymPercent = 50000/90;
	$('.bar-step').removeClass('active');
	$('.gymRank1').css({width:(2000/gymPercent)+'%',left:'0'}).addClass(gymPrestige>0?'active':'');
	$('.gymRank2').css({width:((4000/gymPercent)-(2000/gymPercent))+'%',left:(2000/gymPercent)+'%'}).addClass((gymPrestige>2000)?'active':'');
	$('.gymRank3').css({width:((8000/gymPercent)-(4000/gymPercent))+'%',left:(4000/gymPercent)+'%'}).addClass((gymPrestige>4000)?'active':'');
	$('.gymRank4').css({width:((12000/gymPercent)-(8000/gymPercent))+'%',left:(8000/gymPercent)+'%'}).addClass((gymPrestige>8000)?'active':'');
	$('.gymRank5').css({width:((16000/gymPercent)-(12000/gymPercent))+'%',left:(12000/gymPercent)+'%'}).addClass((gymPrestige>12000)?'active':'');
	$('.gymRank6').css({width:((20000/gymPercent)-(16000/gymPercent))+'%',left:(16000/gymPercent)+'%'}).addClass((gymPrestige>16000)?'active':'');
	$('.gymRank7').css({width:((30000/gymPercent)-(20000/gymPercent))+'%',left:(20000/gymPercent)+'%'}).addClass((gymPrestige>20000)?'active':'');
	$('.gymRank8').css({width:((40000/gymPercent)-(30000/gymPercent))+'%',left:(30000/gymPercent)+'%'}).addClass((gymPrestige>30000)?'active':'');
	$('.gymRank9').css({width:((50000/gymPercent)-(40000/gymPercent))+'%',left:(40000/gymPercent)+'%'}).addClass((gymPrestige>40000)?'active':'');
	$('.gymRank10').css({width:'10%',left:(50000/gymPercent)+'%'}).addClass((gymPrestige>50000)?'active':'');
	
	if(gymPrestige>50000){
		//compensate for last rank 
		gymPrestige=(50000+((gymPrestige-50000)*2.775))
	}
	
	$('#gym_details_template #gymPrestigeBar').css({'width':((gymPrestige/55550)*100)+'%', 'background-color':teamColor});
	$('#gym_details_template').show();
}
