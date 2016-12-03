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

<div class="row area" id="trainersGraph">
	<div class="col-md-12 text-center">
		<h2 class="sub-title"><?= $locales->TRAINER_GRAPH ?></h2>
	</div>
	
	<div class="col-md-12">
		<canvas id="trainer_lvl" width="100%" height="20"></canvas>
	</div>
</div>

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
		
		</tbody>
		<tfoot>
			<tr class="trainerLoader">
				<td colspan="5"><div class="loader"></div></td>
			</tr>
		</tfoot>
		
	</table>
</div>

