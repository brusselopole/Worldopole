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
	SELECT pokemon_id, COUNT(*), MAX(expire_timestamp), 0.0, 0.0
	FROM sightings
	GROUP BY pokemon_id;

CREATE TRIGGER sightings_inserted 
AFTER INSERT ON sightings
FOR EACH ROW
    INSERT INTO pokemon_stats
	VALUES
		(NEW.pokemon_id, 1, NEW.expire_timestamp, NEW.lat, NEW.lon)
	ON DUPLICATE KEY UPDATE
		count = count + 1,
		last_seen = NEW.expire_timestamp,
		latitude = NEW.lat,
		longitude = NEW.lon;
	
    
    
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
	SELECT pokemon_id, COUNT(*), MAX(time_end), 0.0, 0.0
	FROM raids
    WHERE pokemon_id IS NOT NULL
	GROUP BY pokemon_id;
    
DELIMITER $$
CREATE TRIGGER raids_updated 
BEFORE UPDATE ON raids
FOR EACH ROW BEGIN
	SELECT lat, lon FROM forts WHERE id = NEW.fort_id INTO @lat, @lon;
	IF (OLD.pokemon_id IS NULL AND NEW.pokemon_id IS NOT NULL) THEN
             INSERT INTO raid_stats
		VALUES
			(NEW.pokemon_id, 1, NEW.time_end, @lat, @lon)
		ON DUPLICATE KEY UPDATE
			count = count + 1,
			last_seen = NEW.time_end,
            latitude = @lat,
            longitude = @lon;
	END IF;
END$$
DELIMITER ;