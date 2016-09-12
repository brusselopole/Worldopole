<header id="single-header">
	<div class="row">
		<div class="col-md-12 text-center">
			<h1>
				<?= sprintf($locales->SPAWNPOINTS_TITLE->$lang, $config->infos->city); ?>
			</h1>
			
		</div>
	</div>
</header>


<div class="row area">

	<div class="col-md-6 col-sm-6 col-xs-12 big-data"  style="border-right:1px lightgray solid;"> <!-- POKESTOPS -->
		<a href="<?= HOST_URL ?>/pokemon"><img src="core/img/pawprints.png" alt="Spawn points" width=50 class="big-icon"></a>
		<p><big><strong><?= $spawnpoint->total ?></strong> <?= $locales->SPAWNPOINTS->$lang ?></big><br> <?= sprintf($locales->INCITY->$lang, $config->infos->city); ?></p>
	</div>

	<div class="col-md-6 col-sm-6 col-xs-12 big-data"> <!-- LURED STOPS -->
		<a href="<?= HOST_URL ?>/pokemon"><img src="core/img/gotcha.png" alt="Active spawns" width=50 class="big-icon"></a>
		<p><big><strong><?= $spawnpoint->active ?></strong><?= $locales->ACTIVE_SPAWNPOINTS->$lang ?></big></p>
	</div>
</div>



<div class="row area">
	
	<div class="col-md-12">
	
		<div id="map">
		
		</div>
	
	</div>

</div>
