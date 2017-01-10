function updateCounter(new_value, classname)
{
		
	var CurrentValue = $(classname).text();
	
	$({someValue: CurrentValue}).animate({someValue: new_value}, {
		duration: 3000,
		easing:'swing',
		step: function () {
			$(classname).text(Math.round(this.someValue));
		}
	});
	
}


(function cron()
{
	
	$.ajax({
		url: 'core/process/aru.php?type=home_update',
		success: function (data) {
					
			var pokemon	= data[0];
			var lure	= data[1];

			var gym		= data[2];
			var red		= data[3];
			var blue	= data[4];
			var yellow	= data[5];
			var neutral	= data[6];
			
			updateCounter(pokemon,'.total-pkm-js');
			updateCounter(lure,'.total-lure-js');
			updateCounter(gym,'.total-gym-js');
			updateCounter(red,'.total-valor-js');
			updateCounter(blue,'.total-mystic-js');
			updateCounter(yellow,'.total-instinct-js');
			updateCounter(neutral,'.total-rocket-js');
			
		},
		complete: function () {
			// Schedule the next request when the current one's complete
			setTimeout(cron, 5000);
		}
	});
})();



(function spawn()
{
	
	var last_id = $('.last-mon-js div:first-child').attr('data-pokeid');
	//console.log(last_id);
	 
	$.ajax({
		url: 'core/process/aru.php?type=spawnlist_update&last_id='+last_id,
		success: function (data) {
			
			if (!$.isEmptyObject(data)) {
				$('.last-mon-js').prepend(data[0]);
				
				// stop timer of last child
				stopTimer();

				// replace child
				$('.last-mon-js > div:last-child').fadeOut();
				$('.last-mon-js > div:first-child').fadeIn();
				$('.last-mon-js > div:last-child').remove();

				// start timer for new child
				startTimer(data[1],data[2]);
			}
			
			
		},
		complete: function () {
			// Schedule the next request when the current one's complete
			setTimeout(spawn, 5000);
		}
	});
})();

// Array with timer IDs
timers = [];

function startTimer(duration, element)
{
	var countdown = duration, hours, minutes, seconds;
	timers.push(setInterval(function() {
		hours = Math.abs(parseInt(countdown / 3600, 10));
		minutes = Math.abs(parseInt((countdown / 60) % 60, 10));
		seconds = Math.abs(parseInt(countdown % 60, 10));

		hours = hours < 10 ? "0" + hours : hours;
		minutes = minutes < 10 ? "0" + minutes : minutes;
		seconds = seconds < 10 ? "0" + seconds: seconds;

		var output = hours + ":" + minutes + ":" + seconds
		if (--countdown >= 0) {
			$(element).text(output);
		} else {
			$(element).text("- " + output);
			$(element).css({ 'color': 'rgb(200, 50, 50)'});
		}
	}, 1000));
}

function stopTimer()
{
	var lastTimer = timers.shift();
	clearInterval(lastTimer);
}
