var gl_Slide=new Class(

	{options:{
				active:'',
				fx:{
					wait:false,
					duration:350
					},
				scrollFX:{
					wait:true,
					transition:Fx.Transitions.Sine.easeInOut
					},
				dimensions:{
					width:722,
					height:200
					},

				cssvars: {
					customID: 'gl_',
					},
				autoScroll: {
					interval: 0
					}
			},
	initialize:function(contents,options){
		this.setOptions(options);
		this.content=$(contents);
		this.sections=this.content.getElements('.'+this.options.cssvars.customID+'tab-pane');
		if(!this.sections.length)
		return;
		
		this.filmstrip=new Element('div',{id:this.options.cssvars.customID+'slide_hr'}).injectAfter(this.content);
		this.buildToolbar();
		this.buildFrame();
		if(window.ie)this.fixIE();
		this.scroller=$(this.options.cssvars.customID+'scroller');
		this.startposition=$(this.sections[0].id.replace('-tab','-pane')).getPosition().x;
		this.scroller.scrollFX=new Fx.Scroll(this.scroller,this.options.scrollFX);
		
		this.autoSlide='';
		if (this.options.autoScroll.interval > 0) {this.autoScrollStart();}

		if(this.options.active)
			this.scrollSection(this.options.active.test(/-tab|-pane/)?this.options.active:this.options.active+'-tab');
		else
			this.scrollSection(this.sectionptr[0])},
	buildToolbar:function(){
		var lis=[];
		var that=this;
		this.sectionptr=[];
		var h1,title;
		this.sections.each(function(el){
			el.setStyles({width:this.options.dimensions.width-102,height:this.options.dimensions.height
			});
		this.sectionptr.push(el.id.replace('-pane','-tab'));
		h1=el.getElement('.'+this.options.cssvars.customID+'tab-title');
		title=h1.innerHTML;
		h1.empty().remove();
		lis.push(new Element('li',{
				id:el.id.replace('-pane','-tab'),
				events:{
					'click':function(){	this.addClass('active');
										that.scrollSection(this);
										that.autoScrollStop();
 									},
					'mouseover':function(){ this.addClass('hover');
											this.addClass('active');
											that.autoScrollStop();
										},
					'mouseout':function(){	this.removeClass('hover');
											this.removeClass('active');
											that.autoScrollStart();
										}
						}
					}).setHTML(title))},this);
					this.filmstrip.adopt(new Element('ul',{
								id:this.options.cssvars.customID+'slide-tabs',styles:{
										width:this.options.dimensions.width
									}
								}).adopt(lis),new Element('hr')
								)
			},
	buildFrame:function(){
				var that=this,
				events={
					'click':function(){	that.scrollArrow(this);
							 			that.autoScrollStop();},
					'mouseover':function(){ this.addClass('hover');
											that.autoScrollStop();},
					'mouseout':function(){  this.removeClass('hover');
									   		that.autoScrollStart();}
				};
				this.filmstrip.adopt(new Element('div',{
					id:this.options.cssvars.customID+'frame',
					styles:this.options.dimensions
					}).adopt(new Element('div',{
						'class':'button',
						'id':'left',
						'events':events
						}),
					new Element('div',{
						id:this.options.cssvars.customID+'scroller',
						styles:{
							width:this.options.dimensions.width-102,
							height:this.options.dimensions.height
							}
						}).adopt(this.content.setStyle('width',this.sections.length*1600)),
					new Element('div',{
							'class':'button',
							'id':'right',
							'events':events
							}))
					)
			},
	fixIE:function(){
		this.filmstrip.getElement('hr').setStyle('display','none')
		},	
	scrollSection:function(element){
		element=$($(element||this.sections[0]).id.replace('-pane','-tab'));
		var oldactive=element.getParent().getElement('.current');
		if(oldactive)oldactive.removeClass('current');
		element.addClass('current');
		$(element.id.replace('-tab','-pane'));
		var offset = this.sectionptr.indexOf(this.filmstrip.getElement('.current').id);
		if (offset > 0)
			{offset = (offset * (this.options.dimensions.width-102));}
		this.scroller.scrollFX.scrollTo(offset,false)
		},
	scrollArrow:function(element){
		var direction=Math.pow(-1,['left','right'].indexOf(element.id)+1);
		var current=this.sectionptr.indexOf(this.filmstrip.getElement('.current').id);
		var to=current+direction;
		this.scrollSection(this.sectionptr[to<0?this.sectionptr.length-1:to%this.sectionptr.length])
		},
	autoScroll:function() {
		var currentid = (this.filmstrip.getElement('.current').id); 
		var current=this.sectionptr.indexOf(this.filmstrip.getElement('.current').id); 
		var direction=Math.pow(-1,['left','right'].indexOf(currentid.id)+1);
		var to=current+direction;
		this.scrollSection(this.sectionptr[to<0?this.sectionptr.length-1:to%this.sectionptr.length]);
		},
	autoScrollStart: function () {
		if (this.options.autoScroll.interval > 0)
		 this.autoSlide = this.autoScroll.bind(this).periodical(this.options.autoScroll.interval); 
		},
	autoScrollStop: function() {
		var that=this;
		if (this.options.autoScroll.interval > 0)
			$clear(that.autoSlide);
		}
	});
							
	gl_Slide.implement(new Options);