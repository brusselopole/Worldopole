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


<div class="row area big-padding"> <!-- LAST 12 POKEMONS -->
    <div class="col-md-12 text-center">
        <h2 class="text-center sub-title"><?= $locales->RECENT_SPAWNS ?></h2>
        <div class="last-mon-js">
        <?php foreach ($recents as $recent) {
            $id = $recent->id; ?>
            <div class="col-md-1 col-xs-4 pokemon-single" data-pokeid="<?= $id ?>">
                <a href="pokemon/<?= $id ?>"><img src="core/pokemons/<?= $id ?>.png" alt="<?= $pokemons->pokemon->$id->name ?>" class="img-responsive"></a>
                <a href="pokemon/<?= $id ?>"><p class="pkmn-name"><?= $pokemons->pokemon->$id->name ?></p></a>
                <a href="https://maps.google.com/?q=<?= $recent->last_location->latitude ?>,<?= $recent->last_location->longitude ?>&ll=<?= $recent->last_location->latitude ?>,<?= $recent->last_location->longitude ?>&z=15" target="_blank">
                    <?= time_ago($recent->last_seen, $locales) ?>
                </a>
                <?php
                    if ($recent->iv->percentage > 0) {
                        echo '<p><strong>IV: '.round($recent->iv->percentage).' %</strong></p>';
                    } else {
                        echo '<p><strong>IV:</strong> <code>?</code></p>';
                    }
                    $html =	'
                            <div class="progress" style="height: 6px">
                                <div title="IV Stamina: '. $recent->iv->stamina .'" class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="'. $recent->iv->stamina .'" aria-valuemin="0" aria-valuemax="45" style="width: '. ((100/15)*$recent->iv->stamina)/3 .'%">
                                    <span class="sr-only">Stamina IV : '. $recent->iv->stamina .'</span>
                                </div>
                                <div title="IV attack: '. $recent->iv->attack .'" class="progress-bar progress-bar-danger" role="progressbar" aria-valuenow="'. $recent->iv->attack .'" aria-valuemin="0" aria-valuemax="45" style="width: '. ((100/15)*$recent->iv->attack)/3 .'%">
                                    <span class="sr-only">attack IV : '. $recent->iv->attack .'</span>
                                </div>
                                <div title="IV defense: '. $recent->iv->defense .'" class="progress-bar progress-bar-info" role="progressbar" aria-valuenow="'. $recent->iv->defense .'" aria-valuemin="0" aria-valuemax="45" style="width: '. ((100/15)*$recent->iv->defense)/3 .'%">
                                    <span class="sr-only">defense IV : '. $recent->iv->defense .'</span>
                                </div>
                            </div>
                            ';
    
                    echo 	$html;
                ?>
            </div>
        <?php } ?>
        </div>
    </div>

</div>


<div class="row big padding">
	<h2 class="text-center sub-title"><?= $locales->FIGHT_TITLE ?></h2>

	<?php foreach ($home->teams as $team => $total) { ?>

		<div class="col-md-3 col-sm-6 col-sm-12 team">

			<div class="row">
				<div class="col-xs-12 col-sm-12">
						<p style="margin-top:0.5em;text-align:center;"><img src="core/img/<?= $team ?>.png" alt="Team <?= $team ?>" class="img-responsive" style="display:inline-block" width=80> <strong class="total-<?= $team ?>-js">0</strong> <?= $locales->GYMS ?></p>
				</div>
			</div>
		
		</div>	

	<?php }?>
			
</div>
