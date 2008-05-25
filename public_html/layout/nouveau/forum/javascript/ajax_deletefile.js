var xmlhttp = null;


/* Only really need the record id of the result to delete, but the fieldid is used to re-create the html.
*  Need to re-attach / replace the HTML for the file listing
*  The fieldid is part of the ID field used to define the DOM attrbute (table)
*/
function ajaxDeleteFile(topic, fileid) {

    if (confirm('Delete file')) {
        //alert('field:'+fieldid+',rec:'+rec);

        xmlhttp = new XMLHttpRequest();
        var qs = '?topic=' + topic + '&id=' + fileid;
        xmlhttp.open('GET', site_url + '/forum/ajaxdelfile.php' + qs, true);
        xmlhttp.onreadystatechange = function() {
          if (xmlhttp.readyState == 4) {
            receiveFileListing(xmlhttp.responseXML);
          }
        };
        xmlhttp.send(null);
    }
}

function receiveFileListing(dom) {

    var ocontent = dom.getElementsByTagName('content');

    // Get HTML content returned and updated displayed Template Variables
    var obj = document.getElementById('tblforumfile');
    if (obj.parentNode) {
        // Check for chunks of data returning - look into using the DOM normalize method
        html = ocontent[0].childNodes[0].nodeValue;
        if (ocontent[0].childNodes[1]) {
            html = html + ocontent[0].childNodes[1].nodeValue;
        }
        if (ocontent[0].childNodes[2]) {
            html = html + ocontent[0].childNodes[2].nodeValue;
        }
        obj.parentNode.innerHTML = html;
    }

}