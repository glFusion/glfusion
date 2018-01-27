/*! glFusion CMS - License GNU General Public License version 2 or later
*  Copyright (C) 2016-2017 by Mark R. Evans - mark AT glfusion DOT org */
$(document).ready(function () {
	$(document).on('click','#tfa-enroll', {} ,function(e){
		e.preventDefault();
		$.ajax({
			type: "POST",
			url: glfusionSiteUrl+"/twofactor.php",
			data: {
				action: 'enroll'
			},
			success: function(result) {
				panel = result;
				$('#tfa-panel').html(panel);
			},
			error: function(result) {
				console.log(result);
				alert(lang_ajax_error);
			}
		});
	});
	$(document).on('click','#tfa-verify', {} ,function(e){
		e.preventDefault();
		$.ajax({
			type: "POST",
			url: glfusionSiteUrl+"/twofactor.php",
			data: {
				action: 'verify',
				tfacode: $('#twofactorverify').val(),
				_sectoken: $("#_sectoken").val()
			},
			dataType : "json",
			success: function(data) {
				var result = $.parseJSON(data["js"]);
				if ( result.errorCode == 0 ) {
					panel = result.panel;
					$('#tfa-panel').html(panel);
				} else {
					$('#tfa-error').html('<span class="uk-text-bold uk-text-danger">'+result.message+'</span>');
					$('#twofactorverify').val('');
				}
			},
			error: function(result) {
				console.log(result);
				alert(lang_ajax_error);
			}
		});
	});
	$(document).on('click','#tfa-regenerate', {} ,function(e){
		e.preventDefault();
		$.ajax({
			type: "POST",
			url: glfusionSiteUrl+"/twofactor.php",
			data: {
				action: 'regenerate',
				tfacode: $('#twofactorverify').val()
			},
			dataType : "json",
			success: function(data) {
				var result = $.parseJSON(data["js"]);
				if ( result.errorCode == 0 ) {
					var bc_data = result.list;
					$('#bc-list').empty();
					$ulSub = $("#bc-list");
					$.each(bc_data, function (i,item) {
						$ulSub.append('<li>' + item + '</li>');
					});
					$("#tfa-regenerate").attr("disabled", "disabled");
					$('#backup-codes').show();
				} else {
					console.log(data);
					alert(lang_ajax_error);
				}
			},
			error: function(result) {
				console.log(result);
				alert(lang_ajax_error);
			}
		});
	});
	$("#tfa-disable").click(function(e) {
		e.preventDefault();
		UIkit.modal.confirm(lang_disable_warning, function(){
			$.ajax({
				type: "POST",
				url: glfusionSiteUrl+"/twofactor.php",
				data: {
					action: 'disable',
				_sectoken: $("#_sectoken").val()
				},
				dataType : "json",
				success: function(data) {
					var result = $.parseJSON(data["js"]);
					if ( result.errorCode == 0 ) {
						panel = result.panel;
						$('#tfa-panel').html(panel);
					}
				},
				error: function(result) {
					console.log(result);
					alert(lang_ajax_error);
				}
			});

		}, function() {
			// nop
		});
	});
});