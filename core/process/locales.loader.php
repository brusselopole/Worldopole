<?php 

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
	
	$locale_dir = SYS_PATH.'/core/json/locales/'.$lang;
	
	if(is_dir($locale_dir) && file_exists($locale_dir.'/pokes.json') && file_exists($locale_dir.'/translations.json')){
		
		$pokemon_file 		= file_get_contents($locale_dir.'/pokes.json');
		$translation_file 	= file_get_contents($locale_dir.'/translations.json');
		
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


// Merge translation files
// missing translation --> use english
// same keys so translation will
// always overwrite english if available
########################################


$locales		= (object) array_merge((array) json_decode(file_get_contents(SYS_PATH.'/core/json/locales/EN/translations.json')), (array) json_decode($translation_file));
$pokemons		= (object) array_merge((array) json_decode(file_get_contents(SYS_PATH.'/core/json/locales/EN/pokes.json')), (array) json_decode($pokemon_file));



// Merge the pokedex & pokemon file into a new array 
#####################################################

$i=0;

foreach(json_decode($pokedex_file) as $datas){

	foreach($datas as $key => $value){
		
		if(!empty($pokemons->{$i})){
			
			$pokemons->{$i}->{$key} = $value;
		}
		else{
			
			$pokemons->{$i} = new StdClass;
			$pokemons->{$i}->{$key} = $value;
			
		}
		
	}
	
	$i++; 
}

?>
