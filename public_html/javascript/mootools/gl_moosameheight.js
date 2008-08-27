/*
Get the maximum height from divs with class ="sameheight"
currently implemented in gl_content, gl_navigation, and gl_extra divs,
and set all to the tallest one's height.
Originally written by Djamil Legato <djamil@djamil.it>
Adapted for glFusion by Eric Warren <eakwarren@gmail.com>
*/
var maxHeight = function(classname) {
    var divs = document.getElements('div.' + classname);
    var max = 0;
    divs.each(function(div) {
        max = Math.max(max, div.getSize().size.y);
    });
	divs.setStyle('height', max);
    return max;
}

window.addEvent('load', function() {
	if (window.ie6) { /* don't let it work in IE6 because it creates a black space because of the padding hack  in ie6.css*/
	}
	else {
		maxHeight('sameheight');
		maxHeight.delay(500, maxHeight, 'sameheight'); 
	}
});