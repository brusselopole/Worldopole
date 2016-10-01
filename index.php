<?php

include_once('config.php');
include_once('functions.php');	
include_once('core/process/data.loader.php');

?>


<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
   
   
   <?php include_once('core/inc/meta.inc.php') ?>

    <!-- Bootstrap -->
    <link href="core/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Lato:400,300,700" rel="stylesheet" type="text/css">
	<link href="core/css/font-awesome.min.css" rel="stylesheet">    
    <link href="core/css/style.css" rel="stylesheet">

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
        
    
  </head>
  <body id="page-top" data-spy="scroll" data-target=".navbar-fixed-top">
  
	<nav class="navbar navbar-default navbar-fixed-top">
		<div class="container">
			<!-- Brand and toggle get grouped for better mobile display -->
			<div class="navbar-header">
				<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#menu" aria-expanded="false">
				<span class="sr-only">Toggle navigation</span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				</button>
				<a class="navbar-brand" href="<?= HOST_URL ?>"><img src="<?= $config->infos->logo_path ?>" width="25" style="display:inline-block;" alt="Brusselopole" id="logo-img" /> <?= $config->infos->site_name ?></a>
			</div>
			
			<!-- Collect the nav links, forms, and other content for toggling -->
			<div class="collapse navbar-collapse" id="menu">
				
				<ul class="nav navbar-nav navbar-right">
					
					<?php
					
					
					if(!isset($config->menu)){
						
						echo "Please update variables.json file with menu values";
						exit(); 
						
					}
					
					
					foreach($config->menu as $menu){
						
						if(isset($menu->locale)){
						
							$locale = $menu->locale; 
							$text 	=  $locales->$locale->$lang;
						
						}elseif(isset($menu->text)){
							$text 	= $menu->text; 
						
						}
						
						
						switch($menu->type){
							
							case 'link':
							
							?>
							
							<li>
								<a href="<?= $menu->href ?>" class="menu-label"><i class="fa <?= $menu->icon ?>" aria-hidden="true"></i> <?= $text ?></a>
							</li>
							
							<?php
							
							break;
							
							case 'link_external':
							
							?>
							
							<li>
								<a href="<?= $menu->href ?>" class="menu-label"><i class="fa <?= $menu->icon ?>" aria-hidden="true"></i> <?= $menu->text ?></a>
							</li>
							
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
					
				</ul>
				
			</div><!-- /.navbar-collapse -->
		</div><!-- /.container-fluid -->
	</nav>  
 
 
	<div class="container">
			
		
		<?php
	
		# Include the pages
		
		if (!empty($_GET['page'])) {
			
			$file = SYS_PATH.'/pages/'.$page.'.page.php';
			
			if(file_exists($file)){
				
				echo '<!-- Page :: '.$page.' -->';
				include($file);
				
			}
			else{
				
				include('pages/home.page.php');
				
			}
							
		
		} else {
		
			include('pages/home.page.php');
		
		}	
			
		?>
			
		

	
	</div>


		<footer>
			<div class="container">
				<div class="row">

					<div class="col-md-12 text-center">
						<img src="core/img/logo.png" width=50 class="big-icon" alt="Brusselopole icon">
						<h2><?= $locales->FOOTER_TITLE->$lang ?></h2>
						<p><?= $locales->FOOTER_SUB->$lang ?></p>
						<?= $locales->FOOTER_VISUAL_CONTENT->$lang ?>

						<p class="text-center">Made in Brussels<br/>Based on <a href="https://github.com/brusselopole">Brusselopole</a> by <a href="http://56k.be/" target="_blank">56k</a> & <a href="http://vandereecken.me" target="_blank">Nithou</a></p>
					</div>
					
		</div>

	</div>
</footer>

      

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <script src="core/js/bootstrap.min.js"></script>
   
	
    
    <?php // Load scripts only for page ?>
    
    <?php if(empty($page)){?>
    
    	<script src="core/js/home.script.js"></script>
    	
    	<script>
	    	
	    	updateCounter(<?= $home->pokemon_now ?>,'.total-pkm-js');
        	updateCounter(<?= $home->pokestop_lured ?>,'.total-lure-js');
        	updateCounter(<?= $home->gyms ?>,'.total-gym-js');
        	
			
		updateCounter(<?= $home->teams->valor ?>,'.total-valor-js');
        	updateCounter(<?= $home->teams->mystic ?>,'.total-mystic-js');
        	updateCounter(<?= $home->teams->instinct ?>,'.total-instinct-js');
        	updateCounter(<?= $home->teams->rocket ?>,'.total-new-js');
				    	
	    </script>
    
    <?php 
	
	}else{ 
	    
	    switch($page){
		    
		    
		    case 'pokemon': ?>
		    
			<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.2.1/Chart.min.js"></script>
			<script src="core/js/pokemon.graph.js.php?id=<?= $pokemon_id ?>"></script>	
	
			
			<script src="core/js/pokemon.maps.js.php?id=<?= $pokemon_id ?>"></script>
			<script async defer src="https://maps.googleapis.com/maps/api/js?key=<?= $config->system->GMaps_Key ?>&libraries=visualization&callback=initMap"></script> 		
		    
		    <?php ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
			    
			break; 
		    
		    case 'pokestops': ?>
		    
			<script src="core/js/pokestops.maps.js"></script>
			<script async defer src="https://maps.googleapis.com/maps/api/js?key=<?= $config->system->GMaps_Key ?>&libraries=visualization&callback=initMap"></script> 
		    
		    <?php ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
			    
			break;
		    
		    case 'gym': ?>
			
			<script src="core/js/gym.script.js"></script>
			<script>
				updateCounter(<?= $teams->valor->gym_owned ?>,'.gym-valor-js');
				updateCounter(<?= $teams->valor->average ?>,'.average-valor-js');
	        	
				updateCounter(<?= $teams->instinct->gym_owned ?>,'.gym-instinct-js');
				updateCounter(<?= $teams->instinct->average ?>,'.average-instinct-js');
	        	
				updateCounter(<?= $teams->mystic->gym_owned ?>,'.gym-mystic-js');
				updateCounter(<?= $teams->mystic->average ?>,'.average-mystic-js');	
			</script>
	
			<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.2.1/Chart.min.js"></script>
			<script src="core/js/gym.graph.js.php"></script>

			<script src="core/js/gym.maps.js"></script>
			<script async defer src="https://maps.googleapis.com/maps/api/js?key=<?= $config->system->GMaps_Key ?>&libraries=visualization&callback=initMap"></script>
			<?php ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
				
			break;
				
			case 'pokedex': ?>
			
			<script src="core/js/holmes.js"></script>
			
			<script src="core/js/microlight.js"></script>
			
			<script>
			
			// holmes setup
			var h = new holmes({
				input: '.search input',
				find: '.results .pokemon-single',
				placeholder: '<h3>— No results, my dear Ash. —</h3>',
				class: {
					visible: 'visible',
					hidden: 'hidden'
				}
			});
			
			</script>
			
			
			<?php ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
				
			break;
			
			case 'dashboard': ?>
			
			<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.2.1/Chart.min.js"></script>
			<script src="core/js/dashboard.graph.js.php"></script>	

			
			<?php ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
			
			break; 
		    
	    }
	}
    ?>
	    
  </body>
</html>
