var fx = new Fx.Tween('msgbox', {
    duration : 1000,
    transition: Fx.Transitions.Quad.easeOut,
    onComplete : function() {
        $('msgbox').setStyle('display', 'none');
    }
});
fx.start.delay(5000, fx, ['opacity',0]);
