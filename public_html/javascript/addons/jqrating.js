/*
 * glFusion CMS
 *
 * @license Copyright (c) 2002-2016, Mark R. Evans. All rights reserved.
 * Licensed under the terms of the GNU General Public License
 * 		http://www.opensource.org/licenses/gpl-license.php
 *
 */

$(window).load(function() {
    $(document).on('click', '.rater', function( event ) {
        event.preventDefault();

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
        var url = glfusionSiteUrl+'/rater_rpc.php?p='+thePlugin+'&j='+theVote+'&q='+theratingID+'&t='+theuserIP+'&c='+theunits+'&s='+thesize;
        $.get(url,function(data) {
                    $('#unit_long'+theratingID).html(data);
                  });
    });
});
