/*! glFusion CMS v1.6.1 | https://www.glfusion.org | (c) 2016 glFusion | GNU GPL v2 License */
$('[data-uk-switcher]').on('show.uk.switcher', function(event, area){positionFooter();});
$(window).load(function(){var a=$(window).height();var c=$("#tm-footer").height();var b=$("#tm-footer").position().top+c;if(b<a){$("#tm-footer").css("margin-top",50+(a-b)+"px")}});
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
    // array of keyboard commands
    keyActions: []

});
