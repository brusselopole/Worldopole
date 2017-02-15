<header id="single-header">
	<div class="row">
		<div class="col-md-12 text-center">
			<h1>
				<?= $locales->GYMS_TITLE ?>
			</h1>
			
		</div>
	</div>
</header>


<div class="row teams">


	<?php
	
	foreach ($teams as $team_name => $team) {
		// We do not display the team rocket result as it's not a real team
		
		if ($team_name != 'rocket') {
			$upper_team_name = strtoupper($team_name);
			$lower_team_name	= strtolower($team_name);
		
	?>
		
	<div class="col-md-4 col-sm-4 col-xs-12 big-data"> <!-- <?= $team_name ?> -->
		
		<h2 style="margin:0;"><img src="core/img/<?= $lower_team_name ?>.png" alt="<?= $locales->TEAM ?> <?= $team_name ?> logo" width=50 style="display:inline-block;" class="team-logo" /> <?= $locales->TEAM ?> <?= $locales->$upper_team_name ?></h2>

		<div class="row" style="margin-top:1em;">
			<div class="col-xs-6">
				<p><big><strong class="gym-<?= $team_name ?>-js"><span>0</span></strong></big><br><?= $locales->GYMS_OWNED ?></p>
			</div>

			<div class="col-xs-6">
				<p><big><strong><span class="average-<?= $team_name ?>-js">0</span></strong></big><br><?= $locales->GYMS_AVERAGE ?></p>
			</div>
		</div>

		<div class="row">
			<p style="margin-top:1em"><?= $locales->GYMS_GUARDIANS ?></p>
			
			<?php foreach ($team->guardians as $guardian) { ?>
			
				<div class="col-xs-4 pokemon-single">
					<a href="pokemon/<?= $guardian ?>">
					<img src="core/pokemons/<?= $guardian.$config->system->pokeimg_suffix ?>" alt="<?= $pokemons->pokemon->$guardian->name ?>" class="img-responsive" width=150>
					</a>
				</div>
			
			
			<?php }?>
			
		</div>
	</div>

	<?php
		}
	}?>


</div>

<!-- auto hide buttons if no url is set in variables.json -->
<?php if ($config->urls->fb_mystic || $config->urls->fb_valor || $config->urls->fb_instinct) { ?>
<div class="row hidden-xs hiddem-sm area">
	<div class="col-md-4">
		<p align="center"><a href="<?= $config->urls->fb_mystic ?>" target="_blank" class="btn btn-default"><i class="fa fa-facebook"></i> <?= $locales->TEAM ?> <?= $locales->MYSTIC ?></a></p>
	</div>

	<div class="col-md-4">
		<p align="center"><a href="<?= $config->urls->fb_valor ?>" target="_blank" class="btn btn-default"><i class="fa fa-facebook"></i> <?= $locales->TEAM ?> <?= $locales->VALOR ?></a></p>
	</div>

	<div class="col-md-4">
		<p align="center"><a href="<?= $config->urls->fb_instinct ?>" target="_blank" class="btn btn-default"><i class="fa fa-facebook"></i> <?= $locales->TEAM ?> <?= $locales->INSTINCT ?></a></p>
	</div>
</div>
<?php } ?>

<div class="row">
	
	<div class="col-md-12">
	
		<div id="map">
		
		</div>

	
	</div>

</div>
<div id="gym_details_template_container">
	<div class="row area gym_details" id="gym_details_template">
			<div id="gymDetail">
				<div id="gymInfos">
					<div id="circleImage">				
					</div>
					
					<div id="gymName">
						
					
					</div>
					<div id="levelMeter">
						<div class="progress">
							<div class="bar-step  gymRank1" >
								<div class="label-percent">1</div>
							</div>
							<div class="bar-step gymRank2" style="left: 3.84%; width:3.84%;">
								<div class="label-percent ">2</div>
								<div class="label-line"></div>
							</div>
							<div class="bar-step gymRank3" style="left: 7.68%; width:7.68%;">
								<div class="label-percent ">3</div>
								<div class="label-line"></div>
							</div>
							<div class="bar-step gymRank4" style="left: 15.36%; width:7.68%;">
								<div class="label-percent ">4</div>
								<div class="label-line"></div>
							</div>
							<div class="bar-step gymRank5" style="left: 23.4%; width:8.04%;">
								<div class="label-percent ">5</div>
								<div class="label-line"></div>
							</div>
							<div class="bar-step gymRank6" style="left: 30.72%; width:7.32%;">
								<div class="label-percent ">6</div>
								<div class="label-line"></div>
							</div>
							<div class="bar-step gymRank7" style="left: 38.4%; width:19.2%;">
								<div class="label-percent ">7</div>
								<div class="label-line"></div>
							</div>
							<div class="bar-step gymRank8" style="left: 57.6%; width:19.2%;">
								<div class="label-percent ">8</div>
								<div class="label-line"></div>
							</div>
							<div class="bar-step gymRank9" style="left: 76.8%; width:19.2%;">
								<div class="label-percent ">9</div>
								<div class="label-line"></div>
							</div>
							<div class="bar-step gymRank10" style="left: 90%; width:10%;">
								<div class="label-percent">10</div>
								<div class="label-line"></div>
							</div>
							<div class="progress-bar progress-bar-success" id="gymPrestigeBar" ></div>
					   </div>
					</div>
					<div id="gymPrestige">
						Prestige: <span id="gymPrestigeDisplay"></span>
					</div>
					<div id="gymLastScanned">
						Last scanned: <span id="gymLastScannedDisplay"></span>
					</div>
					<div id="gymDefenders" class="pokemon-single">
						No Defender yet
					</div>
				</div>
				
			</div>
	</div>
</div>
