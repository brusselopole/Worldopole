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
			printRaid(raid, pokeimg_suffix, data.locale);
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

function printRaid(raid, pokeimg_suffix, locale) {
	var raidInfos = $('<tr>',{id: 'raidInfos_'+raid.gym_id}).css('border-bottom','2px solid '+(raid.level>2?'#fad94c':'#e872b7'));
	raidInfos.append($('<td>',{id: 'raidLevel_'+raid.gym_id, text: '★'.repeat(raid.level)}));
	raidInfos.append($('<td>',{id: 'raidStart_'+raid.gym_id, text: raid.starttime}));
	raidInfos.append($('<td>',{id: 'raidEnd_'+raid.gym_id, text: raid.endtime}));
	raidInfos.append($('<td>',{id: 'raidRemaining_'+raid.gym_id, class: 'pokemon-remaining'}));
	raidInfos.append($('<td>',{id: 'raidGym_'+raid.gym_id}).append($('<a>',{href: '/map/?lat=' + raid.latitude + '&lng=' + raid.longitude, text: raid.name})));

	var countdown;
	var details;
	var raidPokemon = $('<div>',{class: 'pokemon-single'});
	if (raid.pokemon_id > 0) {
		raidPokemon.append(
			$('<a>', {href : 'pokemon/'+raid.pokemon_id}).append($('<img />',
				{src: 'core/pokemons/'+raid.pokemon_id+pokeimg_suffix})
			)
		);
		details = raid.cp + ' CP<br>' + raid.quick_move + ' / ' + raid.charge_move;
		countdown = new Date(raid.end);
	} else {
		raidPokemon.append(
			$('<img />',
				{src: 'core/img/egg_' + (raid.level > 4 ? 'legendary' : raid.level > 2 ? 'rare' : 'normal') + '.png'})
		);
		countdown = new Date(raid.battle);
	}
	raidInfos.append($('<td>',{id: 'raidBoss_'+raid.gym_id}).append(raidPokemon));
	raidInfos.append($('<td>',{id: 'raidBossdetails_'+raid.gym_id, class: 'pokemon-details'}).append(details));

	$('#raidsContainer').append(raidInfos);

	$('#raidRemaining_'+raid.gym_id).countdown(countdown, { elapse: true, precision: 60000 }).on('update.countdown', function(event) {
		$(this).html(event.strftime('%H:%M'));
	}).countdown('start');
}