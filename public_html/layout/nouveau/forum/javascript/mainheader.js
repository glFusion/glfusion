function chgtxt(obj,linkid,cellcolor,linkcolor)	{ 
    if(document.getElementById) { // IE5+ and NS6+ only 
	    document.getElementById(linkid).style.color=linkcolor 
	} 
}