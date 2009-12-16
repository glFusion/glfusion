/*
    reflection.js for mootools v1.43
    (c) 2006-2009 Christophe Beyls <http://www.digitalia.be>
    MIT-style license.

    The following options are available:
    height: The height of the reflection. It can be proportional or fixed. If the value is included between 0 and 1, it is considered as proportional to the image’s height (where 1 means 100%). If the value exceeds 1, it is considered as a fixed height, in pixels. Note that the reflection will never be taller than the image, even if this value exceeds the image’s height. Default is 1/3 (proportional).
    opacity: The starting opacity of the reflection (range: 0 to 1). The reflection is shown as an opacity gradient going from this value to 0. Default is 1/2.
*/
Element.implement({reflect:function(b){var a=this;if(a.get("tag")=="img"){b=$extend({height:1/3,opacity:0.5},b);a.unreflect();function c(){var f=a.width,d=a.height,k,h,l,g,j;h=Math.floor((b.height>1)?Math.min(d,b.height):d*b.height);if(Browser.Engine.trident){k=new Element("img",{src:a.src,styles:{width:f,height:d,marginBottom:h-d,filter:"flipv progid:DXImageTransform.Microsoft.Alpha(opacity="+(b.opacity*100)+", style=1, finishOpacity=0, startx=0, starty=0, finishx=0, finishy="+(h/d*100)+")"}})}else{k=new Element("canvas");if(!k.getContext){return}try{g=k.setProperties({width:f,height:h}).getContext("2d");g.save();g.translate(0,d-1);g.scale(1,-1);g.drawImage(a,0,0,f,d);g.restore();g.globalCompositeOperation="destination-out";j=g.createLinearGradient(0,0,0,h);j.addColorStop(0,"rgba(255, 255, 255, "+(1-b.opacity)+")");j.addColorStop(1,"rgba(255, 255, 255, 1.0)");g.fillStyle=j;g.rect(0,0,f,h);g.fill()}catch(i){return}}k.setStyles({display:"block",border:0});l=new Element(($(a.parentNode).get("tag")=="a")?"span":"div").injectAfter(a).adopt(a,k);l.className=a.className;a.store("reflected",l.style.cssText=a.style.cssText);l.setStyles({width:f,height:d+h,overflow:"hidden"});a.style.cssText="display: block; border: 0px";a.className="reflected"}if(a.complete){c()}else{a.onload=c}}return a},unreflect:function(){var b=this,a=this.retrieve("reflected"),c;b.onload=$empty;if(a!==null){c=b.parentNode;b.className=c.className;b.style.cssText=a;b.store("reflected",null);c.parentNode.replaceChild(b,c)}return b}});

// AUTOLOAD CODE BLOCK (MAY BE CHANGED OR REMOVED)
window.addEvent("domready", function() {
    $$("img").filter(function(img) { return img.hasClass("reflect"); }).reflect({/* Put custom options here */});
});