<header id="single-header">
	<div class="row">
		<div class="col-md-12 text-center">
			<h1>
				<?= $locales->DASHBOARD_TITLE ?> <br/>
				<small><?= $locales->DASHBOARD_SUBTITLE ?></small>
			</h1>
			
		</div>
	</div>
</header>

<div class="row area">
	
	<div class="col-md-12">

		<div class="row area">
		
			<div class="col-md-12">
				<h2 class="sub-title"><?= $locales->DASHBOARD_SPAWN_TITLE ?></h2>
			</div>
		
			<div class="col-md-12">
			
				<h4><?= $locales->DASHBOARD_SPAWN_TOTAL ?> <small><?= $locales->DASHBOARD_LAST7DAYS ?></small></h4>
			
				<canvas id="total_spawn" width="100%" height="25"></canvas>
			
			</div>
			
			<div class="col-md-3">
				
				<h4><?= $locales->DASHBOARD_VERYCOMMON ?> <small><?= $locales->DASHBOARD_LAST24HOURS ?></small></h4>
				
				<canvas id="very_common" width="100%" height="50"></canvas>
			
			</div>
			
			<div class="col-md-3">
				
				<h4><?= $locales->DASHBOARD_COMMON ?> <small><?= $locales->DASHBOARD_LAST24HOURS ?></small></h4>
				
				<canvas id="common" width="100%" height="50"></canvas>
			
			</div>
			
			<div class="col-md-3">
				
				<h4><?= $locales->DASHBOARD_RARE ?> <small><?= $locales->DASHBOARD_LAST24HOURS ?></small></h4>
				
				<canvas id="rare" width="100%" height="50"></canvas>
			
			</div>
			
			<div class="col-md-3">
				
				<h4><?= $locales->DASHBOARD_MYTHIC ?> <small><?= $locales->DASHBOARD_LAST24HOURS ?></small></h4>
				
				<canvas id="mythics" width="100%" height="50"></canvas>
			
			</div>
		
		</div>
		
		<div class="row area">
			
			<div class="col-md-12">
				<h2 class="sub-title"><strong><?= $locales->TEAM ?></strong> <?= $locales->PERFORMANCE ?></h2>
			</div>
			
			<div class="col-md-12">
				<h4><?= $locales->DASHBOARD_PRESTIGE_AVERAGE ?> <small><?= $locales->DASHBOARD_LAST24HOURS ?></small></h4>
			</div>
			
			<div class="col-md-12">
				
				<canvas id="team_av" width="100%" height="25"></canvas>
				
			</div>
		
			
			<div class="col-md-12">
				<h4><?= $locales->DASHBOARD_GYM_OWNED_PERFORMANCE ?> <small><?= $locales->DASHBOARD_LAST24HOURS ?></small></h4>
			</div>
			
			<div class="col-md-12">
				
				<canvas id="team_gym" width="100%" height="25"></canvas>
				
			</div>
			
		
		</div>
		
		
		<div class="row area">
		
			<div class="col-md-12">
				<h2 class="sub-title"><strong><?= $locales->POKESTOPS ?></strong> <?= $locales->DASHBOARD_ACTIVITY ?></h2>
			</div>
		
			<div class="col-md-12">
			
				<h4><?= $locales->DASHBOARD_LURES ?> <small><?= $locales->DASHBOARD_LAST7DAYS ?></small></h4>
			
				<canvas id="lures" width="100%" height="25"></canvas>
			
			</div>
						
		
		</div>

	
	</div>


</div>
