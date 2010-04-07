<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | lib-io.php                                                               |
// |                                                                          |
// | Input / Output library                                                   |
// +--------------------------------------------------------------------------+
// | $Id::                                                                   $|
// +--------------------------------------------------------------------------+
// | Copyright (C) 2009 by the following authors:                             |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// +--------------------------------------------------------------------------+
// |                                                                          |
// | This program is free software; you can redistribute it and/or            |
// | modify it under the terms of the GNU General Public License              |
// | as published by the Free Software Foundation; either version 2           |
// | of the License, or (at your option) any later version.                   |
// |                                                                          |
// | This program is distributed in the hope that it will be useful,          |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of           |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            |
// | GNU General Public License for more details.                             |
// |                                                                          |
// | You should have received a copy of the GNU General Public License        |
// | along with this program; if not, write to the Free Software Foundation,  |
// | Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.          |
// |                                                                          |
// +--------------------------------------------------------------------------+
//

if (!defined ('GVERSION')) {
    die ('This file can not be used on its own.');
}

/**
 * Add CSS to a page
 *
 * This adds raw CSS, it should not be wrapped in a <style> tag.
 *
 * @param  string   $code The CSS code
 * @param  int      $priority Load priority
 * @param  string   $mime The mime type of the CSS, 'text/css' used if no
 *                  other type passed.
 * @return nothing
 */
function IO_addStyle($code, $priority = HEADER_PRIO_NORMAL, $mime = 'text/css')
{
    global $pageHandle;

    $pageHandle->addStyle($code, $priority, $mime);
}

/**
 * Add JavaScript to a page
 *
 * This adds raw JavaScript, it should not be wrapped in a <script> tag.
 *
 * @param  string   $code       The JavaScript code
 * @param  int      $priority   Load priority
 * @param  string   $mime       The mime type of the JS, 'text/javascript'
 *                              used if no other type passed.
 * @return nothing
 */
function IO_addScript($code, $priority = HEADER_PRIO_NORMAL, $mime = 'text/javascript')
{
    global $pageHandle;

    $pageHandle->addScript($code, $priority, $mime);
}

/**
 * Add a Link to a page
 *
 * This adds raw Link, it should not be wrapped in a <link> tag.
 *
 * @param  string   $rel        Link relationship
 * @param  string   $href       The link href
 * @param  string   $type       Type attribute (optional)
 * @param  int      $priority   Load priority
 * @param  array    $attr       Attributes array
 *
 * @return nothing
 */
function IO_addLink($rel, $href, $type = '', $priority = HEADER_PRIO_NORMAL, $attrs = array())
{
    global $pageHandle;

    $pageHandle->addLink($rel,$href,$type,$priority,$attrs);
}

/**
 * Add a stylesheet to a page
 *
 * This adds a stylesheet to a page - The URL should not have the <link>
 * attribute.
 *
 * @param  string   $href       The URL to the stylesheet
 * @param  int      $priority   Load priority
 * @param  string   $mime       The mime type of the stylesheet, 'text/css'
 *                              used if no other type passed.
 * @param  array    $attr       Attributes array
 *
 * @return nothing
 */
function IO_addLinkStyle($href, $priority = HEADER_PRIO_NORMAL, $mime = 'text/css', $attrs = array())
{
    global $pageHandle;

    $pageHandle->addLinkStyle($href,$priority,$mime,$attrs);
}


/**
 * Add a JavaScript source to a page
 *
 * This adds a javascript source file to a page - The URL should not have
 * the <link> attribute.
 *
 * @param  string   $href       The URL to the javascript file
 * @param  int      $priority   Load priority
 * @param  string   $mime       The mime type of the stylesheet, 'text/css'
 *                              used if no other type passed.
 *
 * @return nothing
 */
function IO_addLinkScript($href, $priority = HEADER_PRIO_NORMAL, $mime = 'text/javascript')
{
    global $pageHandle;

    $pageHandle->addLinkScript($href,$priority,$mime);
}


/**
 * Add Meta data to header
 *
 * This adds a meta name to the header
 *
 * @param  string   $name       Name of the meta attribute
 * @param  string   $cotent     Content of the attribute
 *
 * @return nothing
 */
function IO_addMetaName($name, $content)
{
    global $pageHandle;

    $pageHandle->addMetaName($name,$content);
}


/**
 * Add Meta HTTP Equiv data to header
 *
 * This adds a meta name to the header
 *
 * @param  string   $header     header name
 * @param  string   $cotent     Content of the attribute
 *
 * @return nothing
 */
function IO_addMetaHttpEquiv($header, $content)
{
    global $pageHandle;

    $pageHandle->addMetaHttpEquiv($header,$content);
}


/**
 * Add a raw header
 *
 * Adds a raw header entry.  The code must be fully formed and include
 * all necessary HTML.  No manipulations are done to the data.
 *
 * @param  string   $code       Fully formed code to add
 * @param  int      $priority   Load priority
 *
 * @return nothing
 */
function IO_addRaw($code, $priority = HEADER_PRIO_NORMAL)
{
    global $pageHandle;

    $pageHandle->addRaw($code,$priority);
}


/**
 * Add a direct header
 *
 * Passes the $text variable directly to the PHP header() function.
 * These items are always sent first.
 *
 * @param  string   $text       direct text to send to browser.
 *
 * @return nothing
 */
function IO_addDirectHeader($text)
{
    global $pageHandle;

    $pageHandle->addDirectHeader($text);
}


/**
 * Add content to a page
 *
 * Stores the page contents (after the header)
 *
 * @param  string   $content    content to place in page.
 *
 * @return nothing
 */
function IO_addContent( $content ) {
    global $pageHandle;

    $pageHandle->addContent($content);
}


/**
 * Renders (displays) the current page
 *
 * Builds the full page for display
 *
 *
 * @return nothing
 */
function IO_displayPage()
{
    global $pageHandle;

    return $pageHandle->displayPage();
}

/**
 * Redirect
 *
 * Immediately redirects to the passed URL
 *
 * @param  string   $url    The URL to redirect to.
 *
 * @access public
 * @return nothing
 */
function IO_redirect($url)
{
    header("Location: " . $url);
//    echo "<html><head><meta http-equiv=\"refresh\" content=\"0; URL=$url\"" . XHTML . "></head></html>" . LB;
//    exit;
}

/**
 * Set the page title
 *
 * Sets the page title to display in the browser title bar
 *
 * @param  string   $title  The page title
 *
 * @return nothing
 */
function IO_setPageTitle($title) {
    global $pageHandle;

    $pageHandle->setPageTitle($title);
}


/**
 * Set Show Extra Blocks
 *
 * This function will set whether the extra blocks should be displayed.
 *
 * @param  bool     $bool   True or False
 *
 * @access public
 * @return nothing
 */
function IO_setShowExtraBlocks( $bool )
{
    global $pageHandle;

    $pageHandle->setShowExtraBlocks($bool);
}


/**
 * Set Show Navigation Blocks
 *
 * This function will set whether the navigation blocks should be displayed.
 *
 * @param  bool     $bool   True or False
 *
 * @access public
 * @return nothing
 */
function IO_setShowNavigationBlocks( $bool )
{
    global $pageHandle;

    $pageHandle->setShowNavigationBlocks($bool);
}


/**
 * Display Error
 *
 * Displays an error message, ignoring any content already added
 * via the addContent() call.  This function terminates the running script.
 *
 * @param  string   $content    The error message to display
 *
 * @return nothing
 */
function IO_displayError($text)
{
    global $pageHandle;

    $pageHandle->displayError($text);
}

/**
* Builds crawler friendly URL if URL rewriting is enabled
*
* This function will attempt to build a crawler friendly URL.  If this feature is
* disabled because of platform issue it just returns original $url value
*
* @param        string      $url    URL to try and convert
* @return       string      rewritten if _isenabled is true otherwise original url
*
*/
function IO_buildURL($url)
{
    global $pageHandle;

    return $pageHandle->buildURL($url);
}



/**
 * Display Login Required
 *
 * Displays an error message, ignoring any content already added
 * via the addContent() call.  This function terminates the running script.
 *
 * @param  string   $content    The error message to display
 *
 * @return nothing
 */

function IO_displayLoginRequired()
{
    global $pageHandle;

    $pageHandle->displayLoginRequired();
}


/*
 * Message handler (message.class.php) APIs
 */

/**
 * Adds a message block to the top of the page
 *
 * This function takes a message number and optional plugin name
 * It will format the message text in the standard system message
 * box and ensure it is displayed at the top of the page.
 *
 * @param  int      $msg    The message number
 * @param  string   $plugin Optional plugin name
 *
 * @access public
 * @return nothing
 */
function IO_addMessage( $msg, $plugin = '')
{
    global $MESSAGE, $_CONF;

    if ($msg > 0) {
        $timestamp = strftime($_CONF['daytime']);
        if (!empty($plugin)) {
            $var = 'PLG_' . $plugin . '_MESSAGE' . $msg;
            global $$var;
            if (isset($$var)) {
                $message = $$var;
            } else {
                $message = sprintf($MESSAGE[61], $plugin);
                COM_errorLog($MESSAGE[61] . ": " . $var, 1);
            }
        } else {
            $message = $MESSAGE[$msg];
        }
    }

    if ( $plugin == '' ) {
        $plugin = 'glFusion';
    }

    $messageHandle =& messageHandler::getInstance();
    $messageHandle->addMessage(GENERAL,$plugin,$message);
}

function IO_addMessageText( $class, $type, $text )
{
    $messageHandle =& messageHandler::getInstance();
    $messageHandle->addMessage($class,$type,$text);
}

/*
 * This section contains the input filtering APIs
 */

/**
 * Retrieves a $_POST, $_GET, $_REQUEST, $_ENV, or $_COOKIE variable.
 *
 * @param  string   $type    Type of data to retrieve
 *                             - int, float, strict, plain, html, free, ...
 * @param  string   $key     Array key
 * @param  array    $mode    Type of data; post, get, request, env, cookie
 *                           must specify in order of precedence
 * @param  string   $default Default return value if not found
 *
 * @note This will return the first item found when passing an array of
 *       modes. If you pass array('get','post') and the $_GET variable is
 *       set, the $_GET variable will be returned.
 *
 * @access public
 * @return varible
 */

function IO_getVar( $type, $key, $mode=array('post'), $default='' )
{
    global $inputHandler;

    return $inputHandler->getVar($type, $key, $mode, $default );
}

/**
 * Filters and returns a variable
 *
 * @param  string   $type    Type of data to retrieve
 *                             - int, float, strict, plain, html, free, ...
 * @param  string   $key     Array key or data to filter
 * @param  array    $A       Array of data to filter
 * @param  array    $mode    Type of data; post, get, request, env, cookie
 * @param  string   $default Default return value if not found
 *
 * @access public
 * @return varible
 *
 * @note Pass only $key to filter a single variable
 */
function IO_filterVar( $type, $key, $A='', $default='' )
{
    global $inputHandler;

    return $inputHandler->filterVar($type, $key, $A, $default);
}

/** buttonCheck -- Find which button was pressed on form.
 *
 *  Instead of doing this:
 *      <input type="submit" value="{lang_save}" name="mode">
 *      <input type="submit" value="{lang_cancel}" name="mode">
 *
 *  and checking
 *      if ($_POST['mode'] == $LANG_ADMIN['save']) ...
 *
 *  Do this:
 *      <input type="submit" value="{lang_save}" name="save">
 *      <input type="submit" value="{lang_cancel}" name="cancel">
 *
 *  and check this way:
 *      $mode = $inputHandler->buttonCheck(array('save','cancel'), $_POST, '');
 *      if ($mode == 'save') ...
 *
 *  @param  string  $buttonlist     List of buttons.
 *  @param  array   $A              Array such as $_GET, $_POST, $_COOKIE or $_REQUEST
 *  @param  mixed   $default        If no entry is in the array, return this default value.
 *  @param  boolean $return_name    If true, the returned name is the name in the buttonlist
 *                                  If false, the return value is the key of the name in
 *                                  the buttonlist
 *  @return mixed                   The given button or the associated key of the button
 */
function IO_buttonCheck($buttonList, $A, $default = '', $return_name = true)
{
    global $inputHandler;

    return $inputHandler->buttonCheck($buttonList, $A, $default, $return_name);
}

/**
* Prepare data for DB use
*
* @param    string  $str                Data to be sanitized
* @return   string                      Escaped string
*
*/
function IO_prepareForDB($str) {
    return mysql_real_escape_string ($str);
}

function IO_getImage($image, $namespace='')
{
    global $pageHandle;

    return $pageHandle->getImage($image,$namespace);
}
?>