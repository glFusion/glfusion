<?php

require_once 'lib-common.php';

$userMenu = new menu(0);
$userMenu->type = MENU_GENERIC;
$userMenu->active = 1;
$userMenu->name = 'user';
$menuData = $userMenu->_adminMenu();

$retval = '<ul>';
foreach ( $menuData as $item ) {
    $retval .= '<li><a href="'.$item['url'].'">'.$item['label'].'</a></li>';
}
$retval .= '</ul>';

echo $retval;
?>