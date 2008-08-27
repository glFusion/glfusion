// a header banner rotator, according to time of day of client. Sets <body id="timeofday" and optionally calls extra stylesheet.
window.addEvent('load', function() {
chronometer=""  // "" for auto-rotation | "day" for static day | "night" for static night, etc.
if (document.body.id == '') {
	if (chronometer != false) {
		timeofday = chronometer;
		document.body.id=chronometer;
	} else {
		datetoday = new Date();
		timenow=datetoday.getTime();
		datetoday.setTime(timenow);
		thehour = datetoday.getHours();
		if (thehour >= 20){
			timeofday = "night";
			//uncomment below to call additional stylesheet
			//new Asset.css('http://enter/absolute/path/to/additional.css', {title: 'enter_the_themename'});
		}
		else if (thehour >= 18){
			timeofday = "dusk";
			//uncomment below to call additional stylesheet
			//new Asset.css('http://enter/absolute/path/to/additional.css', {title: 'enter_the_themename'});
		}
		else if (thehour >= 15){
			timeofday = "afternoon";
			//uncomment below to call additional stylesheet
			//new Asset.css('http://enter/absolute/path/to/additional.css', {title: 'enter_the_themename'});
		}
		else if (thehour >= 9){
			timeofday = "day";
			//uncomment below to call additional stylesheet
			//new Asset.css('http://enter/absolute/path/to/additional.css', {title: 'enter_the_themename'});
		}
		else if (thehour >= 6){
			timeofday = "dawn";
			//uncomment below to call additional stylesheet
			//new Asset.css('http://enter/absolute/path/to/additional.css', {title: 'enter_the_themename'});
		}
		else {
			timeofday = "night";
			//uncomment below to call additional stylesheet
			//new Asset.css('http://enter/absolute/path/to/additional.css', {title: 'enter_the_themename'});
		}
		document.body.id=timeofday;		
	}
	Cookie.set('gl_moochronometer', timeofday, {duration: 1/24,path: "/"});
}
});