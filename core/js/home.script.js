function updateCounter(new_value, classname){
		
	var CurrentValue = $(classname).text(); 
	
	$({someValue: CurrentValue}).animate({someValue: new_value}, {
		duration: 3000,
		easing:'swing', 
		step: function() {
			$(classname).text(Math.round(this.someValue));
		}
	});	
	
}






(function cron() {
   
    $.ajax({
        url: 'core/process/aru.php?type=home_update',
        success: function(data) {           
        	        	
        	var pokemon = data[0]; 
        	var lure	= data[1];  
        	
        	var red 	= data[2];
        	var blue 	= data[3];
        	var yellow 	= data[4];
        	var neutral = data[5]; 
        	
        	updateCounter(pokemon,'.total-pkm-js');
        	updateCounter(lure,'.total-lure-js');
        	updateCounter(red,'.total-valor-js');
        	updateCounter(blue,'.total-mystic-js');
        	updateCounter(yellow,'.total-instinct-js');
        	updateCounter(neutral,'.total-new-js');
        	
        },
        complete: function() {
            // Schedule the next request when the current one's complete
            setTimeout(cron, 5000);
        }
    });
})();




(function spawn() {
   
   var last_id = $('.last-mon-js div:first-child').attr('pokeid'); 
   //console.log(last_id); 
   
    $.ajax({
        url: 'core/process/aru.php?type=spawnlist_update&last_id='+last_id,
        success: function(data) {           
        	
        	if(data != ''){

	        	//console.log(data);
	        	$('.last-mon-js').prepend(data);
	        	
	        	$('.last-mon-js div:last-child').fadeOut();
	        	$('.last-mon-js div:first-child').fadeIn();
				
				$('.last-mon-js div:last-child').remove();
        	}
        	
        	
        },
        complete: function() {
            // Schedule the next request when the current one's complete
            setTimeout(spawn, 5000);
        }
    });
})();