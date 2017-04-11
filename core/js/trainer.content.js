/** global: trainerName */

$(function () {

	$.getJSON( "core/json/variables.json", function( jsondata ) {
		var pokeimg_suffix=jsondata['system']['pokeimg_suffix'];
		var iv_numbers=jsondata['system']['iv_numbers'];

		$('.trainerLoader').hide();
		var page = 0;
		var teamSelector=0; //0=all;1=Blue;2=Red;3=Yellow
		var rankingFilter=0; //0=Level & Gyms; 1=Level; 2=Gyms

		$('input#name').filter(':visible').val(trainerName);
		loadTrainers(page,$('input#name').filter(':visible').val(),null,null,pokeimg_suffix,true,iv_numbers);

		page++;
		$('#loadMoreButton').click(function () {

			loadTrainers(page,$('input#name').filter(':visible').val(),teamSelector,rankingFilter,pokeimg_suffix,true,iv_numbers);
			page++;
		});
		$("#searchTrainer").submit(function ( event ) {
			page = 0;
			$('input#name').filter(':visible').val()!=''?$('#trainersGraph').hide():$('#trainersGraph').show();
			$('#trainersContainer tr:not(.trainersTemplate)').remove();
			loadTrainers(page,$('input#name').filter(':visible').val(),teamSelector,rankingFilter,pokeimg_suffix,true,iv_numbers);
			page++;
			event.preventDefault();
		});
		$(".teamSelectorItems").click(function ( event ) {
			switch ($(this).attr("id")) {
				case "AllTeamsFilter":
					teamSelector=0;
					break;
				case "BlueTeamFilter":
					teamSelector=1;
					break;
				case "RedTeamFilter":
					teamSelector=2;
					break;
				case "YellowFilter":
					teamSelector=3;
					break;
				default:
					teamSelector=0;
			}
			$("#teamSelectorText").html($(this).html());
			event.preventDefault();
			$("#searchTrainer").submit();

		});
		$(".rankingOrderItems").click(function ( event ) {
			switch ($(this).attr("id")) {
				case "levelsFirst":
					rankingFilter=0;
					break;
				case "gymsFirst":
					rankingFilter=1;
					break;
				case "maxCpFirst":
					rankingFilter=2;
					break;
				default:
					rankingFilter=0;
			}
			$("#rankingOrderText").html($(this).html());
			event.preventDefault();
			$("#searchTrainer").submit();

		});
		window.onpopstate = function() {
			if (window.history.state && "Trainer" === window.history.state.page) {
				$('#trainersContainer').empty();
				$('input#name').filter(':visible').val(window.history.state.name);
				loadTrainers(0,$('input#name').filter(':visible').val(),teamSelector,rankingFilter,pokeimg_suffix,false,iv_numbers);
			}
			else{
				window.history.back();
			}
		};

	});
});

function loadTrainers(page,name,teamSelector,rankingFilter,pokeimg_suffix,stayOnPage,iv_numbers) {
	$('.trainerLoader').show();

	if (stayOnPage) {
		// build a state for this name
		var state = {name: name, page: 'Trainer'};
		window.history.pushState(state,'Trainer',"trainer?name="+name);
	}
	var trainerIndex = 0+(page*10);
	$.ajax({
		'async': true,
		'type': "GET",
		'global': false,
		'dataType': 'json',
		'url': "core/process/aru.php",
		'data': {
			'request': "",
			'target': 'arrange_url',
			'method': 'method_target',
			'type' : 'trainer',
			'page' : page,
			'name' : name,
			'team' : teamSelector,
			'ranking' :rankingFilter
		}
	}).done(function (data) {
		var internalIndex = 0;
		$.each(data.trainers, function (trainerName, trainer) {
			trainerIndex++;
			internalIndex++
			printTrainer(trainer, trainerIndex,pokeimg_suffix,iv_numbers, data.locale);
		});
		if(internalIndex < 10){
			$('#loadMoreButton').hide();
		}
		else{
			$('#loadMoreButton').removeClass('hidden');
			$('#loadMoreButton').show();
		}
		$('.trainerLoader').hide();
	});
};



function printTrainer(trainer, trainerIndex,pokeimg_suffix,iv_numbers, locale) {
	var trainersInfos = $('<tr>',{id: 'trainerInfos_'+trainer.name}).css('border-bottom','2px solid '+(trainer.team=="3"?"#ffbe08":trainer.team=="2"?"#ff7676":"#00aaff"));
	trainersInfos.append($('<td>',{id : 'trainerIndex_'+trainer.name, text : trainerIndex}));
	trainersInfos.append($('<td>',{id : 'trainerRank_'+trainer.name, text : trainer.rank}));
	trainersInfos.append($('<td>',{id : 'trainerName_'+trainer.name}).append($('<a>',{href: 'trainer?name='+trainer.name, text: trainer.name})).click(
		function (e) {
			e.preventDefault();
			$('input#name').filter(':visible').val(trainer.name);
			$("#searchTrainer").submit();
			$('#trainerName_'+trainer.name).off('click');
		}
	));
	trainersInfos.append($('<td>',{id : 'trainerLevel_'+trainer.name, text : trainer.level}));
	trainersInfos.append($('<td>',{id : 'trainerGyms_'+trainer.name, text : trainer.gyms}));
	trainersInfos.append($('<td>',{id : 'trainerLastSeen_'+trainer.name, text : trainer.last_seen}));
	$('#trainersContainer').append(trainersInfos);
	var trainersPokemonsRow = $('<tr>',{id: 'trainerPokemons_'+trainer.name});
	var trainersPokemons = $('<td>',{colspan : 6});
	var trainersPokemonsContainer = $('<div>',{class : ""});
	for (var pokeIndex = 0; pokeIndex<trainer.pokemons.length; pokeIndex++) {
		var pokemon = trainer.pokemons[pokeIndex];
		trainersPokemonsContainer.append(printPokemon(pokemon,pokeimg_suffix,iv_numbers, locale));
	}

	trainersPokemons.append(trainersPokemonsContainer);
	trainersPokemonsRow.append(trainersPokemons);
	$('#trainersContainer').append(trainersPokemonsRow);
}

function printPokemon(pokemon,pokeimg_suffix,iv_numbers,locale){
	var trainerPokemon = $('<div>',{id : 'trainerPokemon_'+pokemon.pokemon_uid, class: "col-md-1 col-xs-4 pokemon-single", style: "text-align: center" });
	var gymClass = "";
	if ((pokemon.gym_id===null)) {
		gymClass = "unseen";
	}
	trainerPokemon.append(
		$('<a>',
			{href : 'pokemon/'+pokemon.pokemon_id}
		).append($('<img />',
			{	src : 'core/pokemons/'+pokemon.pokemon_id+pokeimg_suffix,
				'class' : 'img-responsive '+gymClass
			})
		)
	);
	trainerPokemon.append($('<p>',{class : 'pkmn-name'}).append(pokemon.cp));
	var progressBar
	if (iv_numbers) {
		progressBar = $('<div>',{class : 'progress'}).css({'height': '15px','margin-bottom': '0'});
		progressBar.append(
					$('<div>',
					{
						title: locale.ivAttack+' :'+pokemon.iv_attack,
						class: 'progress-bar progress-bar-danger' ,
						role : 'progressbar',
						text : pokemon.iv_attack,
						'aria-valuenow' : pokemon.iv_attack,
						'aria-valuemin' : 0,
						'aria-valuemax' : 45
					}).css({'width':(100/3) + '%','line-height': '16px'}))
		progressBar.append(
					$('<div>',
					{
						title: locale.ivDefense+' :'+pokemon.iv_defense,
						class: 'progress-bar progress-bar-info' ,
						role : 'progressbar',
						text : pokemon.iv_defense,
						'aria-valuenow': pokemon.iv_defense,
						'aria-valuemin' : 0,
						'aria-valuemax' : 45
					}).css({'width':(100/3) + '%','line-height': '16px'}))
		progressBar.append(
					$('<div>',
					{
						title: locale.ivStamina+' :'+pokemon.iv_stamina,
						class: 'progress-bar progress-bar-success' ,
						role : 'progressbar',
						text : pokemon.iv_stamina,
						'aria-valuenow' :pokemon.iv_stamina,
						'aria-valuemin' : 0,
						'aria-valuemax' : 45
					}).css({'width':(100/3) + '%','line-height': '16px'}))
	} else {
		progressBar = $('<div>',{class : 'progress'}).css({'height': '6px','margin-bottom': '0'});
		progressBar.append(
					$('<div>',
					{
						title: locale.ivAttack+' :'+pokemon.iv_attack,
						class: 'progress-bar progress-bar-danger' ,
						role : 'progressbar',
						'aria-valuenow' : pokemon.iv_attack,
						'aria-valuemin' : 0,
						'aria-valuemax' : 45
					}).css('width',((100/45)*pokemon.iv_attack ) + '%'))
		progressBar.append(
					$('<div>',
					{
						title: locale.ivDefense+' :'+pokemon.iv_defense,
						class: 'progress-bar progress-bar-info' ,
						role : 'progressbar',
						'aria-valuenow': pokemon.iv_defense,
						'aria-valuemin' : 0,
						'aria-valuemax' : 45
					}).css('width',((100/45)*pokemon.iv_defense ) + '%'))
		progressBar.append(
					$('<div>',
					{
						title: locale.ivStamina+' :'+pokemon.iv_stamina,
						class: 'progress-bar progress-bar-success' ,
						role : 'progressbar',
						'aria-valuenow' :pokemon.iv_stamina,
						'aria-valuemin' : 0,
						'aria-valuemax' : 45
					}).css('width',((100/45)*pokemon.iv_stamina ) + '%'))
	}
	trainerPokemon.append(progressBar);
	
	var cpm_arr={1:.09399999678134918,1.5:.1351374313235283,2:.16639786958694458,2.5:.1926509141921997,3:.21573247015476227,3.5:.23657265305519104,4:.2557200491428375,4.5:.27353037893772125,5:.29024988412857056,5.5:.3060573786497116,6:.3210875988006592,6.5:.33544503152370453,7:.3492126762866974,7.5:.362457737326622,8:.37523558735847473,8.5:.38759241108516856,9:.39956727623939514,9.5:.4111935495172506,10:.4225000143051148,10.5:.4329264134104144,11:.443107545375824,11.5:.4530599538719858,12:.46279838681221,12.5:.4723360780626535,13:.4816849529743195,13.5:.4908558102324605,14:.4998584389686584,14.5:.5087017565965652,15:.517393946647644,15.5:.5259425118565559,16:.5343543291091919,16.5:.5426357612013817,17:.5507926940917969,17.5:.5588305993005633,18:.5667545199394226,18.5:.574569147080183,19:.5822789072990417,19.5:.5898879119195044,20:.5974000096321106,20.5:.6048236563801765,21:.6121572852134705,21.5:.6194041110575199,22:.6265671253204346,22.5:.633649181574583,23:.6406529545783997,23.5:.6475809663534164,24:.654435634613037,24.5:.6612192690372467,25:.667934000492096,25.5:.6745819002389908,26:.6811649203300476,26.5:.6876849085092545,27:.6941436529159546,27.5:.7005428969860077,28:.7068842053413391,28.5:.7131690979003906,29:.719399094581604,29.5:.7255756109952927,30:.7317000031471252,30.5:.7347410172224045,31:.7377694845199585,31.5:.740785576403141,32:.7437894344329834,32.5:.7467812150716782,33:.74976104,33.5:.752729104,34:.75568551,34.5:.75863037,35:.76156384,35.5:.76448607,36:.76739717,36.5:.77029727,37:.7731865,37.5:.77606494,38:.77893275,38.5:.78179006,39:.78463697,39.5:.78747358,40:.79030001,40.5:.7931164};

	// assume some super high level
	pokemon_level = 50;
	$.each(cpm_arr, function(lvl, cpm) {
		if (pokemon.cp_multiplier <= cpm) {
			pokemon_level = pokemon_level > lvl ? lvl : pokemon_level;
		}
	});

	pokemon_level = Number(pokemon_level) + Number(pokemon.num_upgrades)/2;
	pokemon_level = pokemon_level.toString();
	
	trainerPokemon.append($('<small>').append("L" + " " + pokemon_level));
	trainerPokemon.append($('<br>').append());
	
	if (pokemon.last_scanned === '0') {
		trainerPokemon.append($('<small>',{text: locale.today}));
	}
	else if (pokemon.last_scanned === '1') {
		trainerPokemon.append($('<small>',{text: pokemon.last_scanned + " " + locale.day}));
	}
	else {
		trainerPokemon.append($('<small>',{text: pokemon.last_scanned + " " + locale.days}));
	}
	return trainerPokemon;
}
