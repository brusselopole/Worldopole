<!-- Header -->
<header id="single-header">
	
	<!-- Breadcrumb -->
	<div class="row">
		<div class="col-md-12">
			<ol class="breadcrumb">
				<li><a href="<?= HOST_URL ?>/"><?= $locales->HOME->$lang ?></a></li>
				<li><a href="<?= HOST_URL ?>/pokemon"><?= $locales->NAV_POKEDEX->$lang ?></a></li>
				<li class="active"><?= $pokemon->name ?></li>
			</ol>
		</div>
	</div>
	<!-- /Breadcrumb -->

	<div class="row">
		
		<div class="col-sm-1 hidden-xs">
				
				<?php if($pokemon->id-1 > 0){ ?>
			
				<p align="left" class="nav-links"><a href="<?= HOST_URL ?>/pokemon/<?= $pokemon->id-1 ?>"><i class="fa fa-chevron-left"></i></a></p>
			
				<?php }?>
				
		</div>
		
		<div class="col-sm-10 text-center">
			
			<h1><strong><?= $pokemon->name ?></strong><br>
			<small>[<?= $pokemon->rarity ?>]</small></h1>

			<p id="share">
				<a href="https://www.facebook.com/sharer/sharer.php?u=<?= HOST_URL ?>/pokemon/<?= $pokemon->id ?>" target="_blank" class="btn btn-primary" title="Share on Facebook"><?= $locales->SHARE->$lang ?> <i class="fa fa-facebook" aria-hidden="true"></i></a> 
				
				<a href="https://twitter.com/intent/tweet?source=<?= HOST_URL ?>/pokemon/<?= $pokemon_id ?>&text=Find <?= $pokemon->name ?> in Brussels <?= HOST_URL ?>/pokemon/<?= $pokemon->id ?>" target="_blank" title="Share on Twitter" class="btn btn-info"><?= $locales->SHARE->$lang ?> <i class="fa fa-twitter" aria-hidden="true"></i></a>
			</p>
			
		</div>
		
		
		<div class="col-sm-1 hidden-xs">
			
			<?php if($pokemon->id+1 < $config->system->max_pokemon ){ ?>
			
			<p align="right" class="nav-links"><a href="<?= HOST_URL ?>/pokemon/<?= $pokemon->id+1 ?>"><i class="fa fa-chevron-right"></i></a></p>
				
			<?php } ?>
		</div>
		
	</div>

</header>
<!-- /Header -->


<div class="row">

	<div class="col-md-2 col-xs-4">
		<div id="poke-img" style="padding-top:15px;margin-bottom:1em;">
			<img class="media-object img-responsive" src="/core/pokemons/<?= $pokemon->id ?>.png" alt="<?= $pokemon->name ?> model" >
		</div>
	</div>
	
	<div class="col-md-4 col-xs-8" style="margin-bottom:1em;">

		<div class="media">
			<div class="media-body" style="padding-top:25px;">
				
				<p><?= $pokemon->description ?></p>

				<p>
				<?php foreach($pokemon->types as $type){ ?>
					<span class="label label-default" style="background-color:<?= $type->color ?>"><?= $type->type ?></span>
				<?php }?>
				</p>
				
			</div>
		</div>

	</div>

	<div class="col-md-6" style="padding-top:10px;">
		<canvas id="myChart" width="100%" height="40"></canvas>
	</div>

</div>



<div class="row">
	<div class="col-md-6" style="padding-top:10px;">
		
		<table class="table">
			<tr>
				<td class="col-md-8 col-xs-8"><strong><?= $locales->POKEMON_SEEN->$lang ?></strong></td>
				<td class="col-md-4 col-xs-4">
				
				<?php if(isset($pokemon->last_position)){ ?>
				
					<a href="http://maps.google.com/maps?z=11&t=m&q=loc:<?= $pokemon->last_position->latitude ?>+<?= $pokemon->last_position->longitude ?>" target="_blank"><?= time_ago($pokemon->last_seen) ?></a>
				
				<?php }else{?>
				
					We miss it
				
				<?php }?>
				
				</td>
			</tr>
			<tr>
				<td class="col-md-8 col-xs-8"><strong><?= $locales->POKEMON_AMOUNT->$lang ?> :</strong></td>
				<td class="col-md-4 col-xs-4"><?= $pokemon->total_spawn ?> <?= $locales->SEEN->$lang ?></td>
			</tr>
			<tr>
				<td class="col-md-8 col-xs-8"><strong><?= $locales->POKEMON_RATE->$lang ?> :</strong></td>
				<td class="col-md-4 col-xs-4"><?= $pokemon->spawn_rate ?> / day</td>
			</tr>
			<tr>
				<td class="col-md-8 col-xs-8"><?php if(isset($pokemon->protected_gyms)) { echo "<strong>" . $locales->POKEMON_GYM->$lang . $pokemon->name . "</strong> :";} ?></td>
				<td class="col-md-4 col-xs-4"><?php if(isset($pokemon->protected_gyms)) { echo $pokemon->protected_gyms ;}?></td>
			</tr>
		</table>
		</div>
	
		<div class="col-md-6" style="padding-top:10px;">
		<table class="table">
			<tr>
				<td class="col-md-8 col-xs-8"><strong><?= $locales->POKEMON_EVOLUTION->$lang ?> :</strong></td>
				<td class="col-md-4 col-xs-4"><?php if(isset($pokemon->candies)) { echo  $pokemon->candies . "&nbsp;". $pokemon->candy_name . " candies" ;} else { echo $locales->POKEMON_FINAL->$lang; } ?></td>
			</tr>
			<tr>
				<td class="col-md-8 col-xs-8"><strong><?= $locales->POKEMON_QUICK->$lang ?> :</strong></td>
				<td class="col-md-4 col-xs-4"><?= $pokemon->quick_move ?></td>
			</tr>
			<tr>
				<td class="col-md-8 col-xs-8"><strong><?= $locales->POKEMON_SPECIAL->$lang ?> :</strong> </td>
				<td class="col-md-4 col-xs-4"><?= $pokemon->charge_move ?></p></td>
			</tr>
	
		</table>
		
	</div>
</div>



<div class="row text-center" id="subnav">
	<div class="btn-group" role="group">
	  <a class="btn btn-default" href="#where" class="page-scroll"><i class="fa fa-map-marker"></i> <?= $locales->POKEMON_MAP->$lang ?></a>
	  <a class="btn btn-default" href="#stats" class="page-scroll"><i class="fa fa-pie-chart"></i> <?= $locales->POKEMON_STATS->$lang ?></a>
	 <a class="btn btn-default" href="#family" class="page-scroll"><i class="fa fa-share-alt"></i> <?= $locales->POKEMON_FAMILY->$lang ?></a>
	</div>
</div>




<div class="row area" id="where">
	
	<div class="col-md-12">
		
		<h2 class="text-center sub-title"><?= $locales->POKEMON_WHERE->$lang ?> <?= $pokemon->name ?>?</h2>
			
	</div>
	
	<div class="col-md-12">
		<div id="map">
	
		</div>
	</div>
	
</div>




<div class="row area" id="stats">
	
	<h2 class="text-center sub-title"><strong><?= $pokemon->name ?></strong>  <?= $locales->POKEMON_BREAKDOWN->$lang ?></h2>
	
	
	<!-- CP Datas -->
	<div class="col-md-3 stats-data">
		
		<p><big><?= $pokemon->max_cp ?></big><br/><?= $locales->POKEMON_CP->$lang ?></p>

		<div class="progress" style="margin-bottom:0;">
		  <div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="<?= $pokemon->max_cp_percent ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?= $pokemon->max_cp_percent ?>%;min-width:30%;">
		    <?= $pokemon->max_cp_percent ?> %
		  </div>
		</div>
		
		<?= $locales->POKEMON_COMPGAME->$lang ?>

	</div>
	
	<!-- Chart -->
	<div class="col-md-6">
		
		<canvas id="myPolarChart" width="100%" height="60"></canvas>
		
	</div>
	<!-- /Chart --> 
	
	<!-- PV Datas --> 
	<div class="col-md-3 stats-data">
		
		<p><big><?= $pokemon->max_pv ?></big><br/><?= $locales->POKEMON_HP->$lang ?></p>

		<div class="progress" style="margin-bottom:0;">
		  <div class="progress-bar progress-bar-success progress-bar-striped active" role="progressbar" aria-valuenow="<?= $pokemon->max_pv_percent ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?= $pokemon->max_pv_percent ?>%;min-width:30%;">
		    <?= $pokemon->max_pv_percent ?> %
		  </div>
		</div>
		
		<?= $locales->POKEMON_COMPGAME->$lang ?>
		
	</div>
	
	
</div>





<div class="row" id="family">

	<div class="col-md-12">
		
		<h2 class="text-center sub-title"><strong><?= $pokemon->name ?></strong><?= $locales->POKEMON_FAMILYTITLE->$lang ?></h2>
		
		<div class="row">
		
		<?php
			
		foreach($related as $related_mon){
			
			?>
			
			<div class="col-md-1 col-sm-2 col-xs-3 pokemon-single">
			
				<a href="/pokemon/<?= $related_mon ?>">
					<img src="/core/pokemons/<?= $related_mon ?>.png" alt="<?= $pokemons->$related_mon->name ?>.png" class="img-responsive">
				</a>
			
			</div>
			
			
			<?php
			
		}
					
		?>
		
		</div>
		
	
	</div>

</div>


