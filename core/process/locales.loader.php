<?php

/**
 * The next 3 functions come from the HTTP2 pear package 
 * Copyright (c) 2002-2005, 
 * Stig Bakken <ssb@fast.no>,
 * Sterling Hughes <sterling@php.net>,
 * Tomas V.V.Cox <cox@idecnet.com>,
 * Richard Heyes <richard@php.net>,
 * Philippe Jausions <Philippe.Jausions@11abacus.com>,
 * Michael Wallner <mike@php.net>.
 * Licensed under http://www.opensource.org/licenses/bsd-license.php  New BSD License
 */

/**
 * Parses and sorts a weighed "Accept" HTTP header
 *
 * @param string $header The HTTP "Accept" header to parse
 *
 * @return array Sorted list of "accept" options
 */
$sortAccept = function ($header) {
	$matches = array();
	foreach (explode(',', $header) as $option) {
		$option = array_map('trim', explode(';', $option));
		$l = strtolower($option[0]);
		if (isset($option[1])) {
			$q = (float) str_replace('q=', '', $option[1]);
		} else {
			$q = null;
			// Assign default low weight for generic values
			if ($l == '*/*') {
				$q = 0.01;
			} elseif (substr($l, -1) == '*') {
				$q = 0.02;
			}
		}
		// Unweighted values, get high weight by their position in the
		// list
		$matches[$l] = isset($q) ? $q : 1000 - count($matches);
	}
	arsort($matches, SORT_NUMERIC);
	return $matches;
};

/**
 * Parses a weighed "Accept" HTTP header and matches it against a list
 * of supported options
 *
 * @param string $header    The HTTP "Accept" header to parse
 * @param array  $supported A list of supported values
 *
 * @return string|NULL a matched option, or NULL if no match
 */
$matchAccept = function ($header, $supported) use ($sortAccept) {
	$matches = $sortAccept($header);
	foreach ($matches as $key => $q) {
		if (isset($supported[$key])) {
			return $supported[$key];
		}
	}
	// If any (i.e. "*") is acceptable, return the first supported format
	if (isset($matches['*'])) {
		return array_shift($supported);
	}
	return null;
};

/**
 * Negotiates language with the user's browser through the Accept-Language
 * HTTP header or the user's host address.  Language codes are generally in
 * the form "ll" for a language spoken in only one country, or "ll-CC" for a
 * language spoken in a particular country.  For example, U.S. English is
 * "en-US", while British English is "en-UK".  Portugese as spoken in
 * Portugal is "pt-PT", while Brazilian Portugese is "pt-BR".
 *
 * Quality factors in the Accept-Language: header are supported, e.g.:
 *      Accept-Language: en-UK;q=0.7, en-US;q=0.6, no, dk;q=0.8
 *
 * @param array  $supported An associative array of supported languages,
 *                          whose values must evaluate to true.
 * @param string $default   The default language to use if none is found.
 *
 * @return string The negotiated language result or the supplied default.
 */
$negotiateLanguage = function ($supported, $default = 'en-US') use ($matchAccept) {
	$supp = array();
	foreach ($supported as $lang => $isSupported) {
		if ($isSupported) {
			$supp[strtolower($lang)] = $lang;
		}
	}
	if (!count($supp)) {
		return $default;
	}
	if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
		$match = $matchAccept(
			$_SERVER['HTTP_ACCEPT_LANGUAGE'],
			$supp
		);
		if (!is_null($match)) {
			return $match;
		}
	}
	if (isset($_SERVER['REMOTE_HOST'])) {
		$domain = explode('.', $_SERVER['REMOTE_HOST']);
		$lang = strtolower(end($domain));
		if (isset($supp[$lang])) {
			return $supp[$lang];
		}
	}
	return $default;
};

// Language setting
###################
if (empty($config->system->forced_lang)) {
	$directories = glob(SYS_PATH.'/core/json/locales/*', GLOB_ONLYDIR);
	$directories = array_map("basename", $directories);
	//print_r($directories);
	$browser_lang = $negotiateLanguage(array_fill_keys($directories, true), $config->system->default_lang);
	//print_r($browser_lang);
} else {
	// Use forced language
	$browser_lang = $config->system->forced_lang;
}

// Activate lang
$locale_dir = SYS_PATH.'/core/json/locales/'.strtoupper($browser_lang);
// Allow partial translations
$translation_file = "{}";
$pokemon_file = "{}";
if (is_file($locale_dir.'/pokes.json')) {
	$pokemon_file = file_get_contents($locale_dir.'/pokes.json');
}
if (is_file($locale_dir.'/translations.json')) {
	$translation_file = file_get_contents($locale_dir.'/translations.json');
}
if (is_file($locale_dir.'/moves.json')) {
	$moves_file	= json_decode(file_get_contents($locale_dir.'/moves.json'));
} else {
	$moves_file	= json_decode(file_get_contents(SYS_PATH.'/core/json/locales/EN/moves.json'));
}


// Merge translation files
// missing translation --> use english
// same keys so translation will
// always overwrite english if available
########################################

$locales = (object) array_replace(json_decode(file_get_contents(SYS_PATH.'/core/json/locales/EN/translations.json'), true), json_decode($translation_file, true));

// Recursive replace because of multi level array
$pokemon_trans_array = array_replace_recursive(json_decode(file_get_contents(SYS_PATH.'/core/json/locales/EN/pokes.json'), true), json_decode($pokemon_file, true));

// convert associative array back to object array (recursive)
$pokemon_trans = json_decode(json_encode($pokemon_trans_array), false);
unset($pokemon_trans_array);


// Merge the pokedex, pokemon translation and rarity file into a new array 
##########################################################################

$pokedex_file 	= file_get_contents(SYS_PATH.'/core/json/pokedex.json');
$pokemons 		= json_decode($pokedex_file);

$pokedex_rarity_file = SYS_PATH.'/core/json/pokedex.rarity.json';
$pokemons_rarity = json_decode(file_get_contents($pokedex_rarity_file));

$pokedex_counts_file = SYS_PATH.'/core/json/pokedex.counts.json';
$pokemon_counts = json_decode(file_get_contents($pokedex_counts_file));

foreach ($pokemons->pokemon as $pokeid => $pokemon) {
	// Merge name and description from translation files
	$pokemon->name 			= $pokemon_trans->pokemon->$pokeid->name;
	$pokemon->description 	= $pokemon_trans->pokemon->$pokeid->description;

	// Replace quick and charge move with translation
	$quick_move 			= $pokemon->quick_move;
	$pokemon->quick_move 	= $pokemon_trans->quick_moves->$quick_move;
	$charge_move 			= $pokemon->charge_move;
	$pokemon->charge_move 	= $pokemon_trans->charge_moves->$charge_move;

	// Replace types with translation
	foreach ($pokemon->types as &$type) {
		$type = $pokemon_trans->types->$type;
	}
	unset($type);

	// Resolve candy_id to candy_name
	if (isset($pokemon->candy_id)) {
		$candy_id 				= $pokemon->candy_id;
		$pokemon->candy_name 	= $pokemon_trans->pokemon->$candy_id->name;
		unset($pokemon->candy_id);
	}
	// Convert move numbers to names
	$move = new stdClass();
	foreach ($moves_file as $move_id => $move_name) {
		if (isset($move_name)) {
			$move->$move_id = new stdClass();
			$move->$move_id->name = $move_name->name;
		}
	}
	
	// Add pokemon counts to array
	$pokemon->spawn_count = $pokemon_counts->$pokeid;

	// Calculate and add rarities to array
	$spawn_rate = $pokemons_rarity->$pokeid->rate;
	$pokemon->spawn_rate = $spawn_rate;
	$pokemon->per_day = $pokemons_rarity->$pokeid->per_day;

	// >= 1          = Very common
	// 0.20 - 1      = Common
	// 0.01 - 0.20   = Rare
	// > 0  - 0.01   = Mythic
	// Unseen
	if ($spawn_rate >= 1) {
		$pokemon->rarity = $locales->VERYCOMMON;
	} elseif ($spawn_rate >= 0.20) {
		$pokemon->rarity = $locales->COMMON;
	} elseif ($spawn_rate >= 0.01) {
		$pokemon->rarity = $locales->RARE;
	} elseif ($spawn_rate > 0 || $pokemon->spawn_count > 0) {
		// pokemon with at least 1 spawn in the past aren't unseen!
		$pokemon->rarity = $locales->MYTHIC;
	} else {
		$pokemon->rarity = $locales->UNSEEN;
	}
}

// Add total pokemon count
$pokemons->total = $pokemon_counts->total;

// Translate typecolors array keys as well
$types_temp = new stdClass();
foreach ($pokemons->typecolors as $type => $color) {
	$type_trans 				= $pokemon_trans->types->$type;
	$types_temp->$type_trans 	= $color;
}
// Replace typecolors array with translated one
$pokemons->typecolors = $types_temp;

// unset unused variables to prevent issues with other php scripts
unset($negotiateLanguage);
unset($matchAccept);
unset($sortAccept);
unset($directories);
unset($browser_lang);
unset($locale_dir);
unset($pokemon_file);
unset($translation_file);
unset($pokedex_file);
unset($pokemon_counts);
unset($moves_file);
unset($pokemon_trans);
unset($types_temp);
unset($type_trans);
unset($pokemons_rarity);
unset($quick_move);
unset($charge_move);
unset($candy_id);
unset($spawn_rate);
