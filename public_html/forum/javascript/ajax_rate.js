var xmlhttp = null;

    function ajax_voteuser(vid,pid,tid,vote,vmode) {

        xmlhttp = new XMLHttpRequest();

        var qs = '';

        qs = '?vid=' + vid + '&pid=' + pid + '&tid=' + tid + '&vote=' + vote + '&mode=' + vmode;

        xmlhttp.open('GET', site_url + '/forum/ajaxrate.php' + qs, true);
        xmlhttp.onreadystatechange = function() {
            if (xmlhttp.readyState == 4) {
                receiveForumRate(xmlhttp.responseXML,pid);
            }
        };
        xmlhttp.send(null);

    }
    function receiveForumRate(dom,pid) {
        var id = '';
        try{
            var oxml = dom.getElementsByTagName('topic');
            id = oxml[0].childNodes[0].nodeValue;
        }catch(e){}
        var html = '';
        try{
            var oxml = dom.getElementsByTagName('html');
            html = oxml[0].childNodes[0].nodeValue;
        }catch(e){}

        if (id != '' && html != '') {

            var divs = document.getElementsByTagName("div");
            for (var i = 0; i < divs.length; i++) {
              if (divs[i].className == 'c'+pid) {
                divs[i].innerHTML = html;
              }
            }
        }
    }