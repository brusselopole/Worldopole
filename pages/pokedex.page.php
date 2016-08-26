<header id="single-header">
<div class="row">
	<div class="col-md-12 text-center">
		<h1>
			<?= $locales->POKEDEX_TITLE->$lang ?>
		</h1>
		
	</div>
</div>
</header>

<div class="row">

	<div class="col-md-12">
	
		<div class="search form-group">
	    	<input type="search" class="form-control" placeholder="search here" required>
		</div>
	
	</div>
	
	<div class="col-md-12 flex-container results">
	

		<?php foreach($pokedex as $pokemon){ ?>
			
			<div class="flex-item pokemon-single">
			
				<a href="<?= $pokemon->permalink ?>"><img src="<?= $pokemon->img ?>" alt="<?= $pokemon->name ?>" class="img-responsive <?php if($pokemon->spawn == 0){ echo 'unseen'; } ?> "></a>
				<p class="pkmn-name"><a href="<?= $pokemon->permalink ?>"><?= $pokemon->name ?></a></p>
				<p><?php  if($pokemon->spawn ==0){ echo $locales->UNSEEN->$lang ; }else{ echo $pokemon->spawn. $locales->SEEN->$lang ; } ?> </p>
			
			</div>
			
		<?php }?>
	

	</div>			
	
</div>
