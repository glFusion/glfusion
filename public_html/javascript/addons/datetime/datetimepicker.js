// flexible date time picker for glFusion using xdsoft_datetimepicker
$(document).ready(function(){
	$('.popupcal').each(function(i, obj) {
		var id = $(this).attr('id');
		datetimepicker_popupcal(id);
	});
	$('.datepicker').each(function(i, obj) {
		var id = $(this).attr('id');
		datetimepicker_datepicker(id);
	});
	$('.timepicker').each(function(i, obj) {
		var id = $(this).attr('id');
		datetimepicker_timepicker(id);
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
function datetimepicker_datepicker( selector ) {
	var currentDT = $("#"+selector).val();
	$('#'+selector).val( currentDT );
	$('#'+selector).datetimepicker({
		lazyInit: true,
		value:currentDT,
		format:'Y-m-d',
		timepicker: false,
	});
}
function datetimepicker_timepicker( selector ) {
	var currentDT = $("#"+selector).val();
	$('#'+selector).val( currentDT );
	$('#'+selector).datetimepicker({
		lazyInit: true,
		value:currentDT,
		format:'H:i',
		datepicker: false,
		step: 15,
	});
}
