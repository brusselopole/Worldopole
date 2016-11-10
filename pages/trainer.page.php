<header id="single-header">
<div class="row">
	<div class="col-md-12 text-center">
		<h1>
			<?= $locales->TRAINERS_TITLE->$lang ?>
		</h1>
		
	</div>
</div>
<div class="row">
	<div class="col-md-12 text-center">
		<form class="form-inline" method="GET">
		  <div class="form-group">
			<div class="input-group">
			  <div class="input-group-addon">Trainer</div>
			  <input type="text" class="form-control" name="name" id="name" placeholder="Name" value="<?= $trainer_name ?>">
			  
			</div>
		  </div>
		  <button type="submit" class="btn btn-primary">Search</button>
		</form>
	</div>
</div>
</header>


<div class="row">
	<table class="table">
		<thead>
		<tr>
			<th>#</th>
			<th>Name</th>
			<th>Level</th>
			<th>Gyms</th>
		</tr>
		</thead>
	<tbody>
		<?php 
		$i = 0;
		
		foreach($trainers as $trainers_name => $trainer){ 
		
		$i++;
		?>
		<tr>
			<td><?= $i ?></td>
			<td><a href="trainer?name=<?= $trainer->name ?>"><?= $trainer->name ?></a></td>
			<td><?= $trainer->level ?></td>
			<td><?= $trainer->gyms ?></td>
			</tr>
			<tr>
				<?php
				if($trainer->team == 1){
					$border_color="#AAAAFF";
				}elseif($trainer->team == 2){
					$border_color="#FFAAAA";
				}elseif($trainer->team == 3){
					$border_color="#FFE9AA";
				}else{
					$border_color="#DDDDDD";
				}
				?>
				<td colspan="4" style="border-top: 2px solid <?= $border_color ?>">
				<div class=container">
				<?php
				$j = 0;
				foreach($trainer->pokemons as $pokemon_uid => $pokemon){
				$j++;
				?>
				
				<div class="col-md-1 col-xs-4 pokemon-single" pokeid="<?= $pokemon->pokemon_id-1 ?>">
					<img src="core/pokemons/<?= $pokemon->pokemon_id ?>.png" class="img-responsive">
					<p class="pkmn-name">CP: <?= $pokemon->cp ?></p>		
					<div class="progress">
						<div title="IV Defense" class="progress-bar progress-bar-info" role="progressbar" aria-valuenow="<?= $pokemon->iv_defense ?>" aria-valuemin="0" aria-valuemax="45" style="width: <?= ((100/15)*$pokemon->iv_defense)/3 ?>%">
							<span class="sr-only">Defense IV : <?= $pokemon->iv_defense ?></span>
							<?= $pokemon->iv_defense ?>
						</div>
						<div title="IV Stamina" class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="<?= $pokemon->iv_stamina ?>" aria-valuemin="0" aria-valuemax="45" style="width: <?= ((100/15)*$pokemon->iv_stamina)/3 ?>%">
							<span class="sr-only">Stamina IV : <?= $pokemon->iv_stamina ?></span>
							<?= $pokemon->iv_stamina ?>
						</div>
					
						<div title="IV Attack" class="progress-bar progress-bar-danger" role="progressbar" aria-valuenow="<?= $pokemon->iv_attack ?>" aria-valuemin="0" aria-valuemax="45" style="width: <?= ((100/15)*$pokemon->iv_attack)/3 ?>%">
							<span class="sr-only">Attack IV : <?= $pokemon->iv_attack ?></span>
							<?= $pokemon->iv_attack ?>
						</div>
					</div>
				</div>
				
				
				<?php }?>
				</td>
			</tr>
		<?php }?>
	</tbody>
	</table>
</div>
