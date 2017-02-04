$(function () {
	$('.spawn-counter').each(function(){
		var pokemon_id = $(this).attr('id').substring('spawn_pokemon_'.length);
		loadSpawnCounter(pokemon_id);
	})
});

function loadSpawnCounter(pokemon_id){
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
			'type' : 'pokedex',
			'pokemon_id' : pokemon_id
		}
	}).done(function (data) {
		$('#spawn_pokemon_'+pokemon_id).text(data.total);
	});
}
