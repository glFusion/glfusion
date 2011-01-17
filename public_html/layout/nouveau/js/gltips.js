	window.addEvent('load', function() {
		var Tips1 = new Tips($$('.gl_mootip')); //enables use of tooltips
		var Tips2 = new Tips($$('.gl_mootipfade'), { //enables use of fade in/out tooltips
			initialize:function(){
				this.fx = new Fx.Style(this.toolTip, 'opacity', {duration: 500, wait: false}).set(0);
			},
			onShow: function(toolTip) {
				this.fx.start(1);
			},
			onHide: function(toolTip) {
				this.fx.start(0);
			}
		});
		var Tips3 = new Tips($$('.gl_mootipfixed'), { //enables use of fixed position tooltips (good for hover help text)
			showDelay: 150,
			hideDelay: 400,
			fixed: true
		});
	});