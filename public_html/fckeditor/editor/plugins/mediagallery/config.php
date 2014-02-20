<?php
// +---------------------------------------------------------------------------+
// | Media Gallery Plugin 1.6                                                  |
// +---------------------------------------------------------------------------+
// | $Id::                                                                    $|
// +---------------------------------------------------------------------------+
// | Copyright (C) 2005-2008 by the following authors:                         |
// |                                                                           |
// | Mark R. Evans               -    mark@gllabs.org                          |
// +---------------------------------------------------------------------------+
// |                                                                           |
// | This program is free software; you can redistribute it and/or             |
// | modify it under the terms of the GNU General Public License               |
// | as published by the Free Software Foundation; either version 2            |
// | of the License, or (at your option) any later version.                    |
// |                                                                           |
// | This program is distributed in the hope that it will be useful,           |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of            |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the             |
// | GNU General Public License for more details.                              |
// |                                                                           |
// | You should have received a copy of the GNU General Public License         |
// | along with this program; if not, write to the Free Software Foundation,   |
// | Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.           |
// |                                                                           |
// +---------------------------------------------------------------------------+
//


// Auto Tag defaults - sets default values for MG Media Browser
$_mgMB_CONF['at_border']          = 1;			// 0 = disable border   -- 1 = enable border
$_mgMB_CONF['at_align']           = 'auto';		// auto, none, right, left
$_mgMB_CONF['at_width']           = 0;			// 0=use default or specify pixels
$_mgMB_CONF['at_height']          = 0;          // 0=use default or specify pixels
$_mgMB_CONF['at_src']             = 'tn';		// tn, disp, orig
$_mgMB_CONF['at_autoplay']        = 0;			// 0 = disable, 1 = enable
$_mgMB_CONF['at_enable_link']     = 1;			// 0 = disable, 1 = enable
$_mgMB_CONF['at_delay']           = 10;         // seconds to delay between slides (slideshow / fslideshow)
$_mgMB_CONF['at_alturl']          = 0;          // Use alternate URL for link (if defined with the media item)
$_mgMB_CONF['enable_dest']        = 1;
?>