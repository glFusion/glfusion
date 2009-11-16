// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | moorating.js                                                             |
// |                                                                          |
// | MooTools v1.11 AJAX rating sub-system                                    |
// +--------------------------------------------------------------------------+
// | $Id::                                                                   $|
// +--------------------------------------------------------------------------+
// | Copyright (C) 2002-2008 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// +---------------------------------------------------------------------------+
// | Based on work by:                                                         |
// | Ryan Masuga, masugadesign.com  - ryan@masugadesign.com                    |
// | Masuga Design                                                             |
// |(http://masugadesign.com/the-lab/scripts/unobtrusive-ajax-star-rating-bar/)|
// | Komodo Media (http://komodomedia.com)                                     |
// | Climax Designs (http://slim.climaxdesigns.com/)                           |
// +---------------------------------------------------------------------------+
// |                                                                           |
// | This program is free software; you can redistribute it and/or             |
// | modify it under the terms of the GNU General Public License               |
// | as published by the Free Software Foundation; either version 2            |
// | of the License, or (at your option) any later version.                    |
// |                                                                           |
// | This program is distributed in the hope that it will be useful,           |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of            |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the             |
// | GNU General Public License for more details.                              |
// |                                                                           |
// | You should have received a copy of the GNU General Public License         |
// | along with this program; if not, write to the Free Software Foundation,   |
// | Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.           |
// |                                                                           |
// +---------------------------------------------------------------------------+
//

window.addEvent('domready',function() {
    $$('.rater').addEvent('click',function(e) {
        e = new Event(e).stop();

		var parameterString = this.href.replace(/.*\?(.*)/, "$1"); // onclick="sndReq('j=1&q=2&t=127.0.0.1&c=5');
		var parameterTokens = parameterString.split("&"); // onclick="sndReq('j=1,q=2,t=127.0.0.1,c=5');
		var parameterList = new Array();

		for (j = 0; j < parameterTokens.length; j++) {
			var parameterName = parameterTokens[j].replace(/(.*)=.*/, "$1"); // j
			var parameterValue = parameterTokens[j].replace(/.*=(.*)/, "$1"); // 1
			parameterList[parameterName] = parameterValue;
		}

		var thePlugin   = parameterList['p'];
		var theratingID = parameterList['q'];
		var theVote     = parameterList['j'];
		var theuserIP   = parameterList['t'];
		var theunits    = parameterList['c'];
		var thesize     = parameterList['s'];
    	var url         = glfusionSiteUrl+'/rater_rpc.php?p='+thePlugin+'&j='+theVote+'&q='+theratingID+'&t='+theuserIP+'&c='+theunits+'&s='+thesize;

        new Request({
                method: 'get',
                url: url,
                evalScripts: true,
                onComplete: function(response) { $('unit_long'+theratingID).set('html',response); }
            }).send();
        return false;
    });
});