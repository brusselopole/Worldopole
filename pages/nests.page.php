<header id="single-header">
	<div class="row">
		<div class="col-md-12 text-center">
			<h1>
				<?= $locales->NESTS_TITLE ?>
			</h1>
			<br>
			<h4><p><?= $locales->NESTS_MIGRATIONTEXT ?></p><p id="migration"></p></h4>
		</div>
	</div>
</header>

<div class="row text-center subnav">
    <div class="btn-group" role="group">
        <a class="btn btn-default active" id="showNests"><i class="fa fa-tree"></i> <?= $locales->NESTS_NESTS ?></a>
        <a class="btn btn-default" id="showFrequentSpawns"><i class="fa fa-binoculars"></i> <?= $locales->NESTS_FREQUENT_SPAWNPOINTS ?></a>
    </div>
</div>

<div class="row">
	<div class="col-md-12 text-center">
		<div id="map">

		</div>
	</div>
</div>
