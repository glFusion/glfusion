/**
 * IEWarn - An IE6 Warning to invite people to upgrade to IE8.
 * It also sets a cookie on close so it doesn't keep popping up with
 * each page refresh.
 *
 * @version		1.2
 * @license		MIT-style license
 * @author		Djamil Legato <djamil@rockettheme.com>
 * @copyright		Author
 * Adapted for glFusion by Eric 'Geiss' Warren <eakwarren@gmail.com>
 */

var IEWarn = new Class({
	'initialize': function() {
		var warning = "<h4>You are currently browsing this site with Internet Explorer 6 (IE6).<br />Due to limitiations in this browser, this site might not render as intended.</h4><p>The last version of IE6 was called Service Pack 1 for Internet Explorer 6 and was released in December of 2004.<br /><b>By continuing to run IE6 you are open to all security vulnerabilities discovered since that date.</b></p><p>In October of 2006, Microsoft released version 7 of Internet Explorer that provides better safety and greater compliance with web standards. </p><p>On Feb 12th 2008, Microsoft began to mandate updates to IE6 in order to move people towards the much improved and secure version 7. Microsoft currently considers IE8 to be a \"high-priority\" update.</p><h4>We encourage you to <a href=\"http://www.microsoft.com/windows/downloads/ie/getitnow.mspx\">update Internet Explorer</a> for a better browsing experience.</h4>";

		this.box = new Element('div', {'id': 'iewarn'}).inject(document.body, 'top');
		var div = new Element('div').inject(this.box).setHTML(warning);

		var click = this.toggle.bind(this);
		var button = new Element('a', {'id': 'iewarn_close', 'title': 'Close and don\'t remind me for 7 days'}).addEvents({
			'click': function() {
				click();
			}
		}).inject(div, 'top');

		this.height = $('iewarn').getSize().size.y;

		this.fx = new Fx.Styles(this.box, {duration: 1000}).set({'margin-top': $('iewarn').getStyle('margin-top').toInt(), 'opacity': 0});
		this.open = false;

		var cookie = Cookie.get('IEWarn'), height = this.height;
		//cookie = 'open'; // added for debug to not use the cookie value
		if (!cookie || cookie == "open") this.show();
		else this.fx.set({'margin-top': -height, 'opacity': 0});

		return ;
	},

	'show': function() {
		this.fx.start({
			'margin-top': 0,
			'opacity': 1
		});
		this.open = true;
		Cookie.set('IEWarn', 'open', {duration: 7});
	},
	'close': function() {
		var margin = this.height;
		this.fx.start({
			'margin-top': -margin,
			'opacity': 0
		});
		this.open = false;
		Cookie.set('IEWarn', 'close', {duration: 7});
	},
	'status': function() {
		return this.open;
	},
	'toggle': function() {
		if (this.open) this.close();
		else this.show();
	}
});

window.addEvent('load', function() {
	if (window.ie6) { (function() {var iewarn = new IEWarn();}).delay(2000); }
});