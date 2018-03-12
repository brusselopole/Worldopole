<header id="single-header">
<div class="row">
	<div class="col-md-12 text-center">
		<h1>Explore our <strong>Gym History</strong><br><small>Teams, levels, total CP and Pokémons of gyms</small></h1>
	</div>
</div>
<div class="row">
	<div class="col-md-12 text-center">
		<form class="form-inline" id="searchGyms" method="GET">
		  <div class="form-group">
			<div class="input-group">
				<div class="input-group-btn">
					<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" id="teamSelector"><span id="teamSelectorText"><?= $locales->TRAINERS_SEARCH_ALL_TEAMS ?></span>&nbsp;<span class="caret"></span></button>
					<ul class="dropdown-menu">
						<li><a class="teamSelectorItems" id="AllTeamsFilter" href="#"><?= $locales->TRAINERS_SEARCH_ALL_TEAMS ?></a></li>
						<li><a class="teamSelectorItems" id="NeutralTeamsFilter" href="#"><img src="core/img/map_white.png" />&nbsp;Neutral</a></li>
						<li><a class="teamSelectorItems" id="BlueTeamFilter" href="#"><img src="core/img/map_blue.png" />&nbsp;<?= $locales->MYSTIC ?></a></li>
						<li><a class="teamSelectorItems" id="RedTeamFilter" href="#"><img src="core/img/map_red.png" />&nbsp;<?= $locales->VALOR ?></a></li>
						<li><a class="teamSelectorItems" id="YellowFilter" href="#"><img src="core/img/map_yellow.png" />&nbsp;<?= $locales->INSTINCT ?></a></li>
					</ul>
				</div>
				<input type="text" class="form-control" name="name" id="name" placeholder="Gym name" value="">
				<div class="input-group-btn">
					<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"  id="rankingSelector"><span id="rankingOrderText">by last changed</span>&nbsp;<span class="caret"></span></button>
					<ul class="dropdown-menu dropdown-menu-right">
					  <li><a class="rankingOrderItems" id="changedFirst" href="#">&nbsp;by last changed</a></li>
					  <li><a class="rankingOrderItems" id="nameFirst" href="#">&nbsp;by name</a></li>
					  <li><a class="rankingOrderItems" id="totalcpFirst" href="#">&nbsp;by total CP</a></li>
					</ul>
				</div>
			</div>
		  </div>
		  <button type="submit" class="btn btn-primary"><?= $locales->SEARCH ?></button>
		</form>
	</div>
</div>
</header>

<div class="row">
	<div class="col-md-12">
		<div class="text-center">
			<h3>Recent Gym Activity</h3>
		</div>
		<table class="table table-hover" id="gymsTable">
			<thead>
				<tr>
					<th>Time</th>
					<th>Gym</th>
					<th>Level</th>
					<th>Total CP</th>
					<th>Pokémon</th>
				</tr>
			</thead>
			<tbody id="gymsContainer">

			</tbody>
			<tfoot>
				<tr class="loadMore text-center">
					<td colspan="7"><button id="loadMoreButton" class="btn btn-default hidden"><?= $locales->TRAINERS_LOAD_MORE ?></button></td>
				</tr>
				<tr class="gymLoader">
					<td colspan="7"><div class="loader"></div></td>
				</tr>
			</tfoot>
		</table>
	</div>
</div>
<script type="text/javascript">
<?=
$gymName = "";
if (isset($_GET['name']) && $_GET['name']!="") {
	$gymName = html_entity_decode($_GET['name']);
}
?>
var gymName = "<?= $gymName ?>";
</script>
