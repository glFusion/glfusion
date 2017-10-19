/*
  RoxyFileman - web based file manager. Ready to use with CKEditor, TinyMCE.
  Can be easily integrated with any other WYSIWYG editor or CMS.

  Copyright (C) 2013, RoxyFileman.com - Lyubomir Arsov. All rights reserved.
  For licensing, see LICENSE.txt or http://RoxyFileman.com/license

  This program is free software: you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation, either version 3 of the License.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program.  If not, see <http://www.gnu.org/licenses/>.

  Contact: Lyubomir Arsov, liubo (at) web-lobby.com
*/
function FileSelected(file){
  /**
   * file is an object containing following properties:
   *
   * fullPath - path to the file - absolute from your site root
   * path - directory in which the file is located - absolute from your site root
   * size - size of the file in bytes
   * time - timestamo of last modification
   * name - file name
   * ext - file extension
   * width - if the file is image, this will be the width of the original image, 0 otherwise
   * height - if the file is image, this will be the height of the original image, 0 otherwise
   *
   */
  // Get the ID of the input to fill
  var fieldId = RoxyUtils.GetUrlParam('txtFieldId');
  var videoText = '<video class="uk-responsive-width" controls="controls" preload="auto">  <source type="video/mp4" src="' + file.fullPath + '" />    <!-- Flash fallback for non-HTML5 browsers without JavaScript -->    <object width="320" height="240" type="application/x-shockwave-flash" data="{player_url}flashmediaelement.swf">      <param name="movie" value="{player_url}flashmediaelement.swf" />      <param name="flashvars" value="controls=true&file={movie}" />      <!-- Image as a last resort -->      <img src="' + file.fullPath + '" width="320" height="160" title="No video playback capabilities" />    </object></video>';
  $(window.parent.document).find('#' + fieldId + '_img').html(videoText);
  $(window.parent.document).find('#' + fieldId).attr('value', file.fullPath);
  window.parent.closeStoryVideo();
}

function GetSelectedValue(){
  /**
  * This function is called to retrieve selected value when custom integration is used.
  * Url parameter selected will override this value.
  */

  return "";
}
