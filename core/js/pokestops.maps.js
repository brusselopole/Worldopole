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
                          
				var map = new google.maps.Map(document.getElementById('map'), {
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
                          
              $.get( 'core/js/pogostyle.js', function( data ) {
                    if (data) {
                    pogoStyle = JSON.parse(data);
                    }
                    
                    var styledMap_pogo = new google.maps.StyledMapType(pogoStyle, {name: 'PoGo'});
                    map.mapTypes.set('pogo_style', styledMap_pogo);
                    });
              
              $.get( 'core/js/darkstyle.js', function( data ) {
                    if (data) {
                    darkStyle = JSON.parse(data);
                    }
                    
                    var styledMap_dark = new google.maps.StyledMapType(darkStyle, {name: 'Dark'});
                    map.mapTypes.set('dark_style', styledMap_dark);
                    });
              
              $.get( 'core/js/defaultstyle.js', function( data ) {
                    if (data) {
                    defaultStyle = JSON.parse(data);
                    }
                    
                    map.set('styles',defaultStyle);
                    });
				
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
			
					
			});
		}
	});
}
