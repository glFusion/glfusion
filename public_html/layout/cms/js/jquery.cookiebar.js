function getLanguage() {
    return 'en';
    return $('#dataLang').data('lang');
}

function privacyLink() {
    if (!isMobile()) {
        return '<a href="page.php?page=privacy-policy">Privacy Policy</a>';
//        return '<a href="' + $("#footer-copyright").attr('href') + '" target="_blank">' + $("#footer-copyright").html() + '</a>';

    }
    return "";
}

function isMobile() {
    if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
        return true;
    }
}

function showMessage() {
    var message = new Array();
    message["da"] = "We use cookies to give you the best experience. If you continue, we\'ll assume you accept to receive all cookies from our website.";
    message["de"] = "Für eine optimale Benutzererfahrung werden Cookies eingesetzt. Durch die Weiterbenutzung unserer Webseite akzeptieren Sie alle damit verbundenen Cookies.";
    message["el"] = "We use cookies to give you the best experience. If you continue, we\'ll assume you accept to receive all cookies from our website.";
    message["en"] = "We use cookies to give you the best experience. If you continue, we\'ll assume you accept to receive all cookies from our website.";
    message["es"] = "Utilizamos cookies para darte una mejor experiencia de uso. Si continuas navegando asumimos que aceptas el uso que hacemos de las cookies";
    message["fi"] = "We use cookies to give you the best experience. If you continue, we\'ll assume you accept to receive all cookies from our website.";
    message["fr"] = "Nous employons des cookies pour vous assurer une expérience idéale. En continuant, vous acceptez de recevoir tous les cookies de notre site.";
    message["it"] = "Utilizziamo cookie per offrirti la migliore esperienza. Se continui, si suppone che accetti di ricevere tutti i cookie dal nostro sito.";
    message["ja"] = "We use cookies to give you the best experience. If you continue, we\'ll assume you accept to receive all cookies from our website.";
    message["ko"] = "We use cookies to give you the best experience. If you continue, we\'ll assume you accept to receive all cookies from our website.";
    message["nl"] = "We use cookies to give you the best experience. If you continue, we\'ll assume you accept to receive all cookies from our website.";
    message["no"] = "We use cookies to give you the best experience. If you continue, we\'ll assume you accept to receive all cookies from our website.";
    message["pt"] = "Nós usamos os cookie para dar-lhe a melhor experiência. Se continuar, vamos assumir que aceita receber todos os cookie do nosso website.";
    message["ru"] = "We use cookies to give you the best experience. If you continue, we\'ll assume you accept to receive all cookies from our website.";
    message['sv'] = "We use cookies to give you the best experience. If you continue, we\'ll assume you accept to receive all cookies from our website.";
    message['zh-Hant'] = "We use cookies to give you the best experience. If you continue, we\'ll assume you accept to receive all cookies from our website.";
    return message[getLanguage()];
}

function getDomain() {
    var parts = location.hostname.split('.');
    return String(parts.slice(-2).join('.'));
}

function getCookie(cname)
{
    var name = cname + "=";
    var ca = document.cookie.split(';');
    for (var i = 0; i < ca.length; i++)
    {
        var c = $.trim(ca[i]);
        if (c.indexOf(name) == 0)
            return c.substring(name.length, c.length);
    }
    return "";
}

function setCookie(domain, value) {
    var baseDomain = '.' + domain;
    var expireAfter = new Date();
    expireAfter.setDate(expireAfter.getDate() + 365);
    document.cookie = "CookieLaw=" + value + "; domain=" + baseDomain + "; expires=" + expireAfter + "; path=/";
}

function showBar() {
    $('#cookie-bar').html('<p>' + showMessage() + privacyLink() + ' <a href="#" class="cb-enable"><i class="icon-remove-circle fa fa-times-circle-o"></i></a></p>');
//    $('body').prepend('<div id="cookie-bar"><p>' + showMessage() + privacyLink() + ' <a href="#" class="cb-enable"><i class="icon-remove-circle fa fa-times-circle-o"></i></a></p></div>');
}

function hideBar() {
    $("#cookie-bar").remove();
}

function acceptCookie() {
    $('#cookie-bar').slideUp(300, function() {
        setCookie(getDomain(), true);
        hideBar();
    });
}

$(document).ready(function() {
    if (getCookie('CookieLaw') != 'true') {
        showBar();
        if (!isMobile()) {
            $('#cookie-bar .cb-enable').click(function(event) {
                event.preventDefault();
                acceptCookie();
            });
        }else{
            $('#cookie-bar').click(function(event) {
                event.preventDefault();
                acceptCookie();
            });
        }
        setTimeout(acceptCookie, 15000);
    } else {
        hideBar();
    }
});