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
	
	pokemon_level = '';
	
	if (pokemon.cp_multiplier <= '0.094000' && pokemon.additional_cp_multiplier <= '0') {pokemon_level = '1';}
	else if (pokemon.cp_multiplier <= '0.094000' && pokemon.additional_cp_multiplier > '0') {pokemon_level = '1.5';}
	else if (pokemon.cp_multiplier <= '0.166398' && pokemon.additional_cp_multiplier <= '0') {pokemon_level = '2';}
	else if (pokemon.cp_multiplier <= '0.166398' && pokemon.additional_cp_multiplier > '0') {pokemon_level = '2.5';}
	else if (pokemon.cp_multiplier <= '0.215732' && pokemon.additional_cp_multiplier <= '0') {pokemon_level = '3';}
	else if (pokemon.cp_multiplier <= '0.215732' && pokemon.additional_cp_multiplier > '0') {pokemon_level = '3.5';}
	else if (pokemon.cp_multiplier <= '0.255720' && pokemon.additional_cp_multiplier <= '0') {pokemon_level = '4';}
	else if (pokemon.cp_multiplier <= '0.255720' && pokemon.additional_cp_multiplier > '0') {pokemon_level = '4.5';}
	else if (pokemon.cp_multiplier <= '0.290250' && pokemon.additional_cp_multiplier <= '0') {pokemon_level = '5';}
	else if (pokemon.cp_multiplier <= '0.290250' && pokemon.additional_cp_multiplier > '0') {pokemon_level = '5.5';}
	else if (pokemon.cp_multiplier <= '0.321088' && pokemon.additional_cp_multiplier <= '0') {pokemon_level = '6';}
	else if (pokemon.cp_multiplier <= '0.321088' && pokemon.additional_cp_multiplier > '0') {pokemon_level = '6.5';}
	else if (pokemon.cp_multiplier <= '0.349213' && pokemon.additional_cp_multiplier <= '0') {pokemon_level = '7';}
	else if (pokemon.cp_multiplier <= '0.349213' && pokemon.additional_cp_multiplier > '0') {pokemon_level = '7.5';}
	else if (pokemon.cp_multiplier <= '0.375236' && pokemon.additional_cp_multiplier <= '0') {pokemon_level = '8';}
	else if (pokemon.cp_multiplier <= '0.375236' && pokemon.additional_cp_multiplier > '0') {pokemon_level = '8.5';}
	else if (pokemon.cp_multiplier <= '0.399567' && pokemon.additional_cp_multiplier <= '0') {pokemon_level = '9';}
	else if (pokemon.cp_multiplier <= '0.399567' && pokemon.additional_cp_multiplier > '0') {pokemon_level = '9.5';}
	else if (pokemon.cp_multiplier <= '0.422500' && pokemon.additional_cp_multiplier <= '0') {pokemon_level = '10';}
	else if (pokemon.cp_multiplier <= '0.422500' && pokemon.additional_cp_multiplier > '0') {pokemon_level = '10.5';}
	else if (pokemon.cp_multiplier <= '0.443108' && pokemon.additional_cp_multiplier <= '0') {pokemon_level = '11';}
	else if (pokemon.cp_multiplier <= '0.443108' && pokemon.additional_cp_multiplier > '0') {pokemon_level = '11.5';}
	else if (pokemon.cp_multiplier <= '0.462798' && pokemon.additional_cp_multiplier <= '0') {pokemon_level = '12';}
	else if (pokemon.cp_multiplier <= '0.462798' && pokemon.additional_cp_multiplier > '0') {pokemon_level = '12.5';}
	else if (pokemon.cp_multiplier <= '0.481685' && pokemon.additional_cp_multiplier <= '0') {pokemon_level = '13';}
	else if (pokemon.cp_multiplier <= '0.481685' && pokemon.additional_cp_multiplier > '0') {pokemon_level = '13.5';}
	else if (pokemon.cp_multiplier <= '0.499858' && pokemon.additional_cp_multiplier <= '0') {pokemon_level = '14';}
	else if (pokemon.cp_multiplier <= '0.499858' && pokemon.additional_cp_multiplier > '0') {pokemon_level = '14.5';}
	else if (pokemon.cp_multiplier <= '0.517394' && pokemon.additional_cp_multiplier <= '0') {pokemon_level = '15';}
	else if (pokemon.cp_multiplier <= '0.517394' && pokemon.additional_cp_multiplier > '0') {pokemon_level = '15.5';}
	else if (pokemon.cp_multiplier <= '0.534354' && pokemon.additional_cp_multiplier <= '0') {pokemon_level = '16';}
	else if (pokemon.cp_multiplier <= '0.534354' && pokemon.additional_cp_multiplier > '0') {pokemon_level = '16.5';}
	else if (pokemon.cp_multiplier <= '0.550793' && pokemon.additional_cp_multiplier <= '0') {pokemon_level = '17';}
	else if (pokemon.cp_multiplier <= '0.550793' && pokemon.additional_cp_multiplier > '0') {pokemon_level = '17.5';}
	else if (pokemon.cp_multiplier <= '0.566755' && pokemon.additional_cp_multiplier <= '0') {pokemon_level = '18';}
	else if (pokemon.cp_multiplier <= '0.566755' && pokemon.additional_cp_multiplier > '0') {pokemon_level = '18.5';}
	else if (pokemon.cp_multiplier <= '0.582279' && pokemon.additional_cp_multiplier <= '0') {pokemon_level = '19';}
	else if (pokemon.cp_multiplier <= '0.582279' && pokemon.additional_cp_multiplier > '0') {pokemon_level = '19.5';}
	else if (pokemon.cp_multiplier <= '0.597400' && pokemon.additional_cp_multiplier <= '0') {pokemon_level = '20';}
	else if (pokemon.cp_multiplier <= '0.597400' && pokemon.additional_cp_multiplier > '0') {pokemon_level = '20.5';}
	else if (pokemon.cp_multiplier <= '0.612157' && pokemon.additional_cp_multiplier <= '0') {pokemon_level = '21';}
	else if (pokemon.cp_multiplier <= '0.612157' && pokemon.additional_cp_multiplier > '0') {pokemon_level = '21.5';}
	else if (pokemon.cp_multiplier <= '0.626567' && pokemon.additional_cp_multiplier <= '0') {pokemon_level = '22';}
	else if (pokemon.cp_multiplier <= '0.626567' && pokemon.additional_cp_multiplier > '0') {pokemon_level = '22.5';}
	else if (pokemon.cp_multiplier <= '0.640653' && pokemon.additional_cp_multiplier <= '0') {pokemon_level = '23';}
	else if (pokemon.cp_multiplier <= '0.640653' && pokemon.additional_cp_multiplier > '0') {pokemon_level = '23.5';}
	else if (pokemon.cp_multiplier <= '0.654436' && pokemon.additional_cp_multiplier <= '0') {pokemon_level = '24';}
	else if (pokemon.cp_multiplier <= '0.654436' && pokemon.additional_cp_multiplier > '0') {pokemon_level = '24.5';}
	else if (pokemon.cp_multiplier <= '0.667934' && pokemon.additional_cp_multiplier <= '0') {pokemon_level = '25';}
	else if (pokemon.cp_multiplier <= '0.667934' && pokemon.additional_cp_multiplier > '0') {pokemon_level = '25.5';}
	else if (pokemon.cp_multiplier <= '0.681165' && pokemon.additional_cp_multiplier <= '0') {pokemon_level = '26';}
	else if (pokemon.cp_multiplier <= '0.681165' && pokemon.additional_cp_multiplier > '0') {pokemon_level = '26.5';}
	else if (pokemon.cp_multiplier <= '0.694144' && pokemon.additional_cp_multiplier <= '0') {pokemon_level = '27';}
	else if (pokemon.cp_multiplier <= '0.694144' && pokemon.additional_cp_multiplier > '0') {pokemon_level = '27.5';}
	else if (pokemon.cp_multiplier <= '0.706884' && pokemon.additional_cp_multiplier <= '0') {pokemon_level = '28';}
	else if (pokemon.cp_multiplier <= '0.706884' && pokemon.additional_cp_multiplier > '0') {pokemon_level = '28.5';}
	else if (pokemon.cp_multiplier <= '0.719399' && pokemon.additional_cp_multiplier <= '0') {pokemon_level = '29';}
	else if (pokemon.cp_multiplier <= '0.719399' && pokemon.additional_cp_multiplier > '0') {pokemon_level = '29.5';}
	else if (pokemon.cp_multiplier <= '0.731700' && pokemon.additional_cp_multiplier <= '0') {pokemon_level = '30';}
	else if (pokemon.cp_multiplier <= '0.731700' && pokemon.additional_cp_multiplier > '0') {pokemon_level = '30.5';}
	else if (pokemon.cp_multiplier <= '0.737769' && pokemon.additional_cp_multiplier <= '0') {pokemon_level = '31';}
	else if (pokemon.cp_multiplier <= '0.737769' && pokemon.additional_cp_multiplier > '0') {pokemon_level = '31.5';}
	else if (pokemon.cp_multiplier <= '0.743789' && pokemon.additional_cp_multiplier <= '0') {pokemon_level = '32';}
	else if (pokemon.cp_multiplier <= '0.743789' && pokemon.additional_cp_multiplier > '0') {pokemon_level = '32.5';}
	else if (pokemon.cp_multiplier <= '0.749761' && pokemon.additional_cp_multiplier <= '0') {pokemon_level = '33';}
	else if (pokemon.cp_multiplier <= '0.749761' && pokemon.additional_cp_multiplier > '0') {pokemon_level = '33.5';}
	else if (pokemon.cp_multiplier <= '0.755686' && pokemon.additional_cp_multiplier <= '0') {pokemon_level = '34';}
	else if (pokemon.cp_multiplier <= '0.755686' && pokemon.additional_cp_multiplier > '0') {pokemon_level = '34.5';}
	else if (pokemon.cp_multiplier <= '0.761564' && pokemon.additional_cp_multiplier <= '0') {pokemon_level = '35';}
	else if (pokemon.cp_multiplier <= '0.761564' && pokemon.additional_cp_multiplier > '0') {pokemon_level = '35.5';}
	else if (pokemon.cp_multiplier <= '0.767397' && pokemon.additional_cp_multiplier <= '0') {pokemon_level = '36';}
	else if (pokemon.cp_multiplier <= '0.767397' && pokemon.additional_cp_multiplier > '0') {pokemon_level = '36.5';}
	else if (pokemon.cp_multiplier <= '0.773187' && pokemon.additional_cp_multiplier <= '0') {pokemon_level = '37';}
	else if (pokemon.cp_multiplier <= '0.773187' && pokemon.additional_cp_multiplier > '0') {pokemon_level = '37.5';}
	else if (pokemon.cp_multiplier <= '0.778933' && pokemon.additional_cp_multiplier <= '0') {pokemon_level = '38';}
	else if (pokemon.cp_multiplier <= '0.778933' && pokemon.additional_cp_multiplier > '0') {pokemon_level = '38.5';}
	else if (pokemon.cp_multiplier <= '0.784637' && pokemon.additional_cp_multiplier <= '0') {pokemon_level = '39';}
	else if (pokemon.cp_multiplier <= '0.784637' && pokemon.additional_cp_multiplier > '0') {pokemon_level = '39.5';}
	else if (pokemon.cp_multiplier <= '0.790300' && pokemon.additional_cp_multiplier <= '0') {pokemon_level = '40';}
	else if (pokemon.cp_multiplier <= '0.790300' && pokemon.additional_cp_multiplier > '0') {pokemon_level = '40.5';}

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
