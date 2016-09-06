<header id="single-header">
<div class="row">
	<div class="col-md-12 text-center">
		<h1>
			<?= $locales->DASHBOARD_TITLE->$lang ?>
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
			
				<h4>Total Spawn <small>Last 7days</small></h4>
			
				<canvas id="total_spawn" width="100%" height="25"></canvas>
			
			</div>
			
			<div class="col-md-3">
				
				<h4>Very Common <small>Last 24h</small></h4>
				
				<canvas id="very_common" width="100%" height="50"></canvas>
			
			</div>
			
			<div class="col-md-3">
				
				<h4>Common <small>Last 24h</small></h4>
				
				<canvas id="common" width="100%" height="50"></canvas>
			
			</div>
			
			<div class="col-md-3">
				
				<h4>Rare <small>Last 24h</small></h4>
				
				<canvas id="rare" width="100%" height="50"></canvas>
			
			</div>
			
			<div class="col-md-3">
				
				<h4>Mythic <small>Last 24h</small></h4>
				
				<canvas id="mythics" width="100%" height="50"></canvas>
			
			</div>
		
		</div>
		
		<div class="row area">
			
			<div class="col-md-12">
				<h2 class="sub-title"><strong>Teams</strong> Performance</h2>
			</div>
			
			<div class="col-md-12">
				<h4>Prestige Average Team Performance <small>Last 24h</small></h4>
			</div>
			
			<div class="col-md-12">
				
				<canvas id="team_av" width="100%" height="25"></canvas>
				
			</div>
		
			
			<div class="col-md-12">
				<h4>Gym owned Team performance <small>Last 24h</small></h4>
			</div>
			
			<div class="col-md-12">
				
				<canvas id="team_gym" width="100%" height="25"></canvas>
				
			</div>
			
		
		</div>
		
		
		<div class="row area">
		
			<div class="col-md-12">
				<h2 class="sub-title"><strong>Pokestops</strong> activity</h2>
			</div>
		
			<div class="col-md-12">
			
				<h4>Active lures <small>Last 7days</small></h4>
			
				<canvas id="lures" width="100%" height="25"></canvas>
			
			</div>
						
		
		</div>

	
	</div>


</div>
