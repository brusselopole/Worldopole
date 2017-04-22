#!/bin/bash

# This script will set all the config variables for you

# go to script dir
BASEDIR=$(dirname "$0")
cd "$BASEDIR" || exit 1

# Set some defaults
# config.php
readonly CONFIG_PHP_EX="config.example.php"
readonly CONFIG_PHP="config.php"
SYS_DB_NAME=""
SYS_DB_USER=""
SYS_DB_PSWD=""
SYS_DB_HOST="localhost"
SYS_DB_PORT="3306"
# variables.json
readonly VARIABLES_JSON_EX="core/json/variables.examples.json"
readonly VARIABLES_JSON="core/json/variables.json"
SITE_NAME="Worldopole"
CITY="Weuuurld"
MAP_CENTER_LAT="50.844441"
MAP_CENTER_LONG="4.363557"
GMAPS_KEY=""
TIMEZONE="Europe/Paris"

readinput() {
	local prompt_text=$1
	local default_text=$2
	read -r -e -i "$default_text" -p "$prompt_text" input
	echo "$input"
}


# MAIN
echo "Welcome to Worldopole installation"

if [ "$EUID" -ne 0 ]; then
	echo
	echo "Notice: Running script with other user than root might fail!"
fi
echo
echo "Please enter all the following values and press ENTER to confirm"
echo

# Loop until answer is yes
until [ "$answer" == 'y' ]; do
	# Get input
	echo "- MySQL Settings -"
	SYS_DB_NAME=$(readinput "MySQL Database Name: " "$SYS_DB_NAME")
	SYS_DB_USER=$(readinput "MySQL User: " "$SYS_DB_USER")
	SYS_DB_PSWD=$(readinput "MySQL Password: " "$SYS_DB_PSWD")
	SYS_DB_HOST=$(readinput "MySQL Host: " "$SYS_DB_HOST")
	SYS_DB_PORT=$(readinput "MySQL Port: " "$SYS_DB_PORT")
	echo
	echo "- Site Settings -"
	SITE_NAME=$(readinput "Site Name: " "$SITE_NAME")
	CITY=$(readinput "City Name: " "$CITY")
	MAP_CENTER_LAT=$(readinput "Map Center Latitude: " "$MAP_CENTER_LAT")
	MAP_CENTER_LONG=$(readinput "Map Center Longitude: " "$MAP_CENTER_LONG")
	GMAPS_KEY=$(readinput "GMaps API Key: " "$GMAPS_KEY")
	TIMEZONE=$(readinput "Server Timezone (see http://php.net/manual/en/timezones.php): " "$TIMEZONE")

	# Show input for verification
	echo 
	echo "You entered the following data:"
	echo
	echo "- MySQL Settings -"
	echo "MySQL Database Name: $SYS_DB_NAME"
	echo "MySQL User: $SYS_DB_USER"
	echo "MySQL Password: $SYS_DB_PSWD"
	echo "MySQL Host: $SYS_DB_HOST"
	echo "MySQL Port: $SYS_DB_PORT"
	echo
	echo "- Site Settings -"
	echo "Site Name: $SITE_NAME"
	echo "City Name: $CITY"
	echo "Map Center Latitude: $MAP_CENTER_LAT"
	echo "Map Center Longitude: $MAP_CENTER_LONG"
	echo "GMaps API Key: $GMAPS_KEY"
	echo "Server Timezone: $TIMEZONE"
	echo

	# yes or no
	read -r -n 1 -p "Is everything correct [y/n] " answer
	if [ "$answer" != 'y' ]; then
		echo
		echo "Do your edits:"
		echo
	fi
done
echo
echo

# Replace default values with the ones set above
echo "Writing $CONFIG_PHP ..."
sed 	-e "s/#SYS_DB_NAME#/$SYS_DB_NAME/" \
	-e "s/#SYS_DB_USER#/$SYS_DB_USER/" \
	-e "s/#SYS_DB_PSWD#/$SYS_DB_PSWD/" \
	-e "s/#SYS_DB_HOST#/$SYS_DB_HOST/" \
	-e "s/3306/$SYS_DB_PORT/" \
"$CONFIG_PHP_EX" > "$CONFIG_PHP"

# We have to escape the / from timezone
TIMEZONE=$(echo "$TIMEZONE" | sed 's/\//\\\//g')
echo "Writing $VARIABLES_JSON ..."
sed	-e "s/\"Worldopole\"/\"$SITE_NAME\"/" \
	-e "s/\"Weuuurld\"/\"$CITY\"/" \
	-e "s/\"50.844441\"/\"$MAP_CENTER_LAT\"/" \
	-e "s/\"4.363557\"/\"$MAP_CENTER_LONG\"/" \
	-e "s/#GMAPS_KEY#/$GMAPS_KEY/" \
	-e "s/\"Europe\/Paris\"/\"$TIMEZONE\"/" \
"$VARIABLES_JSON_EX" > "$VARIABLES_JSON"

# Rename htaccess
cp -v htaccess .htaccess

echo
echo "For even more settings have a look at $BASEDIR/$VARIABLES_JSON"
echo "Please make sure that your webserver user has read/write access to /core/json/ & /install/ folders"
echo
echo "Everything is set up. Catch 'Em All!"
echo
echo
echo "Optional: If you want to enable Dashboard add the following to your crontab"
echo '          Just copy it. Then paste it after executing "sudo crontab -e" and save the file'
echo "*/15 * * * * php $BASEDIR/core/cron/crontabs.include.php >/dev/null 2>&1"
echo
echo "Afterwards add the following to your $VARIABLES_JSON file inside the \"menu\" tag:"
echo '{ "type" : "link", "href" : "dashboard", "text" : "Dashboard", "icon" : "fa-area-chart" }'
