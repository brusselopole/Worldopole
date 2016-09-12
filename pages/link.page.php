<header id="single-header">
<div class="row">
	<div class="col-md-12 text-center">
		<h1>
			<?= $locales->LINK_TITLE->$lang ?>
		</h1>
		
	</div>
</div>
</header>







<div class="row linkpage">
	<div class="row worldopole">
		
		
		<?php

		if(!isset($config->linkmenu)){
			
			echo "Please update variables.json file with menu values";
			exit(); 
			
		}
		
		
		foreach($config->linkmenu as $menu){
			
			if(isset($menu->locale)){
			
				$locale = $menu->locale; 
				$text 	=  $locales->$locale->$lang;
			
			}else{
			
				$text 	= $menu->text; 
			
			}
			
			
			switch($menu->type){
				
				case 'link':
				
				?>
				<div class=container">
				
				<div class="col-md-3 linkstyle  text-center">
					<p><?= $menu->text ?></p>
					<p><a href="<?= $menu->href ?>" target="_blank" class="btn btn-primary"><?= $menu->title ?></a></p>
				</div>
				
				<?php
				
				break;
				
				case 'link_external':
				
				?>
				
				<div class="col-md-3 linkstyle  text-center">
					<p><?= $menu->text ?></p>
					<p><a href="<?= $menu->href ?>" target="_blank" class="btn btn-primary"><?= $menu->title ?></a></p>
				</div>
				
				<?php
				
				break; 
				
				case 'html':
				
				?>
				
				<li> <?= $menu->value ?> </li>
				
				<?php
					
				break; 

			}
			
			
			
		}
			
		?>
		
		
		
	</div>
	

</div>

<div class="row area">
	<div class="col-md-12">

	</div>

</div>