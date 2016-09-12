<header id="single-header">
	<div class="row">
		<div class="col-md-12 text-center">
			<h1>
				<?= sprintf($locales->POKESTOPS_TITLE->$lang, $config->infos->city); ?>
			</h1>
			
		</div>
	</div>
</header>


<div class="row area">

	<div class="col-md-6 col-sm-6 col-xs-12 big-data"  style="border-right:1px lightgray solid;"> <!-- POKESTOPS -->
		<a href="<?= HOST_URL ?>/pokemon"><img src="core/img/pokestop.png" alt="Pokestop" width=50 class="big-icon"></a>
		<p><big><strong><?= $pokestop->total ?></strong> Pokestops</big><br> <?= sprintf($locales->INCITY->$lang, $config->infos->city); ?></p>
	</div>

	<div class="col-md-6 col-sm-6 col-xs-12 big-data"> <!-- LURED STOPS -->
		<a href="<?= HOST_URL ?>/pokemon"><img src="core/img/lure-module.png" alt="Lured Pokestop" width=50 class="big-icon"></a>
		<p><big><strong><?= $pokestop->lured ?></strong> <?= $locales->LURES->$lang ?></big><br> <?= $locales->POKESTOPS_LURES->$lang ?></p>
	</div>
</div>



<div class="row area">
	
	<div class="col-md-12">
	
		<div id="map">
		
		</div>
	
	</div>

</div>
