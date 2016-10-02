<header id="single-header">
	<div class="row">
		<div class="col-md-12 text-center">
			<h1>
				<?= $locales->DASHBOARD_TITLE->$lang ?> <br/>
				<small><?= $locales->DASHBOARD_SUBTITLE->$lang ?></small>
			</h1>
			
		</div>
	</div>
</header>

<div class="row area">
	
	<div class="col-md-12">

		<div class="row area">
		
			<div class="col-md-12">
				<h2 class="sub-title"><?= $locales->DASHBOARD_SPAWN_TITLE->$lang ?></h2>
			</div>
		
			<div class="col-md-12">
			
				<h4><?= $locales->DASHBOARD_SPAWN_TOTAL->$lang ?> <small><?= $locales->DASHBOARD_LAST7DAYS->$lang ?></small></h4>
			
				<canvas id="total_spawn" width="100%" height="25"></canvas>
			
			</div>
			
			<div class="col-md-3">
				
				<h4><?= $locales->DASHBOARD_VERYCOMMON->$lang ?> <small><?= $locales->DASHBOARD_LAST24HOURS->$lang ?></small></h4>
				
				<canvas id="very_common" width="100%" height="50"></canvas>
			
			</div>
			
			<div class="col-md-3">
				
				<h4><?= $locales->DASHBOARD_COMMON->$lang ?> <small><?= $locales->DASHBOARD_LAST24HOURS->$lang ?></small></h4>
				
				<canvas id="common" width="100%" height="50"></canvas>
			
			</div>
			
			<div class="col-md-3">
				
				<h4><?= $locales->DASHBOARD_RARE->$lang ?> <small><?= $locales->DASHBOARD_LAST24HOURS->$lang ?></small></h4>
				
				<canvas id="rare" width="100%" height="50"></canvas>
			
			</div>
			
			<div class="col-md-3">
				
				<h4><?= $locales->DASHBOARD_MYTHIC->$lang ?> <small><?= $locales->DASHBOARD_LAST24HOURS->$lang ?></small></h4>
				
				<canvas id="mythics" width="100%" height="50"></canvas>
			
			</div>
		
		</div>
		
		<div class="row area">
			
			<div class="col-md-12">
				<h2 class="sub-title"><strong><?= $locales->TEAM->$lang ?></strong> <?= $locales->PERFORMANCE->$lang ?></h2>
			</div>
			
			<div class="col-md-12">
				<h4><?= $locales->DASHBOARD_PRESTIGE_AVERAGE->$lang ?> <small><?= $locales->DASHBOARD_LAST24HOURS->$lang ?></small></h4>
			</div>
			
			<div class="col-md-12">
				
				<canvas id="team_av" width="100%" height="25"></canvas>
				
			</div>
		
			
			<div class="col-md-12">
				<h4><?= $locales->DASHBOARD_GYM_OWNED_PERFORMANCE->$lang ?> <small><?= $locales->DASHBOARD_LAST24HOURS->$lang ?></small></h4>
			</div>
			
			<div class="col-md-12">
				
				<canvas id="team_gym" width="100%" height="25"></canvas>
				
			</div>
			
		
		</div>
		
		
		<div class="row area">
		
			<div class="col-md-12">
				<h2 class="sub-title"><strong><?= $locales->POKESTOPS->$lang ?></strong> <?= $locales->DASHBOARD_ACTIVITY->$lang ?></h2>
			</div>
		
			<div class="col-md-12">
			
				<h4><?= $locales->DASHBOARD_LURES->$lang ?> <small><?= $locales->DASHBOARD_LAST7DAYS->$lang ?></small></h4>
			
				<canvas id="lures" width="100%" height="25"></canvas>
			
			</div>
						
		
		</div>

	
	</div>


</div>
