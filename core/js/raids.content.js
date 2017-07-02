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
	var raidIndex = 0+(page*10);
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
			raidIndex++;
			printRaid(raid, raidIndex, pokeimg_suffix, data.locale);
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

function printRaid(raid,raidIndex,pokeimg_suffix,locale) {
	var raidInfos = $('<tr>',{id: 'raidInfos_'+raid.gym_id}).css('border-bottom','2px solid '+(raid.level>2?'#fad94c':'#e872b7'));
	raidInfos.append($('<td>',{id: 'raidIndex_'+raid.gym_id, text: raidIndex}));
	raidInfos.append($('<td>',{id: 'raidLevel_'+raid.gym_id, text: raid.level}));
	raidInfos.append($('<td>',{id: 'raidStart_'+raid.gym_id, text: raid.start}));
	raidInfos.append($('<td>',{id: 'raidEnd_'+raid.gym_id, text: raid.end}));
	raidInfos.append($('<td>',{id: 'raidGym_'+raid.gym_id}).append($('<a>',{href: '/map/?lat=' + raid.latitude + '&lng=' + raid.longitude, text: raids.name})));
	raidInfos.append($('<td>',{id: 'raidBoss_'+raid.gym_id, text: raid.pokemon_id}));
	$('#raidsContainer').append(raidInfos);
}