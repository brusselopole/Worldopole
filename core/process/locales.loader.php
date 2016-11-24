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

$pokedex_file		= SYS_PATH.'/core/json/pokedex.json';
$pokedex_file_content	= file_get_contents($pokedex_file);


// Merge translation files
// missing translation --> use english
// same keys so translation will
// always overwrite english if available
########################################

$locales		= (object) array_merge((array) json_decode(file_get_contents(SYS_PATH.'/core/json/locales/EN/translations.json')), (array) json_decode($translation_file));
// TODO fix doesn't work yet
$pokemon_trans	= (object) array_merge((array) json_decode(file_get_contents(SYS_PATH.'/core/json/locales/EN/pokes.json')), (array) json_decode($pokemon_file));


// Merge the pokedex & pokemon file into a new array 
#####################################################

$pokemons = json_decode($pokedex_file_content);

foreach ($pokemons->pokemon as $pokeid => $pokemon) {
	// Merge name and description from translation files
	$pokemon->name 				= $pokemon_trans->pokemon->$pokeid->name;
	$pokemon->description 		= $pokemon_trans->pokemon->$pokeid->description;

	// Replace quick and charge move with translation
	$quick_move 				= $pokemon->quick_move;
	$pokemon->quick_move 		= $pokemon_trans->quick_moves->$quick_move;
	$charge_move 				= $pokemon->charge_move;
	$pokemon->charge_move 		= $pokemon_trans->charge_moves->$charge_move;

	// Replace types with translation
	foreach ($pokemon->types as $idx => $type) {
		$pokemon->types[$idx] = $pokemon_trans->types->$type;
	}

	// Resolve candy_id to candy_name
	if(isset($pokemon->candy_id)) {
		$candy_id 				= $pokemon->candy_id;
		$pokemon->candy_name	= $pokemon_trans->pokemon->$candy_id->name;
		unset($pokemon->candy_id);
	}

	// Calculate and add rarities to array
	$spawn_rate = $pokemon->spawn_rate;
	// >= 1          = Very common
	// 0.20 - 1      = Common
	// 0.01 - 0.20   = Rare
	// > 0  - 0.01   = Mythic
	// Unseen
	if($spawn_rate >= 1){
		$pokemon->rarity = $locales->VERYCOMMON;
	}elseif($spawn_rate >= 0.20){
		$pokemon->rarity = $locales->COMMON;
	}elseif($spawn_rate >= 0.01){
		$pokemon->rarity = $locales->RARE;
	}elseif($spawn_rate > 0){
		$pokemon->rarity = $locales->MYTHIC;
	}else{
		$pokemon->rarity = $locales->UNSEEN;
	}
}

// Translate typecolors array keys as well
$types_temp = new stdClass();
foreach($pokemons->typecolors as $type => $color) {
	$type_trans = $pokemon_trans->types->$type;
	$types_temp->$type_trans = $color;
}
// Replace typecolors array with translated one
$pokemons->typecolors = $types_temp;

// unset unused variables to prevent issues with other php scripts
unset($browser_lang);
unset($lang);
unset($locale_dir);
unset($pokemon_file);
unset($translation_file);
unset($pokemon_trans);
unset($types_temp);
unset($type_trans);
unset($quick_move);
unset($charge_move);
unset($candy_id);
unset($spawn_rate);

?>
