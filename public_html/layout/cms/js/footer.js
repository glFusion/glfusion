/*! glFusion CMS v2.0.0 | https://www.glfusion.org | (c) 2017-2021 glFusion | GNU GPL v2 License */
$(window).load(function(){pf()});
$('[data-uk-switcher]').on('show.uk.switcher', function(event, area) {pf();});
$('[data-uk-tab]').on('click', function(event, active_item, previous_item) {pf();});
$('.uk-alert').on('closed.uk.alert', function(){pf();});
$( window ).resize(function(){pf();});
$(window).load(function(){
	if ($(window).scrollTop()>100){
		$("#scroll-to-top").fadeIn(1500);
	}
	$(function () {
		$(window).scroll(function(){
			if ($(window).scrollTop()>100){
				$("#scroll-to-top").fadeIn(1500);
			} else {
				$("#scroll-to-top").fadeOut(750);
			}
		});
	});
});
UIkit.modal.help = function(content, options) {
	options = UIkit.$.extend(true, {bgclose:true, keyboard:true, modal:true, labels:UIkit.modal.labels}, options);
	var modal = UIkit.modal.dialog(([
	'<div class="uk-margin uk-modal-content">'+String(content)+'</div>',
	'<div class="uk-modal-footer uk-text-right"><button class="uk-button uk-button-primary uk-modal-close">'+options.labels.Ok+'</button></div>'
	]).join(""), options);
	modal.on('show.uk.modal', function(){
		setTimeout(function(){
			modal.element.find('button:first').focus();
		}, 50);
	});
	return modal.show();
};
$('.block-help-icon').on('click', function(e) {
	e.preventDefault();
	var help_url = $(this).attr('href');
	var modaltext = '<iframe style="width:100%;" src="'+help_url+'"></iframe>';
	UIkit.modal.help(modaltext);
	return false;
});
