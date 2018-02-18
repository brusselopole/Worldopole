<?php

include_once __DIR__ . '/../../../config.php';
include_once __DIR__ . '/QueryManagerMysql.php';
include_once __DIR__ . '/QueryManagerPostgresql.php';

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


			switch (strtolower(self::$config->system->db_type)) {
				case "monocle-alt":
				case "monocle-alt-mysql":
					self::$current = new QueryManagerMysqlMonocleAlternate();
					break;
				case "monocle-alt-pgsql":
					self::$current = new QueryManagerPostgresqlMonocleAlternate();
					break;
				default:
					self::$current = new QueryManagerMysqlRocketmap();
					break;
			}
		}
		return self::$current;
	}
	private function __construct(){}

	// Misc
	abstract public function getEcapedString($string);

	// Tester
	abstract public function testTotalPokemon();
	abstract public function testTotalGyms();
	abstract public function testTotalPokestops();

	// Homepage
	abstract public function getTotalPokemon();
	abstract public function getTotalLures();
	abstract public function getTotalGyms();
	abstract public function getTotalRaids();
	abstract public function getTotalGymsForTeam($team_id);
	abstract public function getRecentAll();
	abstract public function getRecentMythic($mythic_pokemon);

	// Single Pokemon
	abstract public function getGymsProtectedByPokemon($pokemon_id);
	abstract public function getPokemonLastSeen($pokemon_id);
	abstract public function getTop50Pokemon($pokemon_id, $top_order_by, $top_direction);
	abstract public function getTop50Trainers($pokemon_id, $best_order_by, $best_direction);
	abstract public function getPokemonHeatmap($pokemon_id, $start, $end);
	abstract public function getPokemonGraph($pokemon_id);
	abstract public function getPokemonLive($pokemon_id, $ivMin, $ivMax, $inmap_pokemons);
	abstract public function getPokemonSliderMinMax();
	abstract public function getMapsCoords();

	// Pokestops
	abstract public function getTotalPokestops();
	abstract public function getAllPokestops();

	// Gyms
	abstract public function getTeamGuardians($team_id);
	abstract public function getOwnedAndPoints($team_id);
	abstract public function getAllGyms();
	abstract public function getGymData($gym_id);
	abstract public function getGymDefenders($gym_id);

	// Gym History
	abstract public function getGymHistories($gym_name, $team, $page, $ranking);
	abstract public function getGymHistoriesPokemon($gym_id);
	abstract public function getHistoryForGym($page, $gym_id);

	// Raids
	abstract public function getAllRaids($page);

	// Trainers
	abstract public function getTrainers($trainer_name, $team, $page, $ranking);
	abstract public function getTrainerLevelCount($team_id);

	// Cron
	abstract public function getPokemonCountsActive();
	abstract public function getPokemonCountsLastDay();
	abstract public function getPokemonSinceLastUpdate($pokemon_id, $last_update);
	abstract public function getRaidsSinceLastUpdate($pokemon_id, $last_update);
	abstract public function getCaptchaCount();
	abstract public function getNestData();
}