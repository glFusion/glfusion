/*! glFusion CMS v1.7.0 | https://www.glfusion.org | (c) 2017 glFusion | GNU GPL v2 License */
$(window).load(function(){pf()});
$('[data-uk-switcher]').on('show.uk.switcher', function(event, area) {pf();});
$('[data-uk-tab]').on('click', function(event, active_item, previous_item) {pf();});
$('.uk-alert').on('closed.uk.alert', function(){pf();});
$( window ).resize(function(){pf();});
$(window).load(function(){
	if ($(window).scrollTop()>100){
		$("#scroll-to-top").fadeIn(1500);
	}
	$(function () {
		$(window).scroll(function(){
			if ($(window).scrollTop()>100){
				$("#scroll-to-top").fadeIn(1500);
			} else {
				$("#scroll-to-top").fadeOut(750);
			}
		});
	});
});
$('video,audio').mediaelementplayer({
	// if the <video width> is not specified, this is the default
	defaultVideoWidth: 480,
	// if the <video height> is not specified, this is the default
	defaultVideoHeight: 270,
	// if set, overrides <video width>
	videoWidth: -1,
	// if set, overrides <video height>
	videoHeight: -1,
	// width of audio player
	audioWidth: 400,
	// height of audio player
	audioHeight: 30,
	// initial volume when the player starts
	startVolume: 0.8,
	// useful for <audio> player loops
	loop: false,
	// enables Flash and Silverlight to resize to content size
	enableAutosize: true,
	// the order of controls you want on the control bar (and other plugins below)
	features: ['playpause','progress','current','duration','tracks','volume','fullscreen'],
	// Hide controls when playing and mouse is not over the video
	alwaysShowControls: false,
	// force iPad's native controls
	iPadUseNativeControls: false,
	// force iPhone's native controls
	iPhoneUseNativeControls: false,
	// force Android's native controls
	AndroidUseNativeControls: false,
	// forces the hour marker (##:00:00)
	alwaysShowHours: false,
	// show framecount in timecode (##:00:00:00)
	showTimecodeFrameCount: false,
	// used when showTimecodeFrameCount is set to true
	framesPerSecond: 25,
	// turns keyboard support on and off for this instance
	enableKeyboard: true,
	// when this player starts, it will pause other players
	pauseOtherPlayers: true,
	// path to players
	pluginPath: glfusionSiteUrl + '/javascript/addons/mediaplayer/',
	// array of keyboard commands
	keyActions: [],
	success:  function (mediaElement, domObject) {
		mediaElement.addEventListener("ended", function(e){
			// Revert to the poster image when ended
			var $thisMediaElement = (mediaElement.id) ? jQuery("#"+mediaElement.id) : jQuery(mediaElement);
			$thisMediaElement.parents(".mejs-inner").find(".mejs-poster").show();
		});
	}
});
UIkit.modal.help = function(content, options) {
	options = UIkit.$.extend(true, {bgclose:true, keyboard:true, modal:true, labels:UIkit.modal.labels}, options);
	var modal = UIkit.modal.dialog(([
	'<div class="uk-margin uk-modal-content">'+String(content)+'</div>',
	'<div class="uk-modal-footer uk-text-right"><button class="uk-button uk-button-primary uk-modal-close">'+options.labels.Ok+'</button></div>'
	]).join(""), options);
	modal.on('show.uk.modal', function(){
		setTimeout(function(){
			modal.element.find('button:first').focus();
		}, 50);
	});
	return modal.show();
};
$('.block-help-icon').on('click', function(e) {
	e.preventDefault();
	var help_url = $(this).attr('href');
	var modaltext = '<iframe style="width:100%;" src="'+help_url+'"></iframe>';
	UIkit.modal.help(modaltext);
	return false;
});