// code for IE6 for Site Tailor vertical menus only
STverticalmenu = function() {
	if(!document.body.currentStyle) return;
	var subs = document.getElementsByName('parent-l3'); //set to parent-r3 if using in #gl_extra column
	for(var i=0; i<subs.length; i++) {
		var li = subs[i].parentNode;
		if(li && li.lastChild.style) {
			li.onmouseover = function() {
				this.lastChild.style.visibility = 'visible';
			}
			li.onmouseout = function() {
				this.lastChild.style.visibility = 'hidden';
			}
		}
	}
}
window.onload=STverticalmenu;