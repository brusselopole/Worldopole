/** global: google */
/** global: navigator */

function initMap()
{
	var pokestopOpts = {
		'type': "GET",
		'global': false,
		'dataType': 'json',
		'url': "core/process/aru.php",
		'data': { 'request': "", 'target': 'arrange_url', 'method': 'method_target', 'type' : 'pokestop' }
	};
	var geoOpts = {
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
	}
	$.when($.ajax(pokestopOpts), $.ajax(geoOpts)).then(function (response1, response2) {
		var pokestops = response1[0];
		var coordinates = response2[0];
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

			var infowindow = new google.maps.InfoWindow();
		
			var markers = [];
	
			for (i = 0; i < pokestops.length; i++) {
				marker = new google.maps.Marker({
					position: new google.maps.LatLng(pokestops[i][2], pokestops[i][3]),
					icon: 'core/img/'+pokestops[i][1]
				});
	
				google.maps.event.addListener(marker, 'click', (function (marker, i) {
						return function () {
							infowindow.setContent(pokestops[i][0]);
							infowindow.open(map, marker);
						}
				})(marker, i));
				if (pokestops[i][1].lastIndexOf('lured') !== -1) {
					marker.setMap(map);
					marker.setAnimation(google.maps.Animation.BOUNCE);
				} else {					
					markers.push(marker);
				}
			}
			
			var clusterOptions = {
				gridSize: 80,
				minimumClusterSize: 4,
				cssClass: 'pokeStopCluster'
			}
			markerCluster = new MarkerClusterer(map, [], clusterOptions);			
			markerCluster.addMarkers(markers);
		});
	});
}
