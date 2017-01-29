$(function () {
	$.getJSON( "core/json/variables.json", function( jsondata ) {
		var pokeimg_suffix=jsondata['system']['pokeimg_suffix'];

		$('.trainerLoader').hide();
		var page = 0;
		var teamSelector=0; //0=all;1=Blue;2=Red;3=Yellow
		var rankingFilter=0; //0=Level & Gyms; 1=Level; 2=Gyms

		loadTrainers(page,null,null,null,pokeimg_suffix);
		page++;
		var win = $(window);
		win.scroll(function () {
			// End of the document reached?
			if ($(document).height() - win.height() == win.scrollTop()) {
				loadTrainers(page,$('input#name').val(),teamSelector,rankingFilter,pokeimg_suffix);
				page++;
			}
		});
		$("#searchTrainer").submit(function ( event ) {
			page = 0;
			$('input#name').val()!=''?$('#trainersGraph').hide():$('#trainersGraph').show();
			$('#trainersContainer tr:not(.trainersTemplate)').remove();
			loadTrainers(page,$('input#name').val(),teamSelector,rankingFilter,pokeimg_suffix);
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

		
	});
});

function loadTrainers(page,name,teamSelector,rankingFilter,pokeimg_suffix) {
	$('.trainerLoader').show();
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
		$.each(data, function (trainerName, trainer) {
			trainerIndex++;
			printTrainer(trainer, trainerIndex,pokeimg_suffix);
		});
		$('.trainerLoader').hide();
	});
};
function printTrainer(trainer, trainerIndex,pokeimg_suffix) {
	var trainersInfos = $('<tr>',{id: 'trainerInfos_'+trainer.name}).css('border-bottom','2px solid '+(trainer.team=="3"?"#ffbe08":trainer.team=="2"?"#ff7676":"#00aaff"));
	trainersInfos.append($('<td>',{id : 'trainerIndex_'+trainer.name, text : trainerIndex}));
	trainersInfos.append($('<td>',{id : 'trainerRank_'+trainer.name, text : trainer.rank}));
	trainersInfos.append($('<td>',{id : 'trainerName_'+trainer.name, text : trainer.name}).click(
		function (e) {
			e.preventDefault();$('input#name').val(trainer.name);
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
		
		trainersPokemonsContainer.append(printPokemon(pokemon,pokeimg_suffix));
	}

	trainersPokemons.append(trainersPokemonsContainer);
	trainersPokemonsRow.append(trainersPokemons);
	$('#trainersContainer').append(trainersPokemonsRow);
}

function printPokemon(pokemon,pokeimg_suffix){
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
	var progressBar = $('<div>',{class : 'progress'}).css({'height': '6px','margin-bottom': '0'});
	progressBar.append(
		$('<div>',
			{
			title: 'IV Stamina :'+pokemon.iv_stamina, 
			class: 'progress-bar progress-bar-success' ,
			role : 'progressbar',
			'aria-valuenow' :pokemon.iv_stamina,
			'aria-valuemin' : 0, 
			'aria-valuemax' : 45
		}).css('width',((100/45)*pokemon.iv_stamina ) + '%'))
	progressBar.append(
		$('<div>',
			{
			title: 'IV Attack :'+pokemon.iv_attack, 
			class: 'progress-bar progress-bar-danger' ,
			role : 'progressbar', 
			'aria-valuenow' : pokemon.iv_attack, 
			'aria-valuemin' : 0, 
			'aria-valuemax' : 45
		}).css('width',((100/45)*pokemon.iv_attack ) + '%'))
	progressBar.append(
		$('<div>',
			{
			title: 'IV Defense :'+pokemon.iv_defense,
			class: 'progress-bar progress-bar-info' ,
			role : 'progressbar', 
			'aria-valuenow': pokemon.iv_defense, 
			'aria-valuemin' : 0, 
			'aria-valuemax' : 45
		}).css('width',((100/45)*pokemon.iv_defense ) + '%'))
	trainerPokemon.append(progressBar);
	if (pokemon.last_scanned === '0') {
		trainerPokemon.append($('<small>',{text: "Today"}));
	}
	else if (pokemon.last_scanned === '1') {
		trainerPokemon.append($('<small>',{text: pokemon.last_scanned + " Day"}));
	}
	else {
		trainerPokemon.append($('<small>',{text: pokemon.last_scanned + " Days"}));
	}
	return trainerPokemon;
}