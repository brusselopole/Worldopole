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

<div class="row">
	
	<div class="col-md-12">

		<div class="row area">
		
			<div class="col-md-12">
				<h2 class="sub-title"><?= $locales->DASHBOARD_SPAWN_TITLE ?></h2>
			</div>
		
			<div class="col-md-12">
			
				<h4><?= $locales->DASHBOARD_SPAWN_TOTAL ?> <small><?= $locales->DASHBOARD_LAST7DAYS ?></small></h4>
				<div style="height:30vh">
					<canvas id="total_spawn"></canvas>
				</div>
			</div>
			
			<div class="col-md-3">
				
				<h4><?= $locales->VERYCOMMON ?> <small><?= $locales->DASHBOARD_LAST24HOURS ?></small></h4>
				<div style="height:15vh">
					<canvas id="very_common"></canvas>
				</div>
			</div>
			
			<div class="col-md-3">
				
				<h4><?= $locales->COMMON ?> <small><?= $locales->DASHBOARD_LAST24HOURS ?></small></h4>
				<div style="height:15vh">
					<canvas id="common"></canvas>
				</div>
			
			</div>
			
			<div class="col-md-3">
				
				<h4><?= $locales->RARE ?> <small><?= $locales->DASHBOARD_LAST24HOURS ?></small></h4>
				<div style="height:15vh">
					<canvas id="rare"></canvas>
				</div>
			</div>
			
			<div class="col-md-3">
				
				<h4><?= $locales->MYTHIC ?> <small><?= $locales->DASHBOARD_LAST24HOURS ?></small></h4>
				<div style="height:15vh">
					<canvas id="mythics"></canvas>
				</div>
			</div>
		
		</div>
		
		<div class="row area">
			
			<div class="col-md-12">
				<h2 class="sub-title"><strong><?= $locales->TEAM ?></strong> <?= $locales->PERFORMANCE ?></h2>
			</div>
			
			<div class="col-md-12">
				<h4><?= $locales->DASHBOARD_PRESTIGE_AVERAGE ?> <small><?= $locales->DASHBOARD_LAST7DAYS ?></small></h4>
			</div>
			
			<div class="col-md-12">
				<div style="height:30vh">
					<canvas id="team_av"></canvas>
				</div>
			</div>
		
			
			<div class="col-md-12">
				<h4><?= $locales->DASHBOARD_GYM_OWNED_PERFORMANCE ?> <small><?= $locales->DASHBOARD_LAST7DAYS ?></small></h4>
			</div>
			
			<div class="col-md-12">
				<div style="height:30vh">
					<canvas id="team_gym"></canvas>
				</div>
			</div>
			
		
		</div>

<?php if (!$config->system->no_lures === true) { ?>

    <?php if ($config->system->captcha_support) { ?>
        <div class="row area">
    <?php } else { ?>
        <div class="row">
    <?php } ?>

			<div class="col-md-12">
				<h2 class="sub-title"><strong><?= $locales->POKESTOPS ?></strong> <?= $locales->DASHBOARD_ACTIVITY ?></h2>
			</div>
		
			<div class="col-md-12">
			
				<h4><?= $locales->DASHBOARD_LURES ?> <small><?= $locales->DASHBOARD_LAST7DAYS ?></small></h4>
				<div style="height:30vh">
					<canvas id="lures"></canvas>
				</div>
			</div>

		</div>

<?php } ?>


<?php if ($config->system->captcha_support) { ?>
		<div class="row">

			<div class="col-md-12">
				<h2 class="sub-title"><strong>reCaptcha</strong> <?= $locales->DASHBOARD_ACTIVITY ?></h2>
			</div>

			<div class="col-md-12">
			
				<h4><?= $locales->DASHBOARD_CAPTCHA ?> <small><?= $locales->DASHBOARD_LAST7DAYS ?></small></h4>
				<div style="height:30vh">
					<canvas id="captcha"></canvas>
				</div>
			</div>


		</div>
<?php } ?>

	
	</div>


</div>
