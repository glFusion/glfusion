window.addEvent('domready', function() {

    // loop through the mootip class selectors, and parse the "title"
    // element for the tooltip 'title::text', and store appropriately

    $$(['.gf_mootip','.gf_mootipfixed','.gf_mootipfade']).each(function(element,index) {
	var content = element.get('title').split('::');
	 switch(content[0]) {
	    case 'AJAX':
		var req = new Request({
		    url: content[1],
		    method: 'get',
		    onFailure: function(xhr) {
			element.store('tip:title', 'ERROR');
			element.store('tip:text', 'Ajax request failed');
		    },
		    onSuccess: function(responseText, responseXML) {
			content = responseText.split('::');
			// store title and text
			element.store('tip:title', content[0]);
			element.store('tip:text', content[1]);
			// remove the element title
			element.title = '';
		    }
		});
		req.send();
		break;
	    default:
		// store title and text
		element.store('tip:title', content[0]);
		element.store('tip:text', content[1]);
		// remove the element title
		element.title = '';
		break;
	}
    });

    var gfTips1 = new Tips('.gf_mootip',{
	className: 'gftool'
    });

    var gfTips2 = new Tips('.gf_mootipfade',{
	className: 'gftoolfade'
    });

    gfTips2.addEvents({
	'hide': function(tip) {
	    tip.fade('out');
	},
	'show': function(tip) {
	    tip.fade('in');
	}
    });

    var gfTips3 = new Tips('.gf_mootipfixed',{
	className: 'gftoolfixed',
	fixed: true
    });

});
