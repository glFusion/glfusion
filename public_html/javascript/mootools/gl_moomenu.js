var moomenu = new Class({
    options: {
        hoverClass: 'sfHover',
        delay: 500,
        animate: {
            props: ['opacity', 'height'],
            opts: Class.empty
        }
    },
    initialize: function (el, options) {
        this.setOptions(options);
        this.element = $(el);
        if (this.element) {
            this.element.getElements('li').each(function (el) {
                el.addEvents({
                    'mouseover': this.over.bind(this, el),
                    'mouseout': this.out.bind(this, el)
                })
            },
            this)
        }
    },
    over: function (el) {
        $clear(el.sfTimer);
        if (!el.hasClass(this.options.hoverClass)) {
            el.addClass(this.options.hoverClass);
            var ul = el.getElement('ul');
            if (ul) {
                if (this.options.bgiframe) ul.bgiframe({
                    opacity: false
                });
                ul.animate(this.options.animate)
            }
            el.getSiblings().each(function (ele) {
                ele.removeClass(this.options.hoverClass)
            },
            this)
        }
    },
    out: function (el) {
        el.sfTimer = (function () {
            el.removeClass(this.options.hoverClass);
            var iframe = el.getElement('iframe');
            if (iframe) iframe.remove()
        }).delay(this.options.delay, this)
    }
});

moomenu.implement(new Options);

Element.implement({
    animate: function (obj) {
        if (!this.Fx) {
            this.Fx = new Fx.Morph(this, obj.opts);
            this.now = this.getStyles.apply(this, obj.props);
            this.FxEmpty = {};
            for (var i in this.now) this.FxEmpty[i] = 0
        }
        if (obj.props.contains('height') || obj.props.contains('width')) {
            this.setStyle('overflow', 'hidden');
            this.getParents('ul').each(function (el) {
                el.setStyle('overflow', 'visible')
            })
        }
        this.Fx.set(this.FxEmpty).start(this.now)
    },
    getParents: function (expr) {
        var matched = [];
        var cur = this.getParent();
        while (cur && cur !== document) {
            if(cur.get('tag').test(expr))matched.push(cur);
            cur = cur.getParent()
        }
        return matched
    },
    getSiblings: function () {
        var children = this.getParent().getChildren();
        children.splice(children.indexOf(this), 1);
        return children
    }
});