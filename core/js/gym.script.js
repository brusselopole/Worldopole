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
		url: 'core/process/aru.php?type=update_gym',
		success: function (data) {

			var mysgym = data[0];
			var mysave = data[1];

			var valgym = data[2];
			var valave = data[3];

			var insgym = data[4];
			var insave = data[5];


			updateCounter(valgym,'.gym-valor-js');
			updateCounter(valave,'.average-valor-js');

			updateCounter(insgym,'.gym-instinct-js');
			updateCounter(insave,'.average-instinct-js');

			updateCounter(mysgym,'.gym-mystic-js');
			updateCounter(mysave,'.average-mystic-js');

		},
		complete: function () {
			// Schedule the next request when the current one's complete
			setTimeout(cron, 5000);
		}
	});
})();
