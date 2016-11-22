<header id="single-header">
<div class="row">
	<div class="col-md-12 text-center">
		<h1>
			<?= $locales->TRAINERS_TITLE ?>
		</h1>
		
	</div>
</div>
<div class="row">
	<div class="col-md-12 text-center">
		<form class="form-inline" id="searchTrainer" method="GET">
		  <div class="form-group">
			<div class="input-group">
			  <div class="input-group-addon">Trainer</div>
			  <input type="text" class="form-control" name="name" id="name" placeholder="Name" value="">
			</div>
		  </div>
		  <button type="submit" class="btn btn-primary">Search</button>
		</form>
	</div>
</div>
</header>

<div class="row area">
	<div class="col-md-12 text-center">
		<h2 class="sub-title">Trainer <strong>level</strong> distribution</h2>
	</div>
	
	<div class="col-md-12">
		<canvas id="trainer_lvl" width="100%" height="20"></canvas>
	</div>
</div>
<script async defer src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.2.1/Chart.min.js"></script>
<script async defer src="core/js/trainer.graph.js.php"></script>



<div class="row">
	<table class="table">
		<thead>
		<tr>
			<th>#</th>
			<th>Rank</th>
			<th>Name</th>
			<th>Level</th>
			<th>Gyms</th>
		</tr>
		</thead>
		<tbody id="trainersContainer">
			<tr id="trainerInfos" class="trainersTemplate">
				<td id="trainerIndex"></td>
				<td id="trainerRank"></td>
				<td ><a id="trainerNameLink" href=""><span id="trainerName"></span></a></td>
				<td id="trainerLevel"></td>
				<td id="trainerGyms"></td>
			</tr>
			<tr id="trainerPokemons"  class="trainersTemplate">
				<td colspan="5" class="">
					<div class="container">
						<div class="col-md-1 col-xs-4 pokemon-single trainersTemplate" style="text-align: center" id="trainerPokemon">
							<a id="trainerPokemonLink" href="">
							<img src="" class="img-responsive pkmn-image" id="trainerPokemonImage">
							</a>
							<p class="pkmn-name" id="trainerPokemonCp"></p>
							<div class="progress" style="height: 6px">
								<div id="trainerPokemonStamina" title="IV Stamina: " class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="" aria-valuemin="0" aria-valuemax="45" >
									<span class="sr-only">Stamina IV : </span>
								</div>
							
								<div id="trainerPokemonAttack" title="IV Attack: " class="progress-bar progress-bar-danger" role="progressbar" aria-valuenow="" aria-valuemin="0" aria-valuemax="45" >
									<span class="sr-only">Attack IV : </span>
								</div>

								<div id="trainerPokemonDefense" title="IV Defense: " class="progress-bar progress-bar-info" role="progressbar" aria-valuenow="" aria-valuemin="0" aria-valuemax="45" >
									<span class="sr-only">Defense IV : </span>
								</div>
							</div>
						</div>
					</div>
				</td>
			</tr>
			
			
		</tbody>
		<tfoot>
			<tr class="trainerLoader">
				<td colspan="5"><div class="loader"></div></td>
			</tr>
		</tfoot>
		
	</table>
</div>

