/** global: google */
function initMap()
{
	$('.gym_details').hide();
	//ensure that gmaps is loaded before loading infobox (nasty but usefull trick)
	$.getScript("//rawgit.com/googlemaps/v3-utility-library/master/infobox/src/infobox.js").done(function () {
		$.ajax({
			'async': true,
			'type': "GET",
			'global': false,
			'dataType': 'text',
			'url': "core/process/aru.php",
			'data': { 'request': "", 'target': 'arrange_url', 'method': 'method_target', 'type' : 'gym_map' }}).done(function (data) {
			
			
			// Get website variables
			
				$.getJSON("core/json/variables.json", function ( jsondata ) {
				
					var variables = jsondata;
												
					var lattitude = Number(variables['system']['map_center_lat']);
					var longitude = Number(variables['system']['map_center_long']);
					var zoom_level = Number(variables['system']['zoom_level']);
				
					// Convert return to JSON Array
				
					var locations = JSON.parse(data);
					var arr = [];
				
					for (i = 0; i < locations.length; i++) {
						arr.push(JSON.parse(locations[i]));
					}
					
					var map = new google.maps.Map(document.getElementById('map'), {
						center: {
							lat: lattitude,
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
			
					for (i = 0; i < arr.length; i++) {
						marker = new google.maps.Marker({
							position: new google.maps.LatLng(arr[i][2], arr[i][3]),
							map: map,
							icon: 'core/img/'+arr[i][1],
						});
					
					
						google.maps.event.addListener(marker, 'click', (function (marker, i) {
							return function () {
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
	
	$('#gym_details_template #gymLastModifiedDisplay').html(gym.gymDetails.gymInfos.last_modified);
	var currentTeamColor = 'white';
	if(gym.gymDetails.gymInfos.team=="1") {
		currentTeamColor = 'rgb(0, 170, 255)';
	} else if(gym.gymDetails.gymInfos.team=="2") {
		currentTeamColor = 'rgb(255, 118, 118)';
	} else if(gym.gymDetails.gymInfos.team=="3") {
		currentTeamColor = 'rgb(255, 190, 8)';
	}
	var currentGymPrestige = gym.gymDetails.gymInfos.points;
	formatGyms(currentGymPrestige, currentTeamColor);
	$('#gym_details_template').show();
}

function formatGyms(gymPrestigeValue,teamColor){
	var gymPrestige = gymPrestigeValue;
	var gymRanks = [
	{
		level : 1,
		prestigeMax : 2000,
		prestigeMin : 0
	},
	{
		level : 2,
		prestigeMax : 4000,
		prestigeMin : 2000
	},
	{
		level : 3,
		prestigeMax : 8000,
		prestigeMin : 4000
	},
	{
		level : 4,
		prestigeMax : 12000,
		prestigeMin : 8000
	},
	{
		level : 5,
		prestigeMax : 16000,
		prestigeMin : 12000
	},
	{
		level : 6,
		prestigeMax : 20000,
		prestigeMin : 16000
	},
	{
		level : 7,
		prestigeMax : 30000,
		prestigeMin : 20000
	},
	{
		level : 8,
		prestigeMax : 40000,
		prestigeMin : 30000
	},
	{
		level : 9,
		prestigeMax : 50000,
		prestigeMin : 40000
	},
	{
		level : 10,
		prestigeMax : 52000,
		prestigeMin : 50000
	}
	];
	
	$('#gym_details_template #gymInfos').css("border-color", teamColor);
	//Set rank positions (50000 = 90% for rank 10 to be visible)
	var gymPercent = 50000/90;
	if (gymPrestige>50000) {
		//compensate for last rank
		gymPrestige=(50000+((gymPrestige-50000)*2.775))
	}
	$('.bar-step').removeClass('active');
	for (var i in gymRanks) {
		if (!gymRanks.hasOwnProperty(i)) {
			continue; // Skip keys from the prototype.
		}
		var width = (((gymRanks[i].prestigeMax)-(gymRanks[i].prestigeMin))/gymPercent);
		if(gymRanks[i].level > 9) {
			width = 10;
		}
		var left = (gymRanks[i].prestigeMin/gymPercent);
		var active = (gymPrestige >= gymRanks[i].prestigeMax);
		if(active){
			$('.gymRank'+gymRanks[i].level).addClass('active');
		}
		$('gymRank'+gymRanks[i].level).css({width:width+'%',left:left+'%'});
		
	}
	$('#gym_details_template #gymPrestigeBar').css({'width':((gymPrestige/55550)*100)+'%', 'background-color':teamColor});
}
