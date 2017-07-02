<?php

include_once('config.php');
include_once('functions.php');
include_once('core/process/data.loader.php');

?>

<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">

		<?php include_once('core/inc/meta.inc.php') ?>

		<!-- Bootstrap -->
		<link href="core/css/bootstrap.min.css" rel="stylesheet">
		<link href="https://fonts.googleapis.com/css?family=Lato:400,300,700" rel="stylesheet" type="text/css">
		<link href="core/css/font-awesome.min.css" rel="stylesheet">
		<link href="<?php auto_ver('core/css/style.css'); ?>" rel="stylesheet">
		<?php if ($page == "pokemon") { ?>
			<link href="<?php auto_ver('core/css/jQRangeSlider-bootstrap.min.css'); ?>" rel="stylesheet">
		<?php } ?>
	</head>
	<body id="page-top" data-spy="scroll" data-target=".navbar-fixed-top">

		<?php
		// Google Analytics
		if (is_file("analyticstracking.php")) {
			include_once("analyticstracking.php");
		}
		?>

		<nav class="navbar navbar-default navbar-fixed-top">
			<div class="container">
				<!-- Brand and toggle get grouped for better mobile display -->
				<div class="navbar-header">
					<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#menu" aria-expanded="false">
						<span class="sr-only">Toggle navigation</span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
					</button>
					<a class="navbar-brand" href="<?= HOST_URL ?>"><img src="<?= $config->infos->logo_path ?>" width="25" style="display:inline-block;" alt="<?= $config->infos->site_name ?>" id="logo-img" /> <?= $config->infos->site_name ?></a>
				</div>

				<!-- Collect the nav links, forms, and other content for toggling -->
				<div class="collapse navbar-collapse" id="menu">
					<ul class="nav navbar-nav navbar-right">

						<?php
						if (!isset($config->menu)) {
							echo "Please update variables.json file with menu values";
							exit();
						}

						foreach ($config->menu as $menu) {
							if (isset($menu->locale)) {
								$locale = $menu->locale;
								$text	= $locales->$locale;
							} elseif (isset($menu->text)) {
								$text	= $menu->text;
							}

							switch ($menu->type) {
								case 'link':
									?>

									<li>
										<a href="<?= $menu->href ?>" class="menu-label"><i class="fa <?= $menu->icon ?>" aria-hidden="true"></i> <?= $text ?></a>
									</li>

									<?php
									break;

								case 'link_external':
									?>

									<li>
										<a href="<?= $menu->href ?>" target="_blank" class="menu-label"><i class="fa <?= $menu->icon ?>" aria-hidden="true"></i> <?= $menu->text ?></a>
									</li>

									<?php
									break;

								case 'html':
									?>

									<li> <?= $menu->value ?> </li>

									<?php
									break;
							}
						}
						?>

					</ul>
				</div> <!-- /.navbar-collapse -->
			</div> <!-- /.container-fluid -->
		</nav>

		<div class="container">
			<?php
			# Include the pages
			if (!empty($_GET['page'])) {
				$file = SYS_PATH.'/pages/'.$page.'.page.php';

				if (is_file($file)) {
					echo '<!-- Page :: '.$page.' -->';
					include($file);
				} else {
					include('pages/home.page.php');
				}
			} else {
				include('pages/home.page.php');
			}

			?>
		</div>

		<footer>
			<div class="container">
				<div class="row">
					<div class="col-md-12 text-center">
						<img src="core/img/logo.png" width=50 class="big-icon" alt="Brusselopole icon">
						<h2><?= $locales->FOOTER_TITLE ?></h2>
						<p><?= $locales->FOOTER_SUB ?></p>
						<?= $locales->FOOTER_VISUAL_CONTENT ?>
						<p><?= $locales->FOOTER_MADE_BY ?></p>
						<h3>Pokémon™</h3>
						<?= $locales->FOOTER_POKEMON_CONTENT ?>
					</div>
				</div>
			</div>
		</footer>

		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
		<script src="core/js/bootstrap.min.js"></script>

		<?php // Load scripts only for page
		if (empty($page)) { ?>

			<script src="<?php auto_ver('core/js/home.script.js') ?>"></script>

			<script>
				updateCounter(<?= $home->pokemon_now ?>,'.total-pkm-js');
				updateCounter(<?= $home->pokestop_lured ?>,'.total-lure-js');
				updateCounter(<?= $home->gyms ?>,'.total-gym-js');

				updateCounter(<?= $home->teams->valor ?>,'.total-valor-js');
				updateCounter(<?= $home->teams->mystic ?>,'.total-mystic-js');
				updateCounter(<?= $home->teams->instinct ?>,'.total-instinct-js');
				updateCounter(<?= $home->teams->rocket ?>,'.total-rocket-js');
			</script>
		<?php
		} else {
			switch ($page) {
				case 'pokemon':
					?>

					<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.5.0/Chart.min.js"></script>
					<script src="core/js/pokemon.graph.js.php?id=<?= $pokemon_id ?>"></script>

					<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
					<script src="<?php auto_ver('core/js/jQAllRangeSliders-withRuler.min.js') ?>"></script>
					<script src="<?php auto_ver('core/js/pokemon.maps.js') ?>"></script>
					<script>
						var pokemon_id = <?= $pokemon_id ?>;
					</script>
					<script src="https://maps.googleapis.com/maps/api/js?key=<?= $config->system->GMaps_Key ?>&libraries=visualization&callback=initMap"></script>

					<?php
					break;

				case 'pokestops':
					?>

					<script src="<?php auto_ver('core/js/pokestops.maps.js') ?>"></script>
					<script src="https://maps.googleapis.com/maps/api/js?key=<?= $config->system->GMaps_Key ?>&libraries=visualization&callback=initMap"></script>

					<?php
					break;

				case 'gym':
					?>

					<script src="<?php auto_ver('core/js/gym.script.js') ?>"></script>
					<script>
						updateCounter(<?= $teams->valor->gym_owned ?>,'.gym-valor-js');
						updateCounter(<?= $teams->valor->average ?>,'.average-valor-js');

						updateCounter(<?= $teams->instinct->gym_owned ?>,'.gym-instinct-js');
						updateCounter(<?= $teams->instinct->average ?>,'.average-instinct-js');

						updateCounter(<?= $teams->mystic->gym_owned ?>,'.gym-mystic-js');
						updateCounter(<?= $teams->mystic->average ?>,'.average-mystic-js');
					</script>

					<script src="<?php auto_ver('core/js/gym.maps.js') ?>"></script>
					<script src="https://maps.googleapis.com/maps/api/js?key=<?= $config->system->GMaps_Key ?>&libraries=visualization&callback=initMap"></script>

					<?php
					break;

				case 'pokedex':
					?>

					<script src="core/js/holmes.min.js"></script>
					<script>
						// holmes setup
						var h = new holmes({
							input: '.search input',
							find: '.results .pokemon-single',
							placeholder: '<h3>— No results, my dear Ash. —</h3>',
							class: {
								visible: 'visible',
								hidden: 'hidden'
							}
						});
					</script>

					<?php
					break;

				case 'dashboard':
					?>

					<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.5.0/Chart.min.js"></script>
					<script src="core/js/dashboard.graph.js.php"></script>

					<?php
					break;

				case 'trainer':
					?>

					<script src="<?php auto_ver('core/js/trainer.content.js') ?>"></script>
					<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.5.0/Chart.min.js"></script>
					<script src="core/js/trainer.graph.js.php"></script>

					<?php
					break;

				case 'nests':
					?>

					<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.countdown/2.2.0/jquery.countdown.min.js"></script>
					<script src="core/js/nests.maps.js.php"></script>
					<script src="https://maps.googleapis.com/maps/api/js?key=<?= $config->system->GMaps_Key ?>&libraries=visualization&callback=initMap"></script>

					<?php
					break;

				case 'raids':
					?>

					<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.countdown/2.2.0/jquery.countdown.min.js"></script>
					<script src="<?php auto_ver('core/js/raids.content.js') ?>"></script>

					<?php
					break;
			}
		}
		?>

	</body>
</html>
