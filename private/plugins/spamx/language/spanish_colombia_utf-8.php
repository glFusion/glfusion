<?php
/**
* glFusion CMS
*
* UTF-8 Spam-X Language File
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2008-2018 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*  Based on prior work Copyright (C) 2004-2008 by the following authors:
*  Tom Willett          tomw AT pigstye DOT net
*
*/

if (!defined ('GVERSION')) {
    die ('This file can not be used on its own.');
}

global $LANG32;

$LANG_SX00 = array (
	'comdel'	    => ' comentarios borrados.',
	'deletespam'    => 'Borrar Spam',
	'masshead'	    => '<hr><center><h1>Borrado Masivo de Comentarios Spam</h1></center>',
	'masstb'        => '<hr><h1 align="center">Mass Delete Trackback Spam</h1>',
	'note1'		    => '<p>Nota: Borrado masivo está para ayudarte cuando te avasalla ',
	'note2'		    => ' comentarios de spam y Spam-X no lo pilla.  <ul><li>Primero, encuentra el/los enlace/s u otros ',
	'note3'		    => 'identificadores de este comentario de Spam y lo añades a tu lista negra personal.</li><li>Después ',
	'note4'		    => 'vuelve aquí y haz que Spam-X compruebe los últimos comentarios de spam.</li></ul><p>Los comentarios ',
	'note5'		    => 'se comprueban desde los más nuevos hasta los más antiguos -- La comprobación de más comentarios ',
	'note6'		    => 'requiere más tiempo para llevarse a cabo</p>',
	'numtocheck'    => 'Número de Comentarios a comprobar',
    'RDF'           => 'RDF url: ',
    'URL'           => 'URL a la lista de Spam-X: ',
    'access_denied' => 'Acceso Denegado',
    'access_denied_msg' => 'Sólo los Usuarios Raíz tiene Acceso a esta página.  Tu nombre de usuario y dirección IP han sido registrados.',
    'acmod'         => 'Módulos de Acción de Spam-X',
    'actmod'        => 'Módulos Activos',
    'add1'          => 'Se han Añadido ',
    'add2'          => ' datos desde ',
    'add3'          => "la lista negra de  .",
    'addcen'        => 'Añadir Lista de Palabras Censuradas',
    'addentry'      => 'Añadir dato',
    'admin'         => 'Plugin de Administración',
    'adminc'        => 'Comandos de Administración:',
    'all'           => 'Todo',
    'allow_url_fopen' => '<p>Lo sentimos, la configuración de tu servidor de web no permite la lectura de ficheros remotos (<code>allow_url_fopen</code> is off). Por favor, descarga la Lista Negra desde el siguiente URL y súbelo al directorio de "datos" de glFusion\'s, <tt>%s</tt>, antes de intentarlo de nuevo:',
    'auto_refresh_off' => 'Refresco Automático No',
    'auto_refresh_on' => 'Refresco Automático Sí',
    'availb'        => 'Listas Negras Disponibles',
    'avmod'         => 'Módulos Disponibles',
    'blacklist'     => 'Blacklist',
    'blacklist_prompt' => 'Enter words to trigger spam',
    'blacklist_success_delete' => 'Selected items successfully deleted',
    'blacklist_success_save' => 'Spam-X Filter Saved Successfully',
    'blocked'       => 'Bloqueado',
    'cancel'        => 'Cancelar',
    'cancel'        => 'Cancelar',
    'clearlog'      => 'Limpiar el fichero de Registros',
    'clicki'        => 'Clic para Importar la Lista Negra',
    'clickv'        => 'Clic para ver la Lista Negra',
    'comment'       => 'Comentario',
    'coninst'       => '<hr>Haz Clic sobre un Módulo Activo para borrarlo, haz clic sobre un módulo Disponible para añadirlo.<br>Los módulos se ejecutan en el orden en que han sido presentados.',
    'conmod'        => 'Configurar el uso del módulo de Spam-X',
    'content'       => 'Contenido',
    'content_type'  => 'Content Type',
    'delete'        => 'Borrar',
    'delete_confirm' => 'Do you really want to delete this item?',
    'delete_confirm_2' => 'Are you REALLY SURE you want to delete this item',
    'documentation' => 'Documentación del Plugin de Spam-X',
    'e1'            => 'Para Borrar el dato cliquéalo.',
    'e2'            => 'Para Añadir un dato, introdúcelo en la caja y pulsa sobre Añadir.  Los datos pueden presentarse con Expresiones Normales de Perl.',
    'e3'            => 'Para añadir las palabras desde la lista de palabras censuradas de glFusions CensorList Pulsa el Botón:',
    'edit_filter_entry' => 'Edit Filter',
    'edit_filters'  => 'Edit Filters',
    'email'         => 'Correo Electrónico',
    'emailmsg'      => "Un nuevo comentario de spam ha sido enviado en/desde \"%s\"\nUser UID:\"%s\"\n\nContent:\"%s\"",
    'emailsubject'  => 'Spam post at %s',
    'enabled'       => 'Deshabilite el plugin antes de desinstalar.',
    'entries'       => ' entradas.',
    'entriesadded'  => 'Entradas Añadidas',
    'entriesdeleted'=> 'Entradas Borradas',
    'exmod'         => 'Módulos de Examen de Spam-X',
    'filter'        => 'Filter',
    'filter_instruction' => 'Here you can define filters which will be applied to each registration and post on the site. If any of the checks return true, the registration / post will be blocked as spam',
    'filters'       => 'Filters',
    'forum_post'    => 'Forum Post',
    'foundspam'     => 'Encontrado comentario de Spam coincidente ',
    'foundspam2'    => ' enviado por el usuario ',
    'foundspam3'    => ' desde el IP ',
    'fsc'           => 'Encontrado un comentario de Spam coincidente',
    'fsc1'          => ' enviado por el usuario ',
    'fsc2'          => ' desde el IP ',
    'headerblack'   => 'Spam-X HTTP Header Blacklist',
    'headers'       => 'Request headers:',
    'history'       => 'Past 3 Months',
    'http_header'   => 'HTTP Header',
    'http_header_prompt' => 'Header',
    'impinst1a'     => 'Antes de usar la característica de bloqueo de Spam con el comentario de Spam-X para ver e importar Listas negras personales de otros ',
    'impinst1b'     => ' sitios, te pido que pulses sobre los siguientes dos botones. (Tienes que pulsar sobre el último.)',
    'impinst2'      => 'Este primero envía tu sitio web al sitio Gplugs/Spam-X para que pueda añadirse al listado principal de ',
    'impinst2a'     => 'sitios que comparten sus listas negras. (Nota: Si tienes varios sitios puede que te interese designar uno de ellos como el ',
    'impinst2b'     => 'principal y sólo  incluir su nombre. Esto te permitirá actualizar tus sitios con facilidad y mantener una lista más pequeña.) ',
    'impinst2c'     => 'Después de pulsar sobre el botón de Enviar, pulsa sobre [atrás]en tu navegador para volver aquí.',
    'impinst3'      => 'Se enviarán los siguientes valores: (puedes editarlos si están equivocados).',
    'import_failure'=> '<p><strong>Error:</strong> No se han encontrado datos.',
    'import_success'=> '<p>Se ha importado con éxito los datos de la Lista Negra de %d.',
    'initial_Pimport'=> '<p>Importar la Lista Negra Personal"',
    'initial_import' => 'Importar la lista Negra-MT inicial',
    'inst1'         => '<p>Si haces esto, los demás ',
    'inst2'         => 'podrán ver e importar tu lista negra personal y nosotros podemos crear una base de datos ',
    'inst3'         => 'distribuida más eficaz.</p><p>Si has registrado tu sitio web y decides que no deseas permanecer en la lista ',
    'inst4'         => 'envía un correo electrónico a <a href="mailto:spamx@pigstye.net">spamx@pigstye.net</a> para comunicármelo. ',
    'inst5'         => 'Todas las peticiones serán respetadas.',
    'install'       => 'Instalar',
    'install_failed' => 'Instalación Fallida -- Lee tu registro de errores para averiguar por qué.',
    'install_header' => 'Plugin de Instalar/Desinstalar ',
    'install_success' => 'Se ha instalado con éxito',
    'installdoc'    => 'Documento de Instalación.',
    'installed'     => 'Se ha instalado el Plugin',
    'instructions'  => 'Spam-X allows you to define words, URLs, and other items that can be used to block spam posts on your site.',
    'interactive_tester' => 'Interactive Tester',
    'invalid_email_or_ip'   => 'Invalid e-mail address or IP address has been blocked',
    'invalid_item_id' => 'Invalid ID',
    'ip_address'    => 'Dirección IP',
    'ip_blacklist'  => 'IP Blacklist',
    'ip_error'  => 'The entry does not appear to be a valid IP or IP range',
    'ip_prompt' => 'Enter IP to block',
    'ipblack' => 'Spam-X IP Blacklist',
    'ipofurl'   => 'IP of URL',
    'ipofurl_prompt' => 'Enter IP of links to block',
    'ipofurlblack' => 'Spam-X IP of URL Blacklist',
    'logcleared' => '- Fichero de Registros limpiado',
    'mblack' => 'Mi Lista Negra:',
    'new_entry' => 'New Entry',
    'new_filter_entry' => 'New Filter Entry',
    'no_bl_data_error' => 'No errors',
    'no_blocked' => 'No spam has been blocked by this module',
    'no_filter_data' => 'No filters have been defined',
    'ok' => 'OK',
    'pblack' => 'Lista Negra Personal de Spam-X',
    'plugin' => 'Plugin',
    'plugin_name' => 'Spam-X',
    'readme' => 'DETENTE! Antes de pulsar sobre instalar, por favor lee el ',
    'referrer'      => 'Referente',
    'response'      => 'Respuesta',
    'rlinks' => 'Enlaces Relacionados:',
    'rsscreated' => 'Se creó la fuente RSS',
    'scan_comments' => 'Scan Comments',
    'scan_trackbacks' => 'Scan Trackbacks',
    'secbut' => 'Este botón segundo crea una fuente rdf para que otras personas puedan importar tu lista.',
    'sitename' => 'Nombre del Sitio: ',
    'slvwhitelist' => 'SLV Whitelist',
    'spamdeleted' => 'Comentario de Spam borrado',
    'spamx_filters' => 'Spam-X Filters',
    'stats_deleted' => 'Posts deleted as spam',
    'stats_entries' => 'Entradas',
    'stats_header' => 'HTTP headers',
    'stats_headline' => 'Spam-X Statistics',
    'stats_ip' => 'Blocked IPs',
    'stats_ipofurl' => 'Blocked by IP of URL',
    'stats_mtblacklist' => 'MT-Blacklist',
    'stats_page_title' => 'Blacklist',
    'stats_pblacklist' => 'Personal Blacklist',
    'submit'        => 'Enviar',
    'submit' => 'Enviar',
    'subthis' => 'esta información a la base de datos de Spam-X Central Database',
    'type'  => 'Tipo',
    'uMTlist' => 'Actualizar la Lista Negra-MT',
    'uMTlist2' => ': Añadidas ',
    'uMTlist3' => ' entradas y borradas ',
    'uPlist' => 'Actualizar la Lista Negra Personal',
    'uninstall' => 'Desinstalar',
    'uninstall_msg' => 'Se ha desinstalado el Plugin con éxito',
    'uninstalled' => 'No se ha instalado el Plugin',
    'user_agent'    => 'Agente',
    'username'  => 'Usuario',
    'value' => 'Valor',
    'viewlog' => 'Ver el registro de Spam-X',
    'warning' => 'Aviso! El Plugin está aún habilitado',
);


/* Define Messages that are shown when Spam-X module action is taken */
$PLG_spamx_MESSAGE128 = 'Se ha detectado spam y se ha borrado el Comentario o el Mensaje.';
$PLG_spamx_MESSAGE8   = 'Se ha detectado spam. Se ha enviado un correo al administrador.';

// Messages for the plugin upgrade
$PLG_spamx_MESSAGE3001 = 'Actualización de extensión no suportada.';
$PLG_spamx_MESSAGE3002 = $LANG32[9];


// Localization of the Admin Configuration UI
$LANG_configsections['spamx'] = array(
    'label' => 'Spam-X',
    'title' => 'Configuración Spam-X'
);

$LANG_confignames['spamx'] = array(
    'action' => 'Acciones Spam-X',
    'notification_email' => '¿Notificación por E-mail?',
    'admin_override' => "No Filtrar Admin Posts",
    'logging' => 'Enable Logging',
    'timeout' => 'Timeout',
    'sfs_username_check' => 'Enable User name validation',
    'sfs_email_check' => 'Enable email validation',
    'sfs_ip_check' => 'Enable IP address validation',
    'sfs_username_confidence' => 'Minimum confidence level on Username match to trigger spam block',
    'sfs_email_confidence' => 'Minimum confidence level on Email match to trigger spam block',
    'sfs_ip_confidence' => 'Minimum confidence level on IP address match to trigger spam block',
    'slc_max_links' => 'Maximum Links allowed in post',
    'debug' => 'Debug Logging',
    'akismet_enabled' => 'Akismet Module Enabled',
    'akismet_api_key' => 'Akismet API Key (Required)',
    'fc_enable' => 'Enable Form Check',
    'sfs_enable' => 'Enable Stop Forum Spam',
    'slc_enable' => 'Enable Spam Link Counter',
    'action_delete' => 'Delete Identified Spam',
    'action_mail' => 'Mail Admin when Spam Caught',
);

$LANG_configsubgroups['spamx'] = array(
    'sg_main' => 'Principal'
);

$LANG_fs['spamx'] = array(
    'fs_main' => 'General',
    'fs_sfs'  => 'Foro',
    'fs_slc'  => 'Spam Link Counter',
    'fs_akismet' => 'Akismet',
    'fs_formcheck' => 'Form Check',
);

$LANG_configSelect['spamx'] = array(
    0 => array(1 => 'Sí', 0 => 'No'),
    1 => array(TRUE => 'No', FALSE => 'Sí')
);
?>
