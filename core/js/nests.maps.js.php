<?php
	# Send Javascript header
	header('Content-type: text/javascript');

	# Load variables and locales
	include_once('../../config.php');
	$variables = SYS_PATH.'/core/json/variables.json';
	$config = json_decode(file_get_contents($variables));
	include_once('../process/locales.loader.php');

	# Load nests-file
	$nest_file = file_get_contents('../json/nests.stats.json');
	$nests = json_decode($nest_file, true);
?>

/** global: google */

function initMap() {
	var lattitude = Number(<?= $config->system->map_center_lat ?>);
	var longitude = Number(<?= $config->system->map_center_long ?>);
	var zoom_level = Number(<?= $config->system->zoom_level ?>);
	var pokeimg_suffix = "<?= $config->system->pokeimg_suffix ?>";

	map = new google.maps.Map(document.getElementById('map'), {
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

	var infoWindow = new google.maps.InfoWindow({pixelOffset: new google.maps.Size(0, 8)});

	var nestData = getNests(pokeimg_suffix);
	var nestImage = getImages(pokeimg_suffix);
	var nestInfo = getInfo();

	for (var i = 0; i <= nestData.length; i++) {
		var marker = new google.maps.Marker({
						position: nestData[i],
						map: map,
						icon: nestImage[i]
					});

		google.maps.event.addListener(marker, 'click', (function(marker, i) {
			return function() {
				infoWindow.setContent(nestInfo[i]);
				infoWindow.open(map, marker);
				infoWindow.isClickOpen = true;
			}})(marker, i));

		google.maps.event.addListener(marker, 'mouseover', (function(marker, i) {
			return function() {
				infoWindow.setContent(nestInfo[i]);
				infoWindow.open(map, marker);
				infoWindow.isClickOpen = false;
			}})(marker, i));

		marker.addListener('mouseout', function() {
			if(infoWindow.isClickOpen === false){
				infoWindow.close();
			}
		});
	}

};


function getNests(pokeimg_suffix) {
	return [
		<?php
			foreach ($nests as $data) { ?>
				new google.maps.LatLng(<?= $data['latitude'] ?>, <?= $data['longitude'] ?>),
		<?php
			} ?>
	];
}


function getImages(pokeimg_suffix) {
	return[
		<?php
			foreach ($nests as $data) { ?>
				image = {
					url: 'core/pokemons/'+<?= $data['pokemon_id'] ?>+pokeimg_suffix,
					scaledSize: new google.maps.Size(32, 32),
					origin: new google.maps.Point(0,0),
					anchor: new google.maps.Point(16, 16),
					labelOrigin : new google.maps.Point(16, 36)
				},
		<?php
			} ?>
	];
}


function getInfo() {
	return[
		<?php
			foreach ($nests as $data) {
				$pokeid = $data['pokemon_id']; ?>
				contentString = '<div id="content">'+
						'<div id="siteNotice">'+
						'</div>'+
						'<div id="bodyContent">'+
						'<p><b><?= $pokemons->pokemon->$pokeid->name ?></b>: <?= $data['total_pokemon'] ?> <?= $locales->NESTS_PER_DAY ?> </p>' +
						'</div>',
		<?php
			} ?>
	];
}
