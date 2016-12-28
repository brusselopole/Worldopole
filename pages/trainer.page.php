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
                            <div class="input-group-btn">
                                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" id="teamSelector"><span id="teamSelectorText"><img src="core/img/map_white.png" />&nbsp;<?= $locales->TRAINERS_SEARCH_ALL_TEAMS ?></span>&nbsp;<span class="caret"></span></button>
                                <ul class="dropdown-menu">
                                    <li><a class="teamSelectorItems" id="AllTeamsFilter" href="#"><img src="core/img/map_white.png" />&nbsp;<?= $locales->TRAINERS_SEARCH_ALL_TEAMS ?></a></li>
                                    <li><a class="teamSelectorItems" id="BlueTeamFilter" href="#"><img src="core/img/map_blue.png" />&nbsp;<?= $locales->MYSTIC ?></a></li>
                                    <li><a class="teamSelectorItems" id="RedTeamFilter" href="#"><img src="core/img/map_red.png" />&nbsp;<?= $locales->VALOR ?></a></li>
                                    <li><a class="teamSelectorItems" id="YellowFilter" href="#"><img src="core/img/map_yellow.png" />&nbsp;<?= $locales->INSTINCT ?></a></li>
                                </ul>
                            </div>
                            <input type="text" class="form-control" name="name" id="name" placeholder="Name" value="">
                            <div class="input-group-btn">
                                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"  id="rankingSelector"><span id="rankingOrderText"><?= $locales->TRAINERS_SEARCH_LEVELS_FIRST ?></span>&nbsp;<span class="caret"></span></button>
                                <ul class="dropdown-menu dropdown-menu-right">
                                  <li><a class="rankingOrderItems" id="levelsFirst" href="#">&nbsp;<?= $locales->TRAINERS_SEARCH_LEVELS_FIRST ?></a></li>
                                  <li><a class="rankingOrderItems" id="gymsFirst" href="#">&nbsp;<?= $locales->TRAINERS_SEARCH_GYMS_FIRST ?></a></li>
                                </ul>
                            </div>
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
			<th>Same Level</th>
			<th>Name</th>
			<th>Level</th>
			<th>Gyms</th>
			<th>Last Seen</th>
		</tr>
		</thead>
		<tbody id="trainersContainer">
		
		</tbody>
		<tfoot>
			<tr class="trainerLoader">
				<td colspan="6"><div class="loader"></div></td>
			</tr>
		</tfoot>
		
	</table>
</div>

