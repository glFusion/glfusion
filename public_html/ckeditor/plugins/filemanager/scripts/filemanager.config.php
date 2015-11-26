<?php
/**
* glFusion CMS
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2014-2015 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*/

require_once '../../../../lib-common.php';

$iid = 'fm_config_'.CACHE_security_hash();
$fm_user_cfg_file = CACHE_instance_filename($iid);
$content = @file_get_contents($fm_user_cfg_file);
if ( $content === false ) die();
echo $content;
?>