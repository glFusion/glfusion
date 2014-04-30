var GL_moospring = {
	start: function(){
		GL_moospring.parsegl_moosprings();
	},
	parsegl_moosprings: function(){
		var squeeze_to = 100;
		var normal_width = 125;
		var max_width = 200;

		//get original widths
		var start_widths = new Array();
		var gl_moosprings = $$('#gl_moospring .gl_moospring');
		var fx = new Fx.Elements(gl_moosprings, {wait: false, duration: 200, transition:Fx.Transitions.quadOut});
		gl_moosprings.each(function(gl_moospring, i){
			start_widths[i] = gl_moospring.getStyle('width').toInt();
			//mouse is in, squeeze and expand
			gl_moospring.addEvent('mouseenter', function(e){
				var obj = {};
				obj[i] = {
					'width': [gl_moospring.getStyle('width').toInt(), max_width]
				};
				var counter = 0;
				gl_moosprings.each(function(other, j){
					if (other != gl_moospring){
						var w = other.getStyle('width').toInt();
						if (w != squeeze_to) obj[j] = {'width': [w,squeeze_to] };
					}
				});
				fx.start(obj);
			}
			);
		});
		//mouse is out, squeeze back
		$('gl_moospring').addEvent('mouseleave', function(e){
			var obj = {};
			gl_moosprings.each(function(other, j){
				obj[j] = {'width': [other.getStyle('width').toInt(), normal_width]};
			});
			fx.start(obj);
		});
	}
};
//lock and load!
window.addEvent('load',GL_moospring.start);