// flexible date time picker for glFusion
$(document).ready(function(){
    $('.popupcal').each(function(i, obj) {
        var id = $(this).attr('id');
        usePopupCalendar(id);
    });
});
function usePopupCalendar( selector ) {
	$('#'+selector).on("click", function(){ datetimepicker_popupcal(selector); });
	$('#'+selector).datetimepicker();
}
function datetimepicker_popupcal( selector ) {
	var currentDT = getCurrentDateTimeSelection( selector );
	$('#'+selector).val( currentDT );
	$('#'+selector).datetimepicker({
		value:currentDT,
		format:'Y.m.d H:i',
		onSelectDate( dp,$input ) {
			var id = $input.attr('id');
			var month = parseInt($input.val().substr(5,2),10);
			var day   = $input.val().substr(8,2);
			var year  = parseInt($input.val().substr(0,4),10);
			$("select[name='" + id + "_month']").val(month);
			$("select[name='" + id + "_day']").val(day);
			$("select[name='" + id + "_year']").val(year);
		},
		onSelectTime( dp,$input ) {
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
	});
}
function getCurrentDateTimeSelection( selectorName ) {
	var year, month, day, hour, minute, ampm, d, t;

	year  = $("select[name='" + selectorName + "_year']").val();
	month = '0' + $("select[name='" + selectorName + "_month']").val();
	day   = '0' + $("select[name='" + selectorName + "_day']").val();
	month = month.substr(month.length - 2, 2);
	day   = day.substr(day.length - 2, 2);
	d = year + '.' + month + '.' + day;
	hour   = '0' + $("select[name='" + selectorName + "_hour']").val();
	minute = '0' + $("select[name='" + selectorName + "_minute']").val();
	ampm   = $("select[name='" + selectorName + "_ampm']").val();
	hour   = hour.substr(hour.length - 2, 2);
	minute = minute.substr(minute.length - 2, 2);
	if ( ampm == 'pm' ) hour = parseInt(hour,10) + 12;
    t = hour + ':' + minute;
	return  d + ' ' + t ;
}