var xmlhttp = null;

/* Only really need the record id of the result to delete, but the fieldid is used to re-create the html.
*  Need to re-attach / replace the HTML for the file listing
*  The fieldid is part of the ID field used to define the DOM attrbute (table)
*/
function ajaxDeleteFile(topic, fileid) {

    if (confirm('Are you sure you want to Delete the File?')) {
        //alert('field:'+fieldid+',rec:'+rec);

        xmlhttp = new XMLHttpRequest();
        var qs = '?topic=' + topic + '&id=' + fileid;
        xmlhttp.open('GET', site_url + '/forum/ajaxdelfile.php' + qs, true);
        xmlhttp.onreadystatechange = handleResponse;

        xmlhttp.send(null);
    }
}

function handleResponse() {
    if (xmlhttp.readyState == 4 ) {
        if (xmlhttp.status == 200 ) {

            var response = xmlhttp.responseText;
            var update = new Array();

            changeText('fileattachlist',response);
        }
    }
}

function changeText ( div2show, text ) {
    // Detect Browser
    var IE = (document.all) ? 1 : 0;
    var DOM = 0;
    if ( parseInt(navigator.appVersion) >= 5) {DOM=1};

    if (DOM) {
        var viewer = document.getElementById(div2show);
        viewer.innerHTML = text;
    } else if (IE) {
        document.all[div2show].innerHTML = text;
    }
}