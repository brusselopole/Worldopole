/** global: google */
/** global: navigator */
/** global: InfoBox */

function initMap()
{
	$('.gym_details').hide();
	//ensure that gmaps is loaded before loading infobox (nasty but usefull trick)
	$.getScript("//cdn.rawgit.com/googlemaps/v3-utility-library/master/infobox/src/infobox.js").done(function () {
		$.ajax({
			'async': true,
			'type': "GET",
			'global': false,
			'dataType': 'json',
			'url': "core/process/aru.php",
			'data': { 'request': "", 'target': 'arrange_url', 'method': 'method_target', 'type' : 'gym_map' }}).done(function (gyms) {

				// Get website variables

				$.getJSON("core/json/variables.json", function (variables) {
					var latitude = Number(variables['system']['map_center_lat']);
					var longitude = Number(variables['system']['map_center_long']);
					var zoom_level = Number(variables['system']['zoom_level']);

					var map = new google.maps.Map(document.getElementById('map'), {
						center: {
							lat: latitude,
							lng: longitude
						},
						zoom: zoom_level,
						zoomControl: true,
						scaleControl: false,
						scrollwheel: true,
						disableDoubleClickZoom: false,
						streetViewControl: false,
						mapTypeControlOptions: {
							mapTypeIds: [
								google.maps.MapTypeId.ROADMAP,
								'pogo_style',
								'dark_style',
							]
						}
					});

					$.getJSON( 'core/json/pogostyle.json', function( data ) {
						var styledMap_pogo = new google.maps.StyledMapType(data, {name: 'PoGo'});
						map.mapTypes.set('pogo_style', styledMap_pogo);
					});
					$.getJSON( 'core/json/darkstyle.json', function( data ) {
						var styledMap_dark = new google.maps.StyledMapType(data, {name: 'Dark'});
						map.mapTypes.set('dark_style', styledMap_dark);
					});
					$.getJSON( 'core/json/defaultstyle.json', function( data ) {
						map.set('styles', data);
					});

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
							'type': 'maps_localization_coordinates'
						}
					}).done(function(coordinates) {
						if (navigator.geolocation) {
							navigator.geolocation.getCurrentPosition(function(position) {
								var pos = {
									lat: position.coords.latitude,
									lng: position.coords.longitude
								};

								if (position.coords.latitude <= coordinates.max_latitude && position.coords.latitude >= coordinates.min_latitude) {
									if (position.coords.longitude <= coordinates.max_longitude && position.coords.longitude >= coordinates.min_longitude) {
										map.setCenter(pos);
									}
								}
							});
						}
					});

					var infowindow = new InfoBox({
						content: document.getElementById("gym_details_template"),
						disableAutoPan: false,
						pixelOffset: new google.maps.Size(-35, 30),
						zIndex: null,
						closeBoxURL: "",
						infoBoxClearance: new google.maps.Size(1, 1)
					});

					google.maps.event.addListener(map, "click", function () {
						infowindow.close();
					});

					var marker, i;

					for (i = 0; i < gyms.length; i++) {
						marker = new google.maps.Marker({
							position: new google.maps.LatLng(gyms[i][1], gyms[i][2]),
							map: map,
							icon: 'core/img/'+gyms[i][0],
						});


						google.maps.event.addListener(marker, 'click', (function (marker, i) {
							return function () {
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
										'gym_id' : gyms[i][3]
									},
									'success': function (data) {
										setGymDetails(data);
										infowindow.setContent($('#gym_details_template').html());
										infowindow.open(map, marker);
									}
								});
							}
						})(marker, i));
					}
				});
			});
	});
}

function setGymDetails(gym)
{
	// replace http with https to fix mixed content
	var imgurl = gym.gymDetails.gymInfos.url;
	imgurl = imgurl.replace(/^http:\/\//i, 'https://');
	$('#gym_details_template #circleImage').css("background", "url("+imgurl+") no-repeat center");
	$('#gym_details_template #gymName').html(gym.gymDetails.gymInfos.name);
	$('#gym_details_template #gymDescription').html(gym.gymDetails.gymInfos.description);
	$('#gym_details_template #gymDefenders').html(gym.infoWindow);
	$('#gym_details_template #gymPrestigeDisplay').html(gym.gymDetails.gymInfos.points);
	$('#gym_details_template #gymLastScannedDisplay').html(gym.gymDetails.gymInfos.last_scanned);
	var currentTeamColor = 'white';
	if(gym.gymDetails.gymInfos.team=="1") {
		currentTeamColor = 'rgb(0, 170, 255)';
	} else if(gym.gymDetails.gymInfos.team=="2") {
		currentTeamColor = 'rgb(255, 118, 118)';
	} else if(gym.gymDetails.gymInfos.team=="3") {
		currentTeamColor = 'rgb(255, 190, 8)';
	}
	$('#gym_details_template #gymInfos').css("border-color", currentTeamColor);
	$('#gym_details_template').show();
}
