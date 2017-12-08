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
			<div class="input-group hidden-xs">
				<div class="input-group-btn">
					<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" id="teamSelector"><span id="teamSelectorText"><img src="core/img/map_white.png" />&nbsp;<?= $locales->TRAINERS_SEARCH_ALL_TEAMS ?></span>&nbsp;<span class="caret"></span></button>
					<ul class="dropdown-menu">
						<li><a class="teamSelectorItems" id="AllTeamsFilter" href="#"><img src="core/img/map_white.png" />&nbsp;<?= $locales->TRAINERS_SEARCH_ALL_TEAMS ?></a></li>
						<li><a class="teamSelectorItems" id="BlueTeamFilter" href="#"><img src="core/img/map_blue.png" />&nbsp;<?= $locales->MYSTIC ?></a></li>
						<li><a class="teamSelectorItems" id="RedTeamFilter" href="#"><img src="core/img/map_red.png" />&nbsp;<?= $locales->VALOR ?></a></li>
						<li><a class="teamSelectorItems" id="YellowFilter" href="#"><img src="core/img/map_yellow.png" />&nbsp;<?= $locales->INSTINCT ?></a></li>
					</ul>
				</div>
				<input type="text" class="form-control" name="name" id="name" placeholder="<?= $locales->NAME ?>" value="">
				<div class="input-group-btn">
					<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"  id="rankingSelector"><span id="rankingOrderText"><?= $locales->TRAINERS_SEARCH_LEVELS_FIRST ?></span>&nbsp;<span class="caret"></span></button>
					<ul class="dropdown-menu dropdown-menu-right">
					  <li><a class="rankingOrderItems" id="levelsFirst" href="#">&nbsp;<?= $locales->TRAINERS_SEARCH_LEVELS_FIRST ?></a></li>
					  <li><a class="rankingOrderItems" id="gymsFirst" href="#">&nbsp;<?= $locales->TRAINERS_SEARCH_GYMS_FIRST ?></a></li>
					  <li><a class="rankingOrderItems" id="maxCpFirst" href="#">&nbsp;<?= $locales->TRAINERS_SEARCH_MAX_CP_FIRST ?></a></li>
					</ul>
				</div>
			</div>
			  <div class="input-group-vertical visible-xs">
				<div class=" ">
					<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" id="teamSelector"><span id="teamSelectorText"><img src="core/img/map_white.png" />&nbsp;<?= $locales->TRAINERS_SEARCH_ALL_TEAMS ?></span>&nbsp;<span class="caret"></span></button>
					<ul class="dropdown-menu">
						<li><a class="teamSelectorItems" id="AllTeamsFilter" href="#"><img src="core/img/map_white.png" />&nbsp;<?= $locales->TRAINERS_SEARCH_ALL_TEAMS ?></a></li>
						<li><a class="teamSelectorItems" id="BlueTeamFilter" href="#"><img src="core/img/map_blue.png" />&nbsp;<?= $locales->MYSTIC ?></a></li>
						<li><a class="teamSelectorItems" id="RedTeamFilter" href="#"><img src="core/img/map_red.png" />&nbsp;<?= $locales->VALOR ?></a></li>
						<li><a class="teamSelectorItems" id="YellowFilter" href="#"><img src="core/img/map_yellow.png" />&nbsp;<?= $locales->INSTINCT ?></a></li>
					</ul>
				</div>
				<input type="text" class="form-control" name="name" id="name" placeholder="<?= $locales->NAME ?>" value="">
				<div class="">
					<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"  id="rankingSelector"><span id="rankingOrderText"><?= $locales->TRAINERS_SEARCH_LEVELS_FIRST ?></span>&nbsp;<span class="caret"></span></button>
					<ul class="dropdown-menu dropdown-menu-right">
					  <li><a class="rankingOrderItems" id="levelsFirst" href="#">&nbsp;<?= $locales->TRAINERS_SEARCH_LEVELS_FIRST ?></a></li>
					  <li><a class="rankingOrderItems" id="gymsFirst" href="#">&nbsp;<?= $locales->TRAINERS_SEARCH_GYMS_FIRST ?></a></li>
					  <li><a class="rankingOrderItems" id="maxCpFirst" href="#">&nbsp;<?= $locales->TRAINERS_SEARCH_MAX_CP_FIRST ?></a></li>
					</ul>
				</div>
			</div>
		  </div>
		  <button type="submit" class="btn btn-primary"><?= $locales->SEARCH ?></button>
		</form>
	</div>
</div>
</header>

<div class="row area" id="trainersGraph">
	<div class="col-md-12 text-center">
		<h2 class="sub-title"><?= $locales->TRAINERS_GRAPH ?></h2>
	</div>

	<div class="col-md-12">
		<div style="height:20vh">
			<canvas id="trainer_lvl"></canvas>
		</div>
	</div>
</div>

<div class="row">
	<div class="col-md-12">
		<div class="table-responsive">
			<table class="table" id="trainersTable">
				<thead>
				<tr>
					<th>#</th>
					<th><?= $locales->TRAINERS_TABLE_SAME_LEVEL ?></th>
					<th><?= $locales->TRAINERS_TABLE_NAME ?></th>
					<th><?= $locales->TRAINERS_TABLE_LEVEL ?></th>
					<th><?= $locales->TRAINERS_TABLE_GYMS ?></th>
					<th><?= $locales->TRAINERS_TABLE_LAST_SEEN ?></th>
				</tr>
				</thead>
				<tbody id="trainersContainer">

				</tbody>
				<tfoot>
					<tr class="loadMore text-center">
						<td colspan="6"><button id="loadMoreButton" class="btn btn-default hidden"><?= $locales->TRAINERS_LOAD_MORE ?></button></td>
					</tr>
					<tr class="trainerLoader">
						<td colspan="6"><div class="loader"></div></td>
					</tr>
				</tfoot>
			</table>
		</div>
	</div>
</div>
<script type="text/javascript">
<?=
$trainerName = "";
if (isset($_GET['name']) && $_GET['name'] != "") {
	$trainerName = htmlentities($_GET['name']);
}

?>
	var trainerName = "<?= $trainerName ?>";

</script>
