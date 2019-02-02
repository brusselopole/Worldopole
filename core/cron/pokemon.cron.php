<?php
	
// -----------------------------------------------------------------------------------------------------------
// Pokemons datas 
// Total pokemon available
// -----------------------------------------------------------------------------------------------------------


// This file is used to rank by rarity 

// Load the pokemons array
// crontabs.include.php forces english lang
include_once(SYS_PATH.'/core/process/locales.loader.php');


$pokemon_stats['timestamp'] = $timestamp;

$pokemon_stats['pokemon_now'] = $manager->getTotalPokemon()->total;

$counts = $manager->getPokemonCountsActive();

$rarityarray = array();
foreach ($counts as $poke_id => $total) {
	$rarity = $pokemons->pokemon->$poke_id->rarity;
	isset($rarityarray[$rarity]) ? $rarityarray[$rarity]+=$total : $rarityarray[$rarity]=$total;
}

// Set amount of Pokemon for each rarity to 0 if there weren't any at that time
isset($rarityarray['Very common']) ?: $rarityarray['Very common'] = 0;
isset($rarityarray['Common']) ?: $rarityarray['Common'] = 0;
isset($rarityarray['Rare']) ?: $rarityarray['Rare'] = 0;
isset($rarityarray['Mythic']) ?: $rarityarray['Mythic'] = 0;

$pokemon_stats['rarity_spawn'] = $rarityarray;


// Write to file
$pokedatas[] = $pokemon_stats;
file_put_contents($pokemonstats_file, json_encode($pokedatas));
