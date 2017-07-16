<!-- Header -->
<header id="single-header">

	<!-- Breadcrumb -->
	<div class="row">
		<div class="col-md-12">
			<ol class="breadcrumb">
				<li><a href="<?= HOST_URL ?>"><?= $locales->HOME ?></a></li>
				<li><a href="pokemon"><?= $locales->NAV_POKEDEX ?></a></li>
				<li class="active"><?= $pokemon->name ?></li>
			</ol>
		</div>
	</div>
	<!-- /Breadcrumb -->

	<div class="row">

		<div class="col-sm-1 hidden-xs">

				<?php if ($pokemon->id-1 > 0) { ?>

				<p class="nav-links"><a href="pokemon/<?= $pokemon->id-1 ?>"><i class="fa fa-chevron-left"></i></a></p>

				<?php }?>

		</div>

		<div class="col-sm-10 text-center">

			<h1>#<?= sprintf('%03d <strong>%s</strong>', $pokemon->id, $pokemon->name) ?><br>
			<small>[<?= $pokemon->rarity ?>]</small></h1>

			<p id="share">
				<a href="https://www.facebook.com/sharer/sharer.php?u=<?= HOST_URL ?>pokemon/<?= $pokemon->id ?>" target="_blank" class="btn btn-primary" title="<?php printf($locales->SHARE, "Facebook") ?>" ><?php printf($locales->SHARE, "") ?> <i class="fa fa-facebook" aria-hidden="true"></i></a>

				<a href="https://twitter.com/intent/tweet?source=<?= HOST_URL ?>pokemon/<?= $pokemon_id ?>&text=Find%20<?= $pokemon->name ?>%20in:%20<?= $config->infos->city ?>%20<?= HOST_URL ?>pokemon/<?= $pokemon->id ?>" target="_blank" title="<?php printf($locales->SHARE, "Twitter") ?>" class="btn btn-info"><?php printf($locales->SHARE, "") ?> <i class="fa fa-twitter" aria-hidden="true"></i></a>
			</p>

		</div>


		<div class="col-sm-1 hidden-xs">

			<?php if ($pokemon->id+1 < $config->system->max_pokemon) { ?>

			<p class="nav-links"><a href="pokemon/<?= $pokemon->id+1 ?>"><i class="fa fa-chevron-right"></i></a></p>

			<?php } ?>
		</div>

	</div>
<?php
	$form_array = array("Unset", "A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "W", "X", "Y", "Z");
	$form_array = array_values($form_array);
	?>

</header>
<!-- /Header -->


<div class="row">

	<div class="col-md-2 col-xs-4">
		<div id="poke-img" style="padding-top:15px;margin-bottom:1em;">
			<img class="media-object img-responsive" src="core/pokemons/<?= $pokemon->id.$config->system->pokeimg_suffix ?>" alt="<?= $pokemon->name ?> model" >
		</div>
	</div>

	<div class="col-md-4 col-xs-8" style="margin-bottom:1em;">

		<div class="media">
			<div class="media-body" style="padding-top:25px;">

				<p><?= $pokemon->description ?></p>

				<p>
				<?php foreach ($pokemon->types as $type) { ?>
					<span class="label label-default" style="background-color:<?= $pokemons->typecolors->$type ?>"><?= $type ?></span>
				<?php }?>
				</p>

			</div>
		</div>

	</div>

	<div class="col-md-6" style="padding-top:10px;">
		<canvas id="spawn_chart" width="100%" height="40"></canvas>
	</div>

</div>



<div class="row">
	<div class="col-md-6" style="padding-top:10px;">

		<table class="table">
			<tr>
				<td class="col-md-8 col-xs-8"><strong><?= $locales->POKEMON_SEEN ?></strong></td>
				<td class="col-md-4 col-xs-4">

				<?php if (isset($pokemon->last_position)) { ?>

					<a href="https://maps.google.com/?q=<?= $pokemon->last_position->latitude ?>,<?= $pokemon->last_position->longitude ?>&ll=<?= $pokemon->last_position->latitude ?>,<?= $pokemon->last_position->longitude ?>&z=16" target="_blank"><?= time_ago($pokemon->last_seen, $locales) ?></a>

				<?php } else {
					echo $locales->NEVER;
}?>

				</td>
			</tr>
			<tr>
				<td class="col-md-8 col-xs-8"><strong><?= $locales->POKEMON_AMOUNT ?> :</strong></td>
				<td class="col-md-4 col-xs-4"><?= $pokemon->spawn_count ?> <?= $locales->SEEN ?></td>
			</tr>
			<tr>
				<td class="col-md-8 col-xs-8"><strong><?= $locales->POKEMON_RATE ?> :</strong></td>
				<td class="col-md-4 col-xs-4"><?= $pokemon->spawns_per_day ?> / <?= $locales->DAY ?></td>
			</tr>
			<tr>
				<td class="col-md-8 col-xs-8"><?php if (isset($pokemon->protected_gyms)) {
					echo "<strong>" . $locales->POKEMON_GYM . $pokemon->name . "</strong> :";
} ?></td>
				<td class="col-md-4 col-xs-4"><?php if (isset($pokemon->protected_gyms)) {
					echo $pokemon->protected_gyms ;
}?></td>
			</tr>
		</table>
		</div>

		<div class="col-md-6" style="padding-top:10px;">
		<table class="table">
			<tr>
				<td class="col-md-8 col-xs-8"><strong><?= $locales->POKEMON_EVOLUTION ?> :</strong></td>
				<td class="col-md-4 col-xs-4"><?php if (isset($pokemon->candies)) {
					printf($locales->POKEMON_CANDIES, $pokemon->candies, $pokemon->candy_name);
} else {
	echo $locales->POKEMON_FINAL;
} ?></td>
			</tr>
			<tr>
				<td class="col-md-8 col-xs-8"><strong><?= $locales->POKEMON_QUICK ?> :</strong></td>
				<td class="col-md-4 col-xs-4"><?= $pokemon->quick_move ?></td>
			</tr>
			<tr>
				<td class="col-md-8 col-xs-8"><strong><?= $locales->POKEMON_SPECIAL ?> :</strong> </td>
				<td class="col-md-4 col-xs-4"><?= $pokemon->charge_move ?></td>
			</tr>

		</table>

	</div>
</div>



<div class="row area text-center subnav">
	<div class="btn-group" role="group">
		<a class="btn btn-default page-scroll" href="pokemon/<?= $pokemon->id ?>#where"><i class="fa fa-map-marker"></i> <?= $locales->POKEMON_MAP ?></a>
		<a class="btn btn-default page-scroll" href="pokemon/<?= $pokemon->id ?>#stats"><i class="fa fa-pie-chart"></i> <?= $locales->POKEMON_STATS ?></a>
		<a class="btn btn-default page-scroll" href="pokemon/<?= $pokemon->id ?>#family"><i class="fa fa-share-alt"></i> <?= $locales->POKEMON_FAMILY ?></a>
		<a class="btn btn-default page-scroll" href="pokemon/<?= $pokemon->id ?>#top50"><i class="fa fa-list"></i> Top50</a>
	</div>
</div>




<div class="row" id="where">

	<div class="col-md-12">

		<h2 class="text-center sub-title"><?= $locales->POKEMON_WHERE ?> <?= $pokemon->name ?>?</h2>

	</div>
</div>
<div class="row text-center subnav">
	<div class="btn-group" role="group">
		<a class="btn btn-default active" id="heatmapSelector"><i class="fa fa-thermometer-three-quarters"></i> <?= $locales->POKEMON_HEATMAP_BUTTON ?></a>
		<a class="btn btn-default " id="liveSelector"><i class="fa fa-eye"></i> <?= $locales->POKEMON_LIVE_BUTTON ?></a>
	</div>
</div>
<div class="row" style="margin-bottom:20px">
	<div class="col-md-12" id="timeFilterContainer">
		<div id="timeSelector">
		</div>
	</div>
	<div class="col-md-12" id="liveFilterContainer">
		<div id="liveFilterSelector">
		</div>
	</div>
	<div class="col-md-12 text-center" id="loaderContainer">
		<h3><?= $locales->LOADING ?></h3>
	</div>
</div>
<div class="row area">
	<div class="col-md-12">
		<div id="map">
		</div>
	</div>
</div>




<div class="row area" id="stats">

	<h2 class="text-center sub-title"><strong><?= $pokemon->name ?></strong> <?= $locales->POKEMON_BREAKDOWN ?></h2>

	<!-- Rating -->
	<p class="text-center stats-data"><big><?= $pokemon->rating ?> / 10</big><br><?= $locales->POKEMON_RATING ?></p>

	<!-- CP Datas -->
	<div class="col-md-3 stats-data">

		<p><big><?= $pokemon->max_cp ?></big><br/><?= $locales->POKEMON_CP ?></p>

		<div class="progress" style="margin-bottom:0;">
			<div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="<?= $pokemon->max_cp_percent ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?= $pokemon->max_cp_percent ?>%;min-width:30%;">
				<?= $pokemon->max_cp_percent ?> %
			</div>
		</div>

		<?= $locales->POKEMON_COMPGAME ?>

	</div>

	<!-- Chart -->
	<div class="col-md-6">

		<canvas id="polar_chart" width="100%" height="60"></canvas>

	</div>
	<!-- /Chart -->

	<!-- PV Datas -->
	<div class="col-md-3 stats-data">

		<p><big><?= $pokemon->max_hp ?></big><br/><?= $locales->POKEMON_HP ?></p>

		<div class="progress" style="margin-bottom:0;">
			<div class="progress-bar progress-bar-success progress-bar-striped active" role="progressbar" aria-valuenow="<?= $pokemon->max_hp_percent ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?= $pokemon->max_hp_percent ?>%;min-width:30%;">
				<?= $pokemon->max_hp_percent ?> %
			</div>
		</div>

		<?= $locales->POKEMON_COMPGAME ?>

	</div>


</div>





<div class="row area" id="family">

	<div class="col-md-12">

		<h2 class="text-center sub-title"><strong><?= $pokemon->name ?></strong><?= $locales->POKEMON_FAMILYTITLE ?></h2>

		<div class="row">

		<?php

		foreach ($related as $related_mon) {
			?>

			<div class="col-md-1 col-sm-2 col-xs-3 pokemon-single">

				<a href="pokemon/<?= $related_mon ?>">
					<img src="core/pokemons/<?= $related_mon.$config->system->pokeimg_suffix ?>" alt="<?= $pokemons->pokemon->$related_mon->name.$config->system->pokeimg_suffix ?>" class="img-responsive">
				</a>

			</div>


			<?php
		}

		?>

		</div>


	</div>

</div>


<?php if (!empty($top)) { ?>
	<div class="row area" id="top50">
		<div class="col-md-12">
			<h2 class="text-center sub-title">Top 50 <strong><?= $pokemon->name ?></strong></h2>
			<table class="table">
				<thead>
					<tr>
						<th>#</th>
						<th><a href="pokemon/<?= $pokemon->id ?>?order=cp<?php echo $best_order == 'cp' && !isset($_GET['direction']) ? '&direction=desc' : ''; ?>#top50">CP <i class="fa fa-sort" aria-hidden="true"></i></a></th>
						<th><a href="pokemon/<?= $pokemon->id ?>?order=IV<?php echo $top_order == 'IV' && !isset($_GET['direction']) ? '&direction=desc' : ''; ?>#top50">IV <i class="fa fa-sort" aria-hidden="true"></i></a></th>
						<th><a href="pokemon/<?= $pokemon->id ?>?order=individual_attack<?php echo $top_order == 'individual_attack' && !isset($_GET['direction']) ? '&direction=desc' : ''; ?>#top50"><?= $locales->POKEMON_TABLE_ATTACK ?> <i class="fa fa-sort" aria-hidden="true"></i></a></th>
						<th><a href="pokemon/<?= $pokemon->id ?>?order=individual_defense<?php echo $top_order == 'individual_defense' && !isset($_GET['direction']) ? '&direction=desc' : ''; ?>#top50"><?= $locales->POKEMON_TABLE_DEFENSE ?> <i class="fa fa-sort" aria-hidden="true"></i></a></th>
						<th><a href="pokemon/<?= $pokemon->id ?>?order=individual_stamina<?php echo $top_order == 'individual_stamina' && !isset($_GET['direction']) ? '&direction=desc' : ''; ?>#top50"><?= $locales->POKEMON_TABLE_STAMINA ?> <i class="fa fa-sort" aria-hidden="true"></i></a></th>
						<th><a href="pokemon/<?= $pokemon->id ?>?order=move_1<?php echo $top_order == 'move_1' && !isset($_GET['direction']) ? '&direction=desc' : ''; ?>#top50">1. <?= $locales->MOVE ?> <i class="fa fa-sort" aria-hidden="true"></i></a></th>
						<th><a href="pokemon/<?= $pokemon->id ?>?order=move_2<?php echo $top_order == 'move_2' && !isset($_GET['direction']) ? '&direction=desc' : ''; ?>#top50">2. <?= $locales->MOVE ?> <i class="fa fa-sort" aria-hidden="true"></i></a></th>
						<th><a href="pokemon/<?= $pokemon->id ?>?order=disappear_time<?php echo $top_order == 'disappear_time' && !isset($_GET['direction']) ? '&direction=desc' : ''; ?>#top50"><?= $locales->DATE ?> <i class="fa fa-sort" aria-hidden="true"></i></a></th>
						<?php if ($pokemon->id == 201) { ?>
							<th>Form</th>
						<?php } ?>
					</tr>
				</thead>
			
				<tbody>
					<?php
					$i = 0;
					foreach ($top as $top50) {
						$i++;
						$move1 = $top50->move_1;
						$move2 = $top50->move_2;
						?>

						<tr>
							<td><?= $i ?></td>
							<td><?= isset($top50->cp) ? $top50->cp : "???" ?></td>
							<td><?= $top50->IV ?> %</td>
							<td><?= $top50->individual_attack ?></td>
							<td><?= $top50->individual_defense ?></td>
							<td><?= $top50->individual_stamina ?></td>
							<td><?php echo $move->$move1->name; ?></td>
							<td><?php echo $move->$move2->name; ?></td>
							<td><a href="https://maps.google.com/?q=<?= $top50->latitude ?>,<?= $top50->longitude ?>&ll=<?= $top50->latitude ?>,<?= $top50->longitude ?>&z=16"
								target="_blank"><?=$top50->distime ?></a></td>
							<?php if ($pokemon->id == 201 && $top50->form) { ?>
								<td><?php echo $form_array[$top50->form]; ?></td>
							<?php } else { ?>
								<td></td>
							<?php } ?>
						</tr>
						<?php
					} ?>
			</tbody>
		</table>
	</div>
</div>

<?php } if ($toptrainer) { ?>
	<div class="row" id="trainer">
		<div class="col-md-12">
			<h2 class="text-center sub-title"><?= $locales->POKEMON_TOPTRAINER ?> <strong><?= $pokemon->name ?></strong></h2>
			<table class="table">
				<thead>
					<tr>
						<th>#</th>
						<th><a href="pokemon/<?= $pokemon->id ?>?order=trainer_name<?php echo $best_order == 'trainer_name' && !isset($_GET['direction']) ? '&direction=desc' : ''; ?>#trainer"><?= $locales->NAME ?> <i class="fa fa-sort" aria-hidden="true"></i></a></th>
						<th><a href="pokemon/<?= $pokemon->id ?>?order=cp<?php echo $best_order == 'cp' && !isset($_GET['direction']) ? '&direction=desc' : ''; ?>#trainer">CP <i class="fa fa-sort" aria-hidden="true"></i></a></th>
						<th><a href="pokemon/<?= $pokemon->id ?>?order=IV<?php echo $best_order == 'IV' && !isset($_GET['direction']) ? '&direction=desc' : ''; ?>#trainer">IV <i class="fa fa-sort" aria-hidden="true"></i></a></th>
						<th><a href="pokemon/<?= $pokemon->id ?>?order=move_1<?php echo $best_order == 'move_1' && !isset($_GET['direction']) ? '&direction=desc' : ''; ?>#trainer">1. <?= $locales->MOVE ?> <i class="fa fa-sort" aria-hidden="true"></i></a></th>
						<th><a href="pokemon/<?= $pokemon->id ?>?order=move_2<?php echo $best_order == 'move_2' && !isset($_GET['direction']) ? '&direction=desc' : ''; ?>#trainer">2. <?= $locales->MOVE ?> <i class="fa fa-sort" aria-hidden="true"></i></a></th>
						<th><a href="pokemon/<?= $pokemon->id ?>?order=last_seen<?php echo $best_order == 'last_seen' && !isset($_GET['direction']) ? '&direction=desc' : ''; ?>#trainer"><?= $locales->POKEMON_TABLE_SEEN ?> <i class="fa fa-sort" aria-hidden="true"></i></a></th>
					</tr>
				</thead>
				<tbody>
					<?php
					$i = 0;
					foreach ($toptrainer as $besttrainer) {
						$i++;
						$move1 = $besttrainer->move_1;
						$move2 = $besttrainer->move_2;
						?>

						<tr>
							<td><?= $i ?></td>
							<td><?= $besttrainer->trainer_name ?></td>
							<td><?= $besttrainer->cp ?></td>
							<td><?= $besttrainer->IV ?> %</td>
							<td><?php echo $move->$move1->name; ?></td>
							<td><?php echo $move->$move2->name; ?></td>
							<td><?=$besttrainer->lasttime ?></td>
						</tr>
						<?php
					} ?>
			</tbody>
		</table>
	</div>
</div>
<?php } ?>
