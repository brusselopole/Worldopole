<header id="single-header">
	<div class="row">
		<div class="col-md-12 text-center">
			<h1>
				<?= $config->infos->site_title ?>
				<br>
				<small><?= sprintf($config->infos->site_claim, $config->infos->city); ?></small>
			</h1>

		</div>
	</div>
</header>

<div class="row area">

	<div class="col-md-3 col-sm-6 col-xs-12 big-data"> <!-- LIVEMON -->
		<a href="pokemon">
			<img src="core/img/pokeball.png" alt="Visit the <?= $config->infos->site_name ?> Pokedex" width=50 class="big-icon">
			<p><big><strong class="total-pkm-js">0</strong> Pokémon</big><br>
			<?= sprintf($locales->WIDGET_POKEMON_SUB, $config->infos->city); ?></p>
		</a>
	</div>

	<div class="col-md-3 col-sm-6 col-xs-12 big-data" style="border-right:1px lightgray solid;border-left:1px lightgray solid;"> <!-- GYMS -->
		<a href="gym">
			<img src="core/img/rocket.png" alt="Discover the <?= $config->infos->site_name ?> Gyms" width=50 class="big-icon">
			<p><big><strong class="total-gym-js">0</strong> <?= $locales->GYMS ?></big><br>
			<?= $locales->WIDGET_GYM_SUB ?></p>
		</a>

	</div>

	<div class="col-md-3 col-sm-6 col-xs-12 big-data" style="border-right:1px lightgray solid;"> <!-- POKESTOPS -->
		<a href="pokestops">
			<img src="core/img/lure-module.png" alt="Discover the <?= $config->infos->site_name ?> Pokéstops" width=50 class="big-icon">
			<p><big><strong class="total-lure-js">0</strong> <?= $locales->LURES ?></big><br>
			<?= sprintf($locales->WIDGET_LURES_SUB, $config->infos->city); ?></p>
		</a>
	</div>

	<div class="col-md-3 col-sm-6 col-xs-12 big-data">
		<a href="<?= $config->homewidget->url ?>" target="_blank">
			<img src="<?= $config->homewidget->image ?>" alt="<?= $config->homewidget->image_alt ?>" width=50 class="big-icon">
			<p><?= $config->homewidget->text ?></p>
		</a>
	</div>

</div>


<div class="row area big-padding">
	<div class="col-md-12 text-center">
		<h2 class="text-center sub-title">
			<?php 
			if ($config->system->recents_filter) { ?>
				<?= $locales->RECENT_MYTHIC_SPAWNS ?>
			<?php 
			} else { ?>
				<?= $locales->RECENT_SPAWNS ?>
			<?php 
			} ?>
		</h2>
		<div class="last-mon-js">
		<?php
		foreach ($recents as $key => $pokemon) {
			$id = $pokemon->id;
			$uid = $pokemon->uid; ?>
			<div class="col-md-1 col-xs-4 pokemon-single" data-pokeid="<?= $id ?>" data-pokeuid="<?= $uid ?>" >
				<a href="pokemon/<?= $id ?>"><img src="core/pokemons/<?= $id.$config->system->pokeimg_suffix ?>" alt="<?= $pokemons->pokemon->$id->name ?>" class="img-responsive"></a>
				<a href="pokemon/<?= $id ?>"><p class="pkmn-name"><?= $pokemons->pokemon->$id->name ?></p></a>
				<a href="https://maps.google.com/?q=<?= $pokemon->last_location->latitude ?>,<?= $pokemon->last_location->longitude ?>&ll=<?= $pokemon->last_location->latitude ?>,<?= $pokemon->last_location->longitude ?>&z=16" target="_blank">
					<small class="pokemon-timer">00:00:00</small>
				</a>
				<?php
				if ($config->system->recents_show_iv) {
					if ($pokemon->iv->available) {
						if ($config->system->iv_numbers) { ?>
							<div class="progress" style="height: 15px; margin-bottom: 0">
								<div title="Attack IV: <?= $pokemon->iv->attack ?>" class="progress-bar progress-bar-danger" role="progressbar" aria-valuenow="<?= $pokemon->iv->attack ?>" aria-valuemin="0" aria-valuemax="45" style="width: <?= (100/3)  ?>%; line-height: 16px">
									<span class="sr-only"><?= $locales->ATTACK ?> IV: <?= $pokemon->iv->attack ?></span><?= $pokemon->iv->attack ?>
								</div>
								<div title="Defense IV: <?= $pokemon->iv->defense ?>" class="progress-bar progress-bar-info" role="progressbar" aria-valuenow="<?= $pokemon->iv->defense ?>" aria-valuemin="0" aria-valuemax="45" style="width: <?= (100/3)  ?>%; line-height: 16px">
									<span class="sr-only"><?= $locales->DEFENSE ?> IV: <?= $pokemon->iv->defense ?></span><?= $pokemon->iv->defense ?>
								</div>
								<div title="Stamina IV: <?= $pokemon->iv->stamina ?>" class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="<?= $pokemon->iv->stamina ?>" aria-valuemin="0" aria-valuemax="45" style="width: <?= (100/3) ?>%; line-height: 16px">
									<span class="sr-only"><?= $locales->STAMINA ?> IV: <?= $pokemon->iv->stamina ?></span><?= $pokemon->iv->stamina ?>
								</div>
							</div>
						<?php 
						} else { ?>
							<div class="progress" style="height: 6px; width: 80%; margin: 5px auto 0 auto;">
								<div title="Attack IV: <?= $pokemon->iv->attack ?>" class="progress-bar progress-bar-danger" role="progressbar" aria-valuenow="<?= $pokemon->iv->attack ?>" aria-valuemin="0" aria-valuemax="45" style="width: <?= ((100/15)*$pokemon->iv->attack)/3 ?>%">
									<span class="sr-only"><?= $locales->ATTACK ?> IV: <?= $pokemon->iv->attack ?></span>
								</div>
								<div title="Defense IV: <?= $pokemon->iv->defense ?>" class="progress-bar progress-bar-info" role="progressbar" aria-valuenow="<?= $pokemon->iv->defense ?>" aria-valuemin="0" aria-valuemax="45" style="width: <?= ((100/15)*$pokemon->iv->defense)/3 ?>%">
									<span class="sr-only"><?= $locales->DEFENSE ?> IV: <?= $pokemon->iv->defense ?></span>
								</div>
								<div title="Stamina IV: <?= $pokemon->iv->stamina ?>" class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="<?= $pokemon->iv->stamina ?>" aria-valuemin="0" aria-valuemax="45" style="width: <?= ((100/15)*$pokemon->iv->stamina)/3 ?>%">
									<span class="sr-only"><?= $locales->STAMINA ?> IV: <?= $pokemon->iv->stamina ?></span>
								</div>
							</div>
					<?php
						} ?>
						<small><?= $pokemon->iv->cp ?></small>
					<?php
					} else {
						if ($config->system->iv_numbers) { ?>
							<div class="progress" style="height: 15px; margin-bottom: 0">
								<div title="Attack IV: not available" class="progress-bar progress-bar-danger" role="progressbar" aria-valuenow="<?= $pokemon->iv->attack ?>" aria-valuemin="0" aria-valuemax="45" style="width: <?= (100/3)  ?>%; line-height: 16px">
									<span class="sr-only"><?= $locales->ATTACK ?> IV: <?= $locales->NOT_AVAILABLE ?></span>?
								</div>
								<div title="Defense IV: not available" class="progress-bar progress-bar-info" role="progressbar" aria-valuenow="<?= $pokemon->iv->defense ?>" aria-valuemin="0" aria-valuemax="45" style="width: <?= (100/3)  ?>%; line-height: 16px">
									<span class="sr-only"><?= $locales->DEFENSE ?> IV: <?= $locales->NOT_AVAILABLE ?></span>?
								</div>
								<div title="Stamina IV: not available" class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="<?= $pokemon->iv->stamina ?>" aria-valuemin="0" aria-valuemax="45" style="width: <?= (100/3) ?>%; line-height: 16px">
									<span class="sr-only"><?= $locales->STAMINA ?> IV: <?= $locales->NOT_AVAILABLE ?></span>?
								</div>
							</div>
						<?php 
						} else { ?>
						<div class="progress" style="height: 6px; width: 80%; margin: 5px auto 0 auto;">
							<div title="IV not available" class="progress-bar" role="progressbar" style="width: 100%; background-color: rgb(210,210,210)" aria-valuenow="1" aria-valuemin="0" aria-valuemax="1">
								<span class="sr-only">IV <?= $locales->NOT_AVAILABLE ?></span>
							</div>
						</div>
					<?php
						} ?>
						<small>???</small>
					<?php
					}
				} ?>
				</div>
			<?php
			// Array with ids and countdowns to start at the end of this file
			$timers[$uid] = $pokemon->last_seen - time();
		} ?>
		</div>
	</div>
</div>


<div class="row big padding">
	<h2 class="text-center sub-title"><?= $locales->FIGHT_TITLE ?></h2>

		<?php 
			foreach ($home->teams as $team => $total) {
				if ($home->teams->rocket) { ?>

					<div class="col-md-3 col-sm-6 col-sm-12 team">
						<div class="row">
							<div class="col-xs-12 col-sm-12">
								<p style="margin-top:0.5em;text-align:center;"><img src="core/img/<?= $team ?>.png" alt="Team <?= $team ?>" class="img-responsive" style="display:inline-block" width=80> <strong class="total-<?= $team ?>-js">0</strong> <?= $locales->GYMS ?></p>

				<?php
				} else { ?>

					<div class="col-md-4 col-sm-6 col-sm-12 team">
						<div class="row">
							<div class="col-xs-12 col-sm-12">
								<?php
									if ($team != 'rocket') { ?>
										<p style="margin-top:0.5em;text-align:center;"><img src="core/img/<?= $team ?>.png" alt="Team <?= $team ?>" class="img-responsive" style="display:inline-block" width=80> <strong class="total-<?= $team ?>-js">0</strong> <?= $locales->GYMS ?></p>
										<?php
									}
				} ?>
							</div>
						</div>
					</div>					
				<?php
			} ?>
</div>


<script>
	document.addEventListener('DOMContentLoaded', function() {
		<?php
		foreach (array_reverse($timers) as $id => $countdown) { ?>
			startTimer(<?= $countdown ?>,"<?= $id ?>");
		<?php
		} ?>
	}, false);
</script>
