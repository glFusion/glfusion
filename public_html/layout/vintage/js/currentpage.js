// tests to see if the current url matches a link in the top horizontal menu, if true it adds
// class="currentpage" to the a href tag so it can be styled from style.css adapted from
// http://www.richnetapps.com/automatically_highlight_current_page_in/

function extractPageName(hrefString)
{
    var arr = hrefString.split('/');
    return  (arr.length < 2) ? hrefString : arr[arr.length-2].toLowerCase() + arr[arr.length-1].toLowerCase();
}
 
function setActiveMenu(arr, crtPage)
{
    for (var i=0; i < arr.length; i++)
    {
        if(extractPageName(arr[i].href) == crtPage)
        {
            if (arr[i].parentNode.tagName != "div")
            {
                arr[i].className = "currentpage";
                arr[i].parentNode.className = ""; //can also assign a class to the parent of the a tag
            }
        }
    }
}

// change the getElementById values below if you use a different menu name
function setPage()
{
    hrefString = document.location.href ? document.location.href : document.location;
    if (document.getElementById("menu_navigation") !=null )
    setActiveMenu(document.getElementById("menu_navigation").getElementsByTagName("a"), extractPageName(hrefString));
    //uncomment below to declare multiple menus, like the block menu
    // if (document.getElementById("menu_block") !=null )
    // setActiveMenu(document.getElementById("menu_block").getElementsByTagName("a"), extractPageName(hrefString));
}

window.onload=function()
{
    setPage();
}