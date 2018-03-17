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
	SELECT pokemon_id, COUNT(*), UNIX_TIMESTAMP(MAX(disappear_time)), 0.0, 0.0
	FROM pokemon
	GROUP BY pokemon_id;

CREATE TRIGGER pokemon_inserted 
AFTER INSERT ON pokemon
FOR EACH ROW
    INSERT INTO pokemon_stats
	VALUES
		(NEW.pokemon_id, 1, UNIX_TIMESTAMP(NEW.disappear_time), NEW.latitude, NEW.longitude)
	ON DUPLICATE KEY UPDATE
		count = count + 1,
		last_seen = UNIX_TIMESTAMP(NEW.disappear_time),
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
	SELECT pokemon_id, COUNT(*), UNIX_TIMESTAMP(MAX(end)), 0.0 ,0.0
	FROM raid
    WHERE pokemon_id IS NOT NULL
	GROUP BY pokemon_id;
    
DELIMITER $$
CREATE TRIGGER raid_updated 
BEFORE UPDATE ON raid
FOR EACH ROW BEGIN
	SELECT latitude, longitude FROM forts WHERE id = NEW.fort_id INTO @lat, @lon;
	IF (OLD.pokemon_id IS NULL AND NEW.pokemon_id IS NOT NULL) THEN
             INSERT INTO raid_stats
		VALUES
			(NEW.pokemon_id, 1, UNIX_TIMESTAMP(NEW.end),  @lat, @lon)
		ON DUPLICATE KEY UPDATE
			count = count + 1,
			last_seen = UNIX_TIMESTAMP(NEW.end),
			latitude = @lat,
            longitude = @lon;
	END IF;
END$$
DELIMITER ;