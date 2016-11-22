$(function() {
	var page = 0;
	
	loadTrainers(page);
	page++;
	var win = $(window);
	win.scroll(function() {
		// End of the document reached?
		if ($(document).height() - win.height() == win.scrollTop()) {
			loadTrainers(page,$('input#name').val());
			page++;
		}
	});
	$( "#searchTrainer" ).submit(function( event ) {
		page = 0;
		$('#trainersContainer tr:not(.trainersTemplate)').remove();
		loadTrainers(page,$('input#name').val());
		page++;
		event.preventDefault();
	});
	function loadTrainers(page,name=''){
		$('.trainerLoader').show();
		var trainerIndex = 0+(page*30);
		$.ajax({
			'async': false,
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
				'name' : name
			},
			'success': function (data) {	
				
				$.each(data, function(trainerName, trainer){
					trainerIndex++;					
					var trainerInfos = $('tr#trainerInfos').clone(false);
					trainerInfos.attr('id',trainerInfos.attr('id')+ trainer.name);
					trainerInfos.find('#trainerIndex').text(trainerIndex);
					trainerInfos.find('#trainerName').text(trainerName).click(function(e){$('input#name').val(trainerName);$( "#searchTrainer" ).submit();e.preventDefault()});
					trainerInfos.find('#trainerRank').text(trainer.rank);
					trainerInfos.find('#trainerLevel').text(trainer.level);
					trainerInfos.find('#trainerGyms').text(trainer.gyms);
					trainerInfos.css('border-bottom','2px solid '+(trainer.team=="3"?"#ffbe08":trainer.team=="2"?"#ff7676":"#00aaff"));
					trainerInfos.find('[id]').each(function(key, val){
						$(val).attr('id',$(val).attr('id')+ trainer.name);
					});
					trainerInfos.removeClass('trainersTemplate');
					$('#trainersContainer').append(trainerInfos);
					
					var trainerPokemons = $('tr#trainerPokemons').clone(false);
					trainerPokemons.attr('id',trainerPokemons.attr('id')+ trainer.name);
					for(pokeIndex = 0; pokeIndex<trainer.pokemons.length;pokeIndex++){
						var pokemon = trainer.pokemons[pokeIndex];
						var trainerPokemon = $('#trainerPokemon').clone(false);
						trainerPokemon.attr('id',trainerPokemon.attr('id') + trainer.pokemons[pokeIndex].pokemon_uid);
						trainerPokemon.find('#trainerPokemonLink').attr('href','pokemon/'+pokemon.pokemon_id+'.png');
						trainerPokemon.find('#trainerPokemonImage').attr('src','core/pokemons/'+pokemon.pokemon_id+'.png');
						if(pokemon.gym_id == null){
							trainerPokemon.find('#trainerPokemonImage').addClass('unseen');
						}
						trainerPokemon.find('#trainerPokemonCp').text(pokemon.cp);
						trainerPokemon.find('#trainerPokemonStamina').attr('title','Stamina IV:'+pokemon.iv_stamina).attr('aria-valuenow',pokemon.iv_stamina).css('width',((100/45)*pokemon.iv_stamina ) + '%' );
						trainerPokemon.find('#trainerPokemonAttack').attr('title','Attack IV:'+pokemon.iv_attack).attr('aria-valuenow',pokemon.iv_attack).css('width',((100/45)*pokemon.iv_attack ) + '%' );
						trainerPokemon.find('#trainerPokemonDefense').attr('title','Defense IV:'+pokemon.iv_defense).attr('aria-valuenow',pokemon.iv_defense).css('width',((100/45)*pokemon.iv_defense ) + '%' );
						trainerPokemon.find('#trainerLevel').text(trainer.level);
						trainerPokemon.find('#trainerGyms').text(trainer.gyms);
						trainerPokemon.find('[id]').each(function(key, val){
							$(val).attr('id',$(val).attr('id')+ pokemon.pokemon_uid);
						});
						trainerPokemon.removeClass('trainersTemplate');
						trainerPokemons.find('.container').append(trainerPokemon);
						
						
					};
					trainerPokemons.removeClass('trainersTemplate');
					$('#trainersContainer').append(trainerPokemons);
				});
				
				$('.trainerLoader').hide();
			}
		});
	};
});
