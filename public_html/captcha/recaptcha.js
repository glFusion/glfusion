var onloadCallbackRC = function() {
	var recaptchas = document.querySelectorAll('div[class=g-recaptcha]');
	for( i = 0; i < recaptchas.length; i++) {
		grecaptcha.render( recaptchas[i].id );
	}
}
function cp_enable() {}