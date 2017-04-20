<?php
	# Send Javascript header
	header('Content-type: text/javascript');

	# Load variables and locales
	include_once('../../config.php');
	$variables = SYS_PATH.'/core/json/variables.json';
	$config = json_decode(file_get_contents($variables));
	include_once('../process/locales.loader.php');
?>

/** global: google */

var pokemon = {
<?php
	foreach ($pokemons->pokemon as $pokeid => $pokemon) {
		echo $pokeid.':"'.$pokemon->name.'",';
	}
?>
}

function initMap() {

	$.getJSON("core/json/variables.json", function (variables) {
		var latitude = Number(variables.system.map_center_lat);
		var longitude = Number(variables.system.map_center_long);
		var zoom_level = Number(variables.system.zoom_level);
		var pokeimg_suffix = variables.system.pokeimg_suffix;

		map = new google.maps.Map(document.getElementById('map'), {
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

		var infoWindow = new google.maps.InfoWindow({pixelOffset: new google.maps.Size(0, 8)});

		// load data
		$.getJSON("core/json/nests.stats.json", function(nestData) {
			
			for (var i = 0; i < nestData.length; i++) {
				var marker = new google.maps.Marker({
					position: new google.maps.LatLng(nestData[i].lat, nestData[i].lng),
					map: map,
					icon: getImage(nestData[i], pokeimg_suffix)
				});

				google.maps.event.addListener(marker, 'click', (function(marker, i) {
					return function() {
						infoWindow.setContent(getInfo(nestData[i]));
						infoWindow.open(map, marker);
						infoWindow.isClickOpen = true;
				}})(marker, i));

				google.maps.event.addListener(marker, 'mouseover', (function(marker, i) {
					return function() {
						infoWindow.setContent(getInfo(nestData[i]));
						infoWindow.open(map, marker);
						infoWindow.isClickOpen = false;
				}})(marker, i));

				marker.addListener('mouseout', function() {
					if(infoWindow.isClickOpen === false){
						infoWindow.close();
					}
				});
		
			}
		});
	});
};


function getImage(data, pokeimg_suffix) {
	var image = {
		url: 'core/pokemons/' + data.pid + pokeimg_suffix,
		scaledSize: new google.maps.Size(32, 32),
		origin: new google.maps.Point(0,0),
		anchor: new google.maps.Point(16, 16),
		labelOrigin : new google.maps.Point(16, 36)
	}
	return image
}


function getInfo(data) {
	var info = 	'<div id="content">' +
			'<div id="bodyContent">' +
			'<p><b>' + pokemon[data.pid] + '</b>: ' + data.c + ' <?= $locales->NESTS_PER_DAY ?> </p>' +
			'</div>' +
			'</div>'
	return info
}


Date.prototype.addDays = function(days) {
	var d = new Date(this.valueOf());
	d.setDate(d.getDate() + days);
	return d;
}

$(function () {
	var migration = new Date('2017-05-04T00:00:00Z');
	while (migration < new Date()) migration = migration.addDays(14);
	$('#migration').countdown(migration, { precision: 60000 }).on('update.countdown', function(event) {
		$(this).html(event.strftime('%w %!w:<?= $locales->WEEK ?>,<?= $locales->WEEKS ?>; %d %!d:<?= $locales->DAY ?>,<?= $locales->DAYS ?>; %H %!H:<?= $locales->HOUR ?>,<?= $locales->HOURS ?>; %M %!M:<?= $locales->MINUTE ?>,<?= $locales->MINUTES ?>;'));
	}).countdown('start');
});