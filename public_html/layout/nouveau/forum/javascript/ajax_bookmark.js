var xmlhttp = null;

    // AJAX Functions to toggle user's bookmark for the topic

    function ajax_toggleForumBookmark(id) {

        xmlhttp = new XMLHttpRequest();

        var qs = '';
        if (id != '') {
            qs =  '?id=' + id;
        }

        xmlhttp.open('GET', site_url + '/forum/ajaxbookmark.php' + qs, true);
        xmlhttp.onreadystatechange = function() {
           if (xmlhttp.readyState == 4) {
                receiveForumBookmark(xmlhttp.responseXML);
              }
       };
        xmlhttp.send(null);

    }

    function receiveForumBookmark(dom) {
        // Update the available forum post bookmark icon

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
            var obj = document.getElementById('forumbookmark' + id);
            obj.innerHTML = html;
        }
    }