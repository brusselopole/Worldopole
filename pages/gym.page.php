<header id="single-header">
<div class="row">
	<div class="col-md-12 text-center">
		<h1>
			<?= $locales->GYMS_TITLE->$lang ?>
		</h1>
		
	</div>
</div>
</header>


<div class="row teams">


	<?php 
	
	foreach($teams as $team_name => $team){ 
		
		// We do not display the team rocket result as it's not a real team 
		
		if($team_name != 'rocket'){
		
			$upper_team_name = strtoupper($team_name); 
			$lower_team_name	= strtolower($team_name);
		
	?>
		
	<div class="col-md-4 col-sm-4 col-xs-12 big-data"> <!-- <?= $team_name ?> -->
		
		<h2 style="margin:0;"><img src="core/img/<?= $lower_team_name ?>.png" alt="<?= $locales->TEAM->$lang ?> <?= $team_name ?> logo" width=50 style="display:inline-block;" class="team-logo" /> <?= $locales->TEAM->$lang ?> <?= $locales->$upper_team_name->$lang ?></h2>

		<div class="row" style="margin-top:1em;">
			<div class="col-xs-6">
				<p><big><strong class="gym-<?= $team_name ?>-js"><span>0</span></strong></big><br><?= $locales->GYMS_OWNED->$lang ?></p>
			</div>

			<div class="col-xs-6">
				<p><big><strong><span  class="average-<?= $team_name ?>-js">0</span></strong></big><br><?= $locales->GYMS_AVERAGE->$lang ?></p>
			</div>
		</div>

		<div class="row">
			<p style="margin-top:1em"><?= $locales->GYMS_GUARDIANS->$lang ?></p>
			
			<?php foreach($team->guardians as $guardian){ ?>
			
			<div class="col-xs-4">
				<a href="pokemon/<?= $guardian ?>">
				<img src="core/pokemons/<?= $guardian ?>.png" alt="<?= $pokemons->$guardian->name ?>" class="img-responsive" width=150>
				</a>
			</div>
			
			
			<?php }?>
			
		</div>
	</div>
		
	<?php } }?>


</div>

<div class="row hidden-xs hiddem-sm area">
	<div class="col-md-4">
		<p align="center"><a href="<?= $config->urls->fb_mystic ?>" target="_blank" class="btn btn-default"><i class="fa fa-facebook"></i> <?= $locales->TEAM->$lang ?> <?= $locales->MYSTIC->$lang ?></a></p>
	</div>

	<div class="col-md-4">
		<p align="center"><a href="<?= $config->urls->fb_valor ?>" target="_blank" class="btn btn-default"><i class="fa fa-facebook"></i> <?= $locales->TEAM->$lang ?> <?= $locales->VALOR->$lang ?></a></p>
	</div>

	<div class="col-md-4">
		<p align="center"><a href="<?= $config->urls->fb_instinct ?>" target="_blank" class="btn btn-default"><i class="fa fa-facebook"></i> <?= $locales->TEAM->$lang ?> <?= $locales->INSTINCT->$lang ?></a></p>
	</div>
</div>


<div class="row area">
	
	<div class="col-md-12">
	
		<div id="map">
		
		</div>

	
	</div>

</div>
