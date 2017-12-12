/** global: gymName */

$(function () {
	$.getJSON("core/json/variables.json", function(variables) {
		var pokeimg_suffix = variables['system']['pokeimg_suffix'];
		var hide_cp_changes = variables['system']['gymhistory_hide_cp_changes'];

		$('.gymLoader').hide();

		var page = 0;
		var teamSelector = ''; //''=all; 0=neutral; 1=Blue; 2=Red; 3=Yellow
		var rankingFilter = 0; //0=Level & Gyms; 1=Level; 2=Gyms

		$('input#name').filter(':visible').val(gymName);

		$('#loadMoreButton').click(function () {
			loadGyms(page, $('input#name').filter(':visible').val(), teamSelector, rankingFilter, pokeimg_suffix, hide_cp_changes, true);
			page++;
		}).trigger('click');

		$('#searchGyms').submit(function ( event ) {
			page = 0;
			$('#gymsContainer').empty();
			$('#loadMoreButton').trigger('click');
			event.preventDefault();
		});

		$('.teamSelectorItems').click(function ( event ) {
			switch ($(this).attr('id')) {
				case 'NeutralTeamsFilter':
					teamSelector=0;
					break;
				case 'BlueTeamFilter':
					teamSelector=1;
					break;
				case 'RedTeamFilter':
					teamSelector=2;
					break;
				case 'YellowFilter':
					teamSelector=3;
					break;
				default:
					teamSelector='';
			}
			$('#teamSelectorText').html($(this).html());
			event.preventDefault();
			$('#searchGyms').submit();

		});
		$('.rankingOrderItems').click(function ( event ) {
			switch ($(this).attr('id')) {
				case 'changedFirst':
					rankingFilter=0;
					break;
				case 'nameFirst':
					rankingFilter=1;
					break;
				case 'totalcpFirst':
					rankingFilter=2;
					break;
				default:
					rankingFilter=0;
			}
			$('#rankingOrderText').html($(this).html());
			event.preventDefault();
			$('#searchGyms').submit();
		});
		window.onpopstate = function() {
			if (window.history.state && 'gymhistory' === window.history.state.page) {
				$('input#name').filter(':visible').val(window.history.state.name);
				page = 0;
				$('#gymsContainer').empty();
				loadGyms(page, $('input#name').filter(':visible').val(), teamSelector, rankingFilter, hide_cp_changes, pokeimg_suffix, false);
				page++;
			} else {
				window.history.back();
			}
		};
	});
});

function loadGyms(page, name, teamSelector, rankingFilter, pokeimg_suffix, hide_cp_changes, stayOnPage) {
	$('.gymLoader').show();
	if (stayOnPage) {
		// build a state for this name
		var state = {name: name, page: 'gymhistory'};
		window.history.pushState(state, 'gymhistory', 'gymhistory?name=' + name);
	}
	$.ajax({
		'async': true,
		'type': 'GET',
		'global': false,
		'dataType': 'json',
		'url': 'core/process/aru.php',
		'data': {
			'type' : 'gyms',
			'page' : page,
			'name' : name,
			'team' : teamSelector,
			'ranking' :rankingFilter
		}
	}).done(function (data) {
		var internalIndex = 0;
		$.each(data.gyms, function (idx, gym) {
			internalIndex++
			printGym(gym, pokeimg_suffix, hide_cp_changes);
		});
		if (internalIndex < 10) {
			$('#loadMoreButton').hide();
		} else {
			$('#loadMoreButton').removeClass('hidden').show();
		}
		$('.gymLoader').hide();
	});
}

function loadGymHistory(page, gym_id, pokeimg_suffix, hide_cp_changes) {
	$('#gymHistory_'+gym_id).addClass('active').show();
	$('#gymHistory_'+gym_id).find('.gymHistoryLoader').show();
	$.ajax({
		'async': true,
		'type': 'GET',
		'global': false,
		'dataType': 'json',
		'url': 'core/process/aru.php',
		'data': {
			'type' : 'gymhistory',
			'page' : page,
			'gym_id' : gym_id
		}
	}).done(function (data) {
		var internalIndex = 0;
		$.each(data.entries, function(idx, entry) {
			internalIndex++
			if (entry.only_cp_changed && hide_cp_changes) return;
			printGymHistory(gym_id, entry, pokeimg_suffix);
		});
		if (internalIndex < 10) {
			$('#gymHistory_'+gym_id).find('.loadMoreButtonHistory').hide();
		} else {
			$('#gymHistory_'+gym_id).find('.loadMoreButtonHistory').removeClass('hidden').data('page', page+1).show();
		}
		$('#gymHistory_'+gym_id).find('.gymHistoryLoader').hide();
	});
}

function printPokemonList(pokemons, pokeimg_suffix) {
	var gymPokemon = $('<ul>',{class: 'list-inline'});
	$.each(pokemons, function(idx, pokemon) {
		var list = $('<li>', {class: pokemon.class});
		list.append($('<a>', { class: 'no-link', href : 'pokemon/'+pokemon.pokemon_id }).append($('<img />', { src: 'core/pokemons/'+pokemon.pokemon_id+pokeimg_suffix }).css('height', '2em')));
		list.append($('<br><span class="small">'+pokemon.cp+' CP</span>'));
		list.append($('<br><span style="font-size:70%"><a href="trainer?name='+pokemon.trainer_name+'" class="no-link">'+pokemon.trainer_name+'</a></span>'));
		gymPokemon.append(list);
	});
	return gymPokemon;
}

function printGymHistory(gym_id, entry, pokeimg_suffix) {
	var gymHistory = $('<tr>').css('border-bottom', '2px solid '+(entry.team_id=='3'?'#ffbe08':entry.team_id=='2'?'#ff7676':entry.team_id=='1'?'#00aaff':'#ddd'));
	gymHistory.append($('<td>',{text: entry.last_modified}));
	gymHistory.append($('<td>',{text: entry.pokemon_count, class: 'level'}).prepend($('<img />', {src:'core/img/map_'+(entry.team_id=='1'?'blue':entry.team_id=='2'?'red':entry.team_id=='3'?'yellow':'white')+'.png'})));
	gymHistory.append($('<td>',{text: parseInt(entry.total_cp).toLocaleString('de-DE'), class: entry.class}).append(
		entry.total_cp_diff !== 0 ? $('<span class="small"> ('+(entry.total_cp_diff > 0 ? '+' : '')+entry.total_cp_diff+')</span>') : null
	));
	var gymPokemon = printPokemonList(entry.pokemon, pokeimg_suffix);
	gymHistory.append($('<td>').append(gymPokemon));
	$('#gymHistory_'+gym_id).find('tbody').append(gymHistory);
}

function hideGymHistoryTables(gymHistoryTables) {
	gymHistoryTables.removeClass('active').hide();
	gymHistoryTables.find('tbody tr').remove();
	gymHistoryTables.find('.loadMoreButtonHistory').hide();
	gymHistoryTables.find('.gymHistoryLoader').hide();
}

function printGym(gym, pokeimg_suffix, hide_cp_changes) {
	var gymsInfos = $('<tr>',{id: 'gymInfos_'+gym.gym_id}).css('cursor', 'pointer').css('border-bottom', '2px solid '+(gym.team_id=='3'?'#ffbe08':gym.team_id=='2'?'#ff7676':gym.team_id=='1'?'#00aaff':'#ddd')).click(function() {
		if (!$('#gymHistory_'+gym.gym_id).hasClass('active')) {
			hideGymHistoryTables($('#gymsContainer').find('.gymhistory'));
			loadGymHistory(0, gym.gym_id, pokeimg_suffix, hide_cp_changes);
		} else {
			hideGymHistoryTables($('#gymHistory_'+gym.gym_id));
		}
	});
	gymsInfos.append($('<td>',{text: gym.last_modified}));
	if (gym.name.length > 50) { gym.name = gym.name.substr(0, 50) + '…'; }
	gymsInfos.append($('<td>',{text: gym.name}));
	gymsInfos.append($('<td>',{text: gym.pokemon_count, class: 'level'}).prepend($('<img />', {src:'core/img/map_'+(gym.team_id=='1'?'blue':gym.team_id=='2'?'red':gym.team_id=='3'?'yellow':'white')+'.png'})));
	gymsInfos.append($('<td>',{text: parseInt(gym.total_cp).toLocaleString('de-DE')}));
	var gymPokemon = printPokemonList(gym.pokemon, pokeimg_suffix);
	gymsInfos.append($('<td>').append(gymPokemon));
	$('#gymsContainer').append(gymsInfos);
	var historyTable = $('<table>',{class: 'table'});
	historyTable.append('<thead><tr><th style="min-width:7em">Time</th><th>Level</th><th>Total CP</th><th>Pokémon</th></tr></thead>');
	historyTable.append('<tbody></tbody>');
	historyTable.append('<tfoot><tr class="loadMore text-center"><td colspan="4"><button class="loadMoreButtonHistory btn btn-default btn-sm hidden">Load more</button></td></tr><tr class="gymHistoryLoader"><td colspan="4"><div class="loader"></div></td></tr></tfoot>');
	historyTable.find('.loadMoreButtonHistory').data('page', 0).click(function() {
		loadGymHistory($(this).data('page'), gym.gym_id, pokeimg_suffix, hide_cp_changes);
	});
	var row = $('<td>',{colspan: 6});
	row.append(historyTable);
	$('#gymsContainer').append($('<tr>', {id: 'gymHistory_'+gym.gym_id, class: 'gymhistory'}).hide().append(row));
}
