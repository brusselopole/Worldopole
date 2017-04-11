<header id="single-header">
	<div class="row">
		<div class="col-md-12 text-center">
			<h1>
				<?= $locales->POKEDEX_TITLE ?>
			</h1>
			<h3>
				<?= sprintf($locales->POKEDEX_TOTAL, number_format($total, 0, ".", " "), $config->infos->city) ?>
			</h3>
		</div>
	</div>
</header>

<div class="row">

	<div class="col-md-12">

		<div class="search form-group">
			<input type="search" class="form-control" placeholder="<?= $locales->POKEDEX_SEARCH ?>" required>
		</div>

	</div>

	<div class="col-md-12 flex-container results">


		<?php foreach ($pokedex as $pokemon) { ?>

			<div class="flex-item pokemon-single">

				<a href="<?= $pokemon->permalink ?>"><img src="<?= $pokemon->img ?>" alt="<?= $pokemon->name ?>" class="img-responsive <?php if ($pokemon->spawn == 0) {
					echo 'unseen';
} ?> "></a>
                <?php if ($config->system->pokedex_numbers) { ?>
                    <p class="pkmn-name"><a href="<?= $pokemon->permalink ?>">#<?= sprintf('%03d', $pokemon->id); ?> <?= $pokemon->name ?></a></p>
                <?php } else { ?>
                    <p class="pkmn-name"><a href="<?= $pokemon->permalink ?>"><?= $pokemon->name ?></a></p>
                <?php } ?>

				<p><?php if ($pokemon->spawn == 0) {
					echo $locales->UNSEEN;
} else {
	echo '<span class="spawn-counter" id="spawn_pokemon_'.$pokemon->id.'">?</span>'.$locales->SEEN;
} ?> </p>

			</div>

		<?php }?>


	</div>

</div>
