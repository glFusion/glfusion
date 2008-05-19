function showVideo(URL, height, width) {
  	day = new Date();
 	id = day.getTime();
  	eval("page" + id + " = window.open(URL, '" + id + "', 'toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=0,resizable=1,width=" + width + ",height=" + height + "');");
}
function divResize(id, nw, nh) {
	var obj = document.getElementById(id);
	obj.style.width = nw + "px";
	obj.style.height = nh + "px";
}
function divColor(id,color) {
    var obj = document.getElementById(id);
    obj.style.backgroundColor = color;
}