function initMap()
{

	var locations;

	$.ajax({
		'async': true,
		'type': "GET",
		'global': false,
		'dataType': 'text',
		'url': "core/process/aru.php",
		'data': { 'request': "", 'target': 'arrange_url', 'method': 'method_target', 'type' : 'pokestop' },
		'success': function (data) {
			
		
			$.getJSON("core/json/variables.json", function ( jsondata ) {
				
				var variables = jsondata;
											
				var lattitude = Number(variables['system']['map_center_lat']);
				var longitude = Number(variables['system']['map_center_long']);
				var zoom_level = Number(variables['system']['zoom_level']);

		 
				// Convert return to JSON Array
			
				locations = JSON.parse(data);
				var arr = [];
			
				for (i = 0; i < locations.length; i++) {
					arr.push(JSON.parse(locations[i]));
				}
			
                var darkStyle = [{'featureType': 'all','elementType': 'labels.text.fill','stylers':
                                    	[{'saturation': 36},{'color': '#b39964'},{'lightness': 40}]},
                                    {'featureType': 'all','elementType': 'labels.text.stroke','stylers':
                                    	[{'visibility': 'on'},{'color': '#000000'},{'lightness': 16}]},
                                    {'featureType': 'all','elementType': 'labels.icon','stylers':
                                        [{'visibility': 'off'}]},
                                    {'featureType': 'administrative','elementType': 'geometry.fill','stylers':
                                        [{'color': '#000000'},{'lightness': 20}]},
                                    {'featureType': 'administrative','elementType': 'geometry.stroke','stylers':
                                        [{'color': '#000000'},{'lightness': 17},{'weight': 1.2}]},
                                    {'featureType': 'landscape','elementType': 'geometry','stylers':
                                        [{'color': '#000000'},{'lightness': 20}]},
                                    {'featureType': 'poi','elementType': 'geometry','stylers':
                                        [{'color': '#000000'},{'lightness': 21}]},
                                    {'featureType': 'road.highway','elementType': 'geometry.fill','stylers':
                                        [{'color': '#000000'},{'lightness': 17}]},
                                    {'featureType': 'road.highway','elementType': 'geometry.stroke','stylers':
                                        [{'color': '#000000'},{'lightness': 29},{'weight': 0.2}]},
                                    {'featureType': 'road.arterial','elementType': 'geometry','stylers':
                                        [{'color': '#000000'},{'lightness': 18}]},
                                    {'featureType': 'road.local','elementType': 'geometry','stylers':
                                        [{'color': '#181818'},{'lightness': 16}]},
                                    {'featureType': 'transit','elementType': 'geometry','stylers':
                                        [{'color': '#000000'},{'lightness': 19}]},
                                    {'featureType': 'water','elementType': 'geometry','stylers':
                                        [{'lightness': 17},{'color': '#525252'}]}]
					
				var pogoStyle = [{'featureType': 'landscape.man_made','elementType': 'geometry.fill','stylers':
                                    	[{'color': '#a1f199'}]},
                                    {'featureType': 'landscape.natural.landcover','elementType': 'geometry.fill','stylers':
                                    	[{'color': '#37bda2'}]},
                                    {'featureType': 'landscape.natural.terrain','elementType': 'geometry.fill','stylers':
                                    	[{'color': '#37bda2'}]},
                                    {'featureType': 'poi.attraction','elementType': 'geometry.fill','stylers':
                                    	[{'visibility': 'on'}]},
                                    {'featureType': 'poi.business','elementType': 'geometry.fill','stylers':
                                    	[{'color': '#e4dfd9'}]},
                                    {'featureType': 'poi.business','elementType': 'labels.icon','stylers':
                                    	[{'visibility': 'off'}]},
                                    {'featureType': 'poi.park','elementType': 'geometry.fill','stylers':
                                    	[{'color': '#37bda2'}]},
                                    {'featureType': 'road','elementType': 'geometry.fill','stylers':
                                    	[{'color': '#84b09e'}]},
                                    {'featureType': 'road','elementType': 'geometry.stroke','stylers':
                                    	[{'color': '#fafeb8'}, {'weight': '1.25'}]},
                                    {'featureType': 'road.highway','elementType': 'labels.icon','stylers':
                                    	[{'visibility': 'off'}]},
                                    {'featureType': 'water','elementType': 'geometry.fill','stylers':
                                    	[{'color': '#5ddad6'}]}]
                          
				map = new google.maps.Map(document.getElementById('map'), {
						center: {lat: lattitude, lng: longitude},
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
                                                               ]}
					});
                          
                var styledMap_dark = new google.maps.StyledMapType(darkStyle, {name: 'Dark'});
                map.mapTypes.set('dark_style', styledMap_dark);
					
				var styledMap_pogo = new google.maps.StyledMapType(pogoStyle, {name: 'PoGo'});
                map.mapTypes.set('pogo_style', styledMap_pogo);
				
                var infowindow = new google.maps.InfoWindow();
			
				var marker, i;
		
				for (i = 0; i < arr.length; i++) {
					marker = new google.maps.Marker({
						position: new google.maps.LatLng(arr[i][2], arr[i][3]),
						map: map,
						icon: 'core/img/'+arr[i][1]
					});
		
					google.maps.event.addListener(marker, 'click', (function (marker, i) {
							return function () {
								infowindow.setContent(arr[i][0]);
								infowindow.open(map, marker);
							}
					})(marker, i));
				}
			
			
				map.set('styles',[{"featureType":"all","elementType":"labels.text.fill","stylers":[{"saturation":36},{"color":"#333333"},{"lightness":40}]},{"featureType":"all","elementType":"labels.text.stroke","stylers":[{"visibility":"on"},{"color":"#ffffff"},{"lightness":16}]},{"featureType":"all","elementType":"labels.icon","stylers":[{"visibility":"off"}]},{"featureType":"administrative","elementType":"geometry.fill","stylers":[{"color":"#fefefe"},{"lightness":20}]},{"featureType":"administrative","elementType":"geometry.stroke","stylers":[{"color":"#fefefe"},{"lightness":17},{"weight":1.2}]},{"featureType":"landscape","elementType":"geometry","stylers":[{"color":"#f5f5f5"},{"lightness":20}]},{"featureType":"poi","elementType":"geometry","stylers":[{"color":"#f5f5f5"},{"lightness":21}]},{"featureType":"poi.park","elementType":"geometry","stylers":[{"color":"#dedede"},{"lightness":21}]},{"featureType":"poi.park","elementType":"geometry.fill","stylers":[{"color":"#c2ffd7"}]},{"featureType":"road.highway","elementType":"geometry.fill","stylers":[{"color":"#ffffff"},{"lightness":17}]},{"featureType":"road.highway","elementType":"geometry.stroke","stylers":[{"color":"#ffffff"},{"lightness":29},{"weight":0.2}]},{"featureType":"road.arterial","elementType":"geometry","stylers":[{"color":"#ffffff"},{"lightness":18}]},{"featureType":"road.local","elementType":"geometry","stylers":[{"color":"#ffffff"},{"lightness":16}]},{"featureType":"transit","elementType":"geometry","stylers":[{"color":"#f2f2f2"},{"lightness":19}]},{"featureType":"water","elementType":"geometry","stylers":[{"color":"#e9e9e9"},{"lightness":17}]},{"featureType":"water","elementType":"geometry.fill","stylers":[{"color":"#b3d8f9"}]}]);
			
					
			});
		}
	});
}
