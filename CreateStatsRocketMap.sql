# POKEMON
CREATE TABLE pokemon_stats (
  pid smallint(6) NOT NULL,
  count int(11) NOT NULL,
  last_seen int(11) NOT NULL,
  latitude double(18,14) NOT NULL,
  longitude double(18,14) NOT NULL,
  PRIMARY KEY (pid)
);

INSERT INTO pokemon_stats 
	SELECT pokemon_id, COUNT(*), UNIX_TIMESTAMP(CONVERT_TZ(MAX(disappear_time), '+00:00', @@time_zone)), 0.0, 0.0
	FROM pokemon
	GROUP BY pokemon_id;

CREATE TRIGGER pokemon_inserted 
AFTER INSERT ON pokemon
FOR EACH ROW
    INSERT INTO pokemon_stats
	VALUES
		(NEW.pokemon_id, 1, UNIX_TIMESTAMP(CONVERT_TZ(NEW.disappear_time, '+00:00', @@time_zone)), NEW.latitude, NEW.longitude)
	ON DUPLICATE KEY UPDATE
		count = count + 1,
		last_seen = UNIX_TIMESTAMP(CONVERT_TZ(NEW.disappear_time, '+00:00', @@time_zone)),
        latitude = NEW.latitude,
        longitude = NEW.longitude;
	
    
    
# RAIDS
CREATE TABLE raid_stats (
  pid smallint(6) NOT NULL,
  count int(11) NOT NULL,
  last_seen int(11) NOT NULL,
  latitude double(18,14) NOT NULL,
  longitude double(18,14) NOT NULL,
  PRIMARY KEY (pid)
);

INSERT INTO raid_stats 
	SELECT pokemon_id, COUNT(*), UNIX_TIMESTAMP(CONVERT_TZ(MAX(end), '+00:00', @@time_zone)), 0.0 ,0.0
	FROM raid
    WHERE pokemon_id IS NOT NULL
	GROUP BY pokemon_id;
    
DELIMITER $$
CREATE TRIGGER raid_updated 
BEFORE UPDATE ON raid
FOR EACH ROW BEGIN
	SELECT latitude, longitude FROM gym WHERE gym_id = NEW.gym_id INTO @lat, @lon;
	IF (OLD.pokemon_id IS NULL AND NEW.pokemon_id IS NOT NULL) THEN
             INSERT INTO raid_stats
		VALUES
			(NEW.pokemon_id, 1, UNIX_TIMESTAMP(CONVERT_TZ(NEW.end, '+00:00', @@time_zone)),  @lat, @lon)
		ON DUPLICATE KEY UPDATE
			count = count + 1,
			last_seen = UNIX_TIMESTAMP(CONVERT_TZ(NEW.end, '+00:00', @@time_zone)),
			latitude = @lat,
            longitude = @lon;
	END IF;
END$$
DELIMITER ;
