    window.addEvent('domready', function() {

        // loop through the mootip class selectors, and parse the "title"
        // element for the tooltip 'title::text', and store appropriately

        $$(['.gl_mootip','.gl_mootipfixed','.gl_mootipfade']).each(function(element,index) {
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

        var Tip1 = new Tips('.gl_mootip',{
            className: 'gl_mootip'
        });

        var Tip2 = new Tips('.gl_mootipfade',{
            className: 'gl_mootipfade'
        });

        Tip2.addEvents({
            'hide': function(tip) {
                tip.fade('out');
            },
            'show': function(tip) {
                tip.fade('in');
            }
        });

        var Tip3 = new Tips('.gl_mootipfixed',{
            className: 'gl_mootipfixed',
            fixed: true
        });

    }); // domready