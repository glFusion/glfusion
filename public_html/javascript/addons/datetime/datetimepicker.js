// flexible date time picker for glFusion using xdsoft_datetimepicker
$(document).ready(function(){
	$('.popupcal').each(function(i, obj) {
		var id = $(this).attr('id');
		datetimepicker_popupcal(id);
	});
});
function datetimepicker_popupcal( selector ) {
	var currentDT = $("#"+selector).val();
	$('#'+selector).val( currentDT );
	$('#'+selector).datetimepicker({
		lazyInit: true,
		value:currentDT,
		format:'Y-m-d H:i',
	});
}
