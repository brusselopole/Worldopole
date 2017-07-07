$(function () {
	$.getJSON( "core/json/variables.json", function(variables) {
		var pokeimg_suffix = variables['system']['pokeimg_suffix'];
		$('.raidsLoader').hide();
		var page = 0;
		loadRaids(page, pokeimg_suffix);
		page++;
		$('#loadMoreButton').click(function () {
			loadRaids(page, pokeimg_suffix);
			page++;
		});
	});
});

function loadRaids(page, pokeimg_suffix) {
	$('.raidsLoader').show();
	$.ajax({
		'type': 'GET',
		'dataType': 'json',
		'url': "core/process/aru.php",
		'data': {
			'type' : 'raids',
			'page' : page
		}
	}).done(function (data) {
		var internalIndex = 0;
		$.each(data.raids, function (gym_id, raid) {
			internalIndex++;
			printRaid(raid, pokeimg_suffix);
		});
		if(internalIndex < 10){
			$('#loadMoreButton').hide();
		}
		else{
			$('#loadMoreButton').removeClass('hidden');
			$('#loadMoreButton').show();
		}
		$('.raidsLoader').hide();
	});
};

function printRaid(raid, pokeimg_suffix) {
	var now = new Date();
	var raidStart = new Date(raid.start);
	var raidEnd = new Date(raid.end);

	var raidInfos = $('<tr>',{id: 'raidInfos_'+raid.gym_id}).css('border-bottom','2px solid '+(raid.level>2?'#fad94c':'#e872b7'));
	raidInfos.append($('<td>',{id: 'raidLevel_'+raid.gym_id, text: '★'.repeat(raid.level)}));
	raidInfos.append($('<td>',{id: 'raidTime_'+raid.gym_id, text: raid.starttime + ' - ' + raid.endtime}));
	raidInfos.append($('<td>',{id: 'raidRemaining_'+raid.gym_id, class: 'pokemon-remaining'}).append($('<span>',{class: (raidStart < now ? 'current' : 'upcoming')})));
	raidInfos.append($('<td>',{id: 'raidGym_'+raid.gym_id}).append($('<a>',{href: '/map/?lat=' + raid.latitude + '&lng=' + raid.longitude, text: raid.name})));

	var details = '';
	var raidPokemon = $('<div>',{class: 'pokemon-single'});
	if (raid.pokemon_id > 0) {
		raidPokemon.append(
			$('<a>', {href : 'pokemon/'+raid.pokemon_id}).append($('<img />',
				{src: 'core/pokemons/'+raid.pokemon_id+pokeimg_suffix})
			)
		);
		details = raid.cp + ' CP<br>' + raid.quick_move + ' / ' + raid.charge_move;
	} else {
		raidPokemon.append(
			$('<img />',
				{src: 'core/img/egg_' + (raid.level > 4 ? 'legendary' : raid.level > 2 ? 'rare' : 'normal') + '.png'})
		);
		if (raidStart < now) {
			raidPokemon.append($('<span>', {text:'?'}));
		}
	}
	raidInfos.append($('<td>',{id: 'raidBoss_'+raid.gym_id}).append(raidPokemon));
	raidInfos.append($('<td>',{id: 'raidBossdetails_'+raid.gym_id, class: 'pokemon-details'}).append(details));

	$('#raidsContainer').append(raidInfos);

	$('span', '#raidRemaining_'+raid.gym_id).countdown(
		(raidStart > now ? raidStart : raidEnd),
		{ elapse: true, precision: 30000 }
	).on('update.countdown', function(event) {
		if (event.elapsed) {
			$(this).html('00:00').css('text-decoration', 'line-through');
		} else {
			$(this).html(event.strftime('%-H:%M'));
		}
	}).countdown('start');
}