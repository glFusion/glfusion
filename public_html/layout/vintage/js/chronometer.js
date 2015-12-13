$(window).ready(function() {
	chronometer=""  // "" for auto-rotation | "day" for static day | "night" for static night, etc.
	if (document.body.id == '') {
		if (chronometer != false) {
			timeofday = chronometer;
			document.body.id=chronometer;
		} else {
			var d = new Date();
			var thehour = d.getHours();
			if (thehour >= 20){
				timeofday = "night";
			}
			else if (thehour >= 18) {
				timeofday = "dusk";
			}
			else if (thehour >= 15){
				timeofday = "afternoon";
			}
			else if (thehour >= 9){
				timeofday = "day";
			}
			else if (thehour >= 6){
				timeofday = "dawn";
			}
			else {
				timeofday = "night";
			}
			document.body.id=timeofday;
		}
	}
});