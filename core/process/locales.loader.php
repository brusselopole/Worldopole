<?php 

global $lang; 

// Language setting
###################

if(isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])){

	$browser_lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);

}else{

	$browser_lang = 'en';

}


// Activate lang
$lang = strtoupper($browser_lang); 


// Check if language is available
if(isset($lang)){
	
	$locale_dir = SYS_PATH.'/core/json/'.$lang;
	
	if(is_dir($locale_dir)){
		
		$pokemon_file 		= file_get_contents(SYS_PATH.'/core/json/locales/'.$lang.'/pokes.json'); 
		$translation_file 	= file_get_contents(SYS_PATH.'/core/json/locales/'.$lang.'/translations.json'); 
		 

	}
	
	// If there's no pokedex in languague we'll use the english one. 
	else{
		
		$pokemon_file 		= file_get_contents(SYS_PATH.'/core/json/locales/EN/pokes.json'); 
		$translation_file 	= file_get_contents(SYS_PATH.'/core/json/locales/EN/translations.json');
		
	}	 
	 
}else{

	$pokemon_file 		= file_get_contents(SYS_PATH.'/core/json/locales/EN/pokes.json'); 
	$translation_file 	= file_get_contents(SYS_PATH.'/core/json/locales/EN/translations.json');

}


// JSON globale file for Pokémon 
################################

$pokedex_file	= file_get_contents(SYS_PATH.'/core/json/pokedex.json');



// Loading JSON files 
#####################

$pokemons			= json_decode($pokemon_file);
$locales 			= json_decode($translation_file); 
$pokemons_data		= json_decode($pokedex_file); 


// Merge the pokedex & pokemon file into a new array 
#####################################################

$i=0; 

foreach($pokemons_data as $datas){
	
	foreach($datas as $key => $value){
	
		$pokemons->{$i}->{$key} = $value;
		
	}
	
	$i++; 
}

	
?>