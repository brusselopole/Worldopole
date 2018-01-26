<?php

include_once __DIR__ . '/../../../config.php';
include_once __DIR__ . '/QueryManagerMysql.php';

abstract class QueryManager {

	protected static $time_offset;
	protected static $config;

	private static $current;
	public static function current() {
		global $time_offset;

		if (self::$current == null) {

			$variables = realpath(dirname(__FILE__)) . '/../../json/variables.json';
			self::$config = json_decode(file_get_contents($variables));

			include_once(SYS_PATH.'/core/process/timezone.loader.php');
			self::$time_offset = $time_offset;

			switch (self::$config->system->db_type) {
				case "monocle-hydro":
					self::$current = new QueryManagerMonocleHydro();
					break;

				default:
					self::$current = new QueryManagerRocketmap();
					break;
			}
		}
		return self::$current;
	}
	private function __construct(){}

	// Misc
	public abstract function getEcapedString($string);

	// Tester
	public abstract function testTotalPokemon();
	public abstract function testTotalGyms();
	public abstract function testTotalPokestops();

	// Homepage
	public abstract function getTotalPokemon();
	public abstract function getTotalLures();
	public abstract function getTotalGyms();
	public abstract function getTotalRaids();
	public abstract function getTotalGymsForTeam($team_id);
	public abstract function getRecentAll();
	public abstract function getRecentMythic($mythic_pokemon);

	// Single Pokemon
	public abstract function getGymsProtectedByPokemon($pokemon_id);
	public abstract function getPokemonLastSeen($pokemon_id);
	public abstract function getTop50Pokemon($pokemon_id, $best_order, $best_direction);
	public abstract function getTop50Trainers($pokemon_id, $best_order, $best_direction);
	public abstract function getPokemonHeatmap($pokemon_id, $start, $end);
	public abstract function getPokemonGraph($pokemon_id);
	public abstract function getPokemonLive($pokemon_id, $ivMin, $ivMax);
	public abstract function getPokemonSliederMinMax();
	public abstract function getMapsCoords();

	// Pokestops
	public abstract function getTotalPokestops();
	public abstract function getAllPokestops();

	// Gyms
	public abstract function getTeamGuardians($team_id);
	public abstract function getOwnedAndPoints($team_id);
	public abstract function getAllGyms();
	public abstract function getGymData($gym_id);
	public abstract function getGymDefenders($gym_id);

	// Raids
	public abstract function getAllRaids($page);

	// Trainers
	public abstract function getTrainers($trainer_name, $team, $page, $ranking);
	public abstract function getTrainerLevelCount($team_id);

}