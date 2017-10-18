<header id="single-header">
	<div class="row">
		<div class="col-md-12 text-center">
			<h1>
				<?= sprintf($locales->POKESTOPS_TITLE, $config->infos->city); ?>
			</h1>
			
		</div>
	</div>
</header>


<div class="row area">

	<div class="col-md-6 col-sm-6 col-xs-12 big-data" style="border-right:1px lightgray solid;"> <!-- POKESTOPS -->
		<img src="core/img/pokestop.png" alt="Pokestop" width=50 class="big-icon">
		<p><big><strong><?= $pokestop->total ?></strong> <?= $locales->POKESTOPS ?></big><br> <?= sprintf($locales->INCITY, $config->infos->city); ?></p>
	</div>

	<div class="col-md-6 col-sm-6 col-xs-12 big-data"> <!-- LURED STOPS -->
		<img src="core/img/lure-module.png" alt="Lured Pokestop" width=50 class="big-icon">
		<p><big><strong><?= $pokestop->lured ?></strong> <?= $locales->LURES ?></big><br> <?= $locales->POKESTOPS_LURES ?></p>
	</div>
</div>


<div class="row text-center subnav">
    <div class="btn-group" role="group">
        <a class="btn btn-default active" id="pokestopSelector"><i class="fa fa-medkit"></i> <?= $locales->POKESTOPS ?></a>
        <a class="btn btn-default " id="lureSelector"><i class="fa fa-eye"></i> <?= $locales->LURES ?></a>
    </div>
</div>

<div class="row">
	
	<div class="col-md-12">
	
		<div id="map">
		
		</div>
	
	</div>

</div>
