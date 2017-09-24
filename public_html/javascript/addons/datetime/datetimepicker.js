// flexible date time picker for glFusion using xdsoft_datetimepicker
$(document).ready(function(){
	$('.popupcal').each(function(i, obj) {
		var id = $(this).attr('id');
		datetimepicker_popupcal(id);
	});
});
function selectedDate($input) {
	var id = $input.attr('id');
	var month = parseInt($input.val().substr(5,2),10);
	var day   = $input.val().substr(8,2);
	var year  = parseInt($input.val().substr(0,4),10);
	$("select[name='" + id + "_month']").val(month);
	$("select[name='" + id + "_day']").val(day);
	$("select[name='" + id + "_year']").val(year);
}
function selectedTime($input) {
	var id = $input.attr('id');
	var hour = $input.val().substr(11,2);
	var minute = $input.val().substr(14,2);
	var ampm = 'am';
	if ( hour > 12 ) {
		hour = hour - 12;
		if ( hour < 10 ) {
			hour = '0' + hour;
		}
		ampm = 'pm';
	} else if ( hour == 12 ) {
		ampm = 'pm';
	} else if ( hour == 0 ) {
		hour = 12;
		ampm = 'am';
	}
	$("select[name='" + id + "_hour']").val(hour);
	$("select[name='" + id + "_minute']").val(minute);
	$("select[name='" + id + "_ampm']").val(ampm);
}
function datetimepicker_popupcal( selector ) {
	var currentDT = getCurrentDateTimeSelection( selector );
	$('#'+selector).val( currentDT );
	$('#'+selector).datetimepicker({
		lazyInit: true,
		value:currentDT,
		format:'Y.m.d H:i',
		onSelectDate( dp,$input ) {
			selectedDate($input);
		},
		onSelectTime( dp,$input ) {
			selectedTime($input);
		}
	});
}
function getCurrentDateTimeSelection( selector ) {
	var year, month, day, hour, minute, ampm, d, t;
	year  = $("select[name='" + selector + "_year']").val();
	month = '0' + $("select[name='" + selector + "_month']").val();
	day   = '0' + $("select[name='" + selector + "_day']").val();
	month = month.substr(month.length - 2, 2);
	day   = day.substr(day.length - 2, 2);
	d = year + '.' + month + '.' + day;
	hour   = '0' + $("select[name='" + selector + "_hour']").val();
	minute = '0' + $("select[name='" + selector + "_minute']").val();
	ampm   = $("select[name='" + selector + "_ampm']").val();
	hour   = hour.substr(hour.length - 2, 2);
	minute = minute.substr(minute.length - 2, 2);
	if ( ampm == 'pm' ) hour = parseInt(hour,10) + 12;
	t = hour + ':' + minute;
	return  d + ' ' + t ;
}