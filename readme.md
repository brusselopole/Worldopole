# Worldopole

Based on [Brusselopole](http://www.brusselopole.be), Worldopole is an open source version of the website, allowing you to display in a nice way the data you have gathered of the Pokemon in your city such as Team Gym Battles, Pokemon nests and Pokéstops. This app is a base and will help you build your own version of Brusselopole with more data-visualisation! 

## Requirements

- PHP5+ 
- Apache2
- Mod_Rewrite (htaccess) 
- Existing [PokemonGo-Map](https://github.com/PokemonGoMap/PokemonGo-Map) **Mysql** database

## To deploy for you

Edit **config.php** file. 
Add your MySQL connexion information and the time delay between your timezone and GMT. (eg: Brussels is + 2 GMT)


Edit **/core/json/variables.json** 
Add your informations (follow the examples included) : database, city, localisation, name you want, ... 
You need to add available languages there also but new language required to be available in translations.json and a pokelist_CODELANG.json need to exist

Rename **htaccess** to **.htaccess**


Look at **translations.json**
All website string are there. 


## Contribute 

If you want to dev. pages or new modules you can add pages to the pages/ directory. 
Page name structure : [name].page.php 
You can join the page by using ?page=[name] parameters in URL. 


## Structure 

- core/
- core/css 		: CSS minified files (except style.css to allow edit)
- core/fonts 	: Website fonts (such as fontawsome)
- core/img		: Website images (no Pokemon)
- core/inc		: Meta datas for pages. 
- core/js 		: Javascript files (Warning, some JS files are PHP files with Javascript headers)
- core/json		: Mostly configuration or datas files
- core/meta-icons : Icons for mobile webapp 
- core/pokemons	: Pokémon images 
- core/process 	: Load datas for pages. 

- functions.php 	: No description needed. 
- index.php		: Pages are included by index. 

- install/ 		: Install folder to make some tests at install and verify the system is ready to host the app. 
- offline.html	: Called file in case of Database connexion issue. 

- pages/ 			: All website pages.



## Install 

- Clone the repo on your webdirectory
- Go to the URL of your directory 

# In case of problem 
Check /core/json/ & /install/ folders rights 
Apache user must be able to read and write files on it. 

**YOU MUST KEEP "en" : true IN variables.json FILE.** 
**You can add or remove others languague following other existing language example.** 

## License

Worldopole is released under the MIT License.
 
