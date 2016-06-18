<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | spanish_colombia_utf-8.php                                               |
// |                                                                          |
// | Spanish (Colombia) language file for the glFusion installation script    |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2008-2015 by the following authors:                        |
// | John J. Toro A.        john DOT toro AT newroute DOT net                 |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// |                                                                          |
// | Copyright (C) 2000-2008 by the following authors:                        |
// |                                                                          |
// | Authors: Tony Bibbs        - tony AT tonybibbs DOT com                   |
// |          Mark Limburg      - mlimburg AT users DOT sourceforge DOT net   |
// |          Jason Whittenburg - jwhitten AT securitygeeks DOT com           |
// |          Dirk Haun         - dirk AT haun-online DOT de                  |
// |          Randy Kolenko     - randy AT nextide DOT ca                     |
// |          Matt West         - matt AT mattdanger DOT net                  |
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

if (!defined ('GVERSION')) {
    die ('This file can not be used on its own.');
}

// +---------------------------------------------------------------------------+

$LANG_CHARSET = 'utf-8';

// +---------------------------------------------------------------------------+
// install.php

$LANG_INSTALL = array(
    'back_to_top' => 'Ir arriba',
    'calendar' => '¿Cargar la extensión de calendario?',
    'calendar_desc' => 'Un calendario en línea / sistema de eventos. Incluye un calendario para sitios anchos y calendario personal para los usuarios.',
    'connection_settings' => 'Configuraciones de conexión',
    'content_plugins' => 'Contenido & Extensiones',
    'copyright' => '<a href="http://www.glfusion.org" target="_blank">glFusion</a> es liberado como Software libre bajo la <a href="http://www.gnu.org/licenses/gpl-2.0.txt" target="_blank">Licencia GNU/GPL v2.0.</a>',
    'core_upgrade_error' => 'Hubo un error durante el desarrollo de la actualización del núcleo.',
    'correct_perms' => 'Por favor corrija los errores listados a continuación. Una vez que hayan sido corregidos, utilice el botón <b>Revisar</b> para validar el ambiente.',
    'current' => 'Actual',
    'database_exists' => 'La base de datos actualmente contiene tablas de glFusion. Por favor remueva las tablas glFusion antes de iniciar la nueva instalación.',
    'database_info' => 'Información de la base de datos',
    'db_hostname' => 'Nombre Host de la base de datos',
    'db_hostname_error' => 'El nombre Host de la base de datos no puede estar en blanco.',
    'db_name' => 'Nombre de la base de datos',
    'db_name_error' => 'El nombre de la base de datos no puede estar en blanco.',
    'db_pass' => 'Contraseña de la base de datos',
    'db_table_prefix' => 'Prefijo de las tablas de la base de datos',
    'db_type' => 'Tipo de base de datos',
    'db_type_error' => 'El tipo de base de datos debe de ser seleccionado',
    'db_user' => 'Nombre de usuario de la base de datos',
    'db_user_error' => 'El nombre de usuario de la base de datos.',
    'dbconfig_not_found' => 'No es posible localizar el archivo db-config.php o db-config.php.dist. Por favor asegúrese de que ha ingresado la ruta correcta a su directorio "private".',
    'dbconfig_not_writable' => 'El archivo db-config.php no tiene definido el permiso de escritura. Por favor asegúrese de que el servidor web tiene permiso de escribir este archivo.',
    'directory_permissions' => 'Permisos de directorio',
    'enabled' => 'Habilitado',
    'env_check' => 'Verificar servidor',
    'error' => 'Error',
    'file_permissions' => 'Permisos de archivos',
    'file_uploads' => 'Varias características de glFusion requieren la habilidad de cargar archivos, esto debe estar encendido.',
    'filemgmt' => '¿Cargar Extensión Administrador de archivos?',
    'filemgmt_desc' => 'Administrador de descarga de archivos. Un método sencillo de ofrecer descarga de archivos, organizado por categorías.',
    'filesystem_check' => 'Verificar sistema de archivos',
    'forum' => '¿Cargar Extensión Foro?',
    'forum_desc' => 'Un sistema de foro y comunidad en línea. Proporciona colaboración e interactividad de la comunidad.',
    'hosting_env' => 'Verificar Configuración del Servidor',
    'install' => 'Instalar',
    'install_heading' => 'Instalación de glFusion',
    'install_steps' => 'PASOS DE INSTALACIÓN',
    'language' => 'Idioma',
    'language_task' => 'Idioma & tarea',
    'libcustom_not_writable' => 'lib-custom.php no tiene permisos de escritura.',
    'links' => '¿Cargar la Extensión Enlaces?',
    'links_desc' => 'Un sistema de administración de enlaces. proporciona enlaces a otros sitios interesantes, organizado por categoría.',
    'load_sample_content' => '¿Cargar Contenido de ejemplo para el sitio?',
    'mediagallery' => '¿Cargar Extensión Galería multimedia?',
    'mediagallery_desc' => 'Un sistema de administración de multimedia. Puede ser utilizado como una simple galería de fotografías o un robusto sistema de administración de multimedia capaz de soportar audio, vídeo e imágenes.',
    'memory_limit' => 'Se recomienda tener al menos 48M de memoria habilitada en su sitio.',
    'missing_db_fields' => 'Por favor ingrese en todos los campos la información requerida de la base de datos.',
    'new_install' => 'Nueva instalación',
    'next' => 'Siguiente',
    'no_db' => 'Aparentemente la base de datos no existe.',
    'no_db_connect' => 'No es posible establecer conexión con la base de datos',
    'no_innodb_support' => 'Has seleccionado MySQL con InnoDB pero su base de datos no soporta índices InnoDB.',
    'no_migrate_glfusion' => 'No puedes migrar un sitio existente construido con glFusion. Por favor elija en su lugar la opción de Actualización.',
    'none' => 'Ninguno',
    'not_writable' => 'NO POSEE PERMISOS DE ESCRITURA',
    'notes' => 'Nota',
    'off' => 'Apagado',
    'ok' => 'OK',
    'on' => 'Encendido',
    'online_help_text' => 'Ayuda de instalación en línea <br /> en glFusion.org',
    'online_install_help' => 'Ayuda de instalación en línea',
    'open_basedir' => 'Si la restricción <strong>open_basedir</strong> está encendida, podría causar problemas de permisos durante la instalación. El verificador del sistema de archivos, revisará que no hayan inconvenientes.',
    'path_info' => 'Información de la ruta',
    'path_prompt' => 'Ruta del directorio <i>private/</i> ',
    'path_settings' => 'Configurar rutas',
    'perform_upgrade' => 'Realizar actualización',
    'php_req_version' => 'glFusion requiere PHP en su versión 5.2.0 o superior.',
    'php_settings' => 'Configuraciones de PHP',
    'php_version' => 'Versión de PHP',
    'php_warning' => 'Si alguno de los objetos listados a continuación, aparece marcado en color <span class="no">rojo</span>, significa que encontrarás problemas con tu sitio glFusion. Verifica con tu proveedor de Alojamiento acerca de cómo cambiar alguna de las configuraciones de PHP.',
    'plugin_install' => 'Instalación de extensiones',
    'plugin_upgrade_error' => 'Hubo un problema durante la actualización de %s extensiones, por favor revise el archivo de registro error.log para más detalles.<br />',
    'plugin_upgrade_error_desc' => 'Las siguientes extensiones no fueron actualizadas. por favor revise el archivo de registro error.log para más detalles.<br />',
    'polls' => '¿Cargar la extensión Encuesta?',
    'polls_desc' => 'Un sistema de encuestas en línea. proporciona encuestas para que los usuarios de tu sitio voten sobre varios temas.',
    'post_max_size' => 'glFusion te permite cargar extensiones, imágenes y archivos. Deberías permitir al menos 8M como tamaño máximo de subida.',
    'previous' => 'Regresar',
    'proceed' => 'Proceder',
    'recommended' => 'Recomendado',
    'register_globals' => 'Si PHP <strong>register_globals</strong> está encendido, puede crear inconvenientes de seguridad.',
    'safe_mode' => 'Si PHP <strong>safe_mode</strong> está encendido, algunas funcionalidades de glFusion pueden no funcionar correctamente. Especialmente la extensión: Galería Multimedia .',
    'samplecontent_desc' => 'Si está marcado, instalará contenidos de ejemplo como bloques, historias y páginas estáticas. <strong>Esto es recomendado para nuevos usuarios de glFusion.</strong>',
    'select_task' => 'Selecciona la tarea',
    'session_error' => 'Su sesión ha expirado. Por favor reinicie el proceso de instalación.',
    'setting' => 'Configuración',
    'site_admin_url' => 'URL de Administración del sitio',
    'site_admin_url_error' => 'La URL de Administración del sitio no puede estar en blanco.',
    'site_email' => 'Correo electrónico del sitio',
    'site_email_error' => 'El correo electrónico del sitio no puede estar en blanco.',
    'site_email_notvalid' => 'El correo electrónico del sitio no es una dirección electrónica válida.',
    'site_info' => 'Información del sitio',
    'site_name' => 'Nombre del sitio',
    'site_name_error' => 'El nombre del sitio no puede estar en blanco.',
    'site_noreply_email' => 'Correo electrónico para no réplica del sitio',
    'site_noreply_email_error' => 'El correo electrónico para no réplica del sitio no puede estar en blanco.',
    'site_noreply_notvalid' => 'El correo electrónico para no réplica del sitio no es una dirección electrónica válida.',
    'site_slogan' => 'Lema del sitio',
    'site_upgrade' => 'Actualizar un sitio glFusion existente ',
    'site_url' => 'URL del sitio',
    'site_url_error' => 'El URL del sitio no puede estar en blanco.',
    'siteconfig_exists' => 'Se ha encontrado un archivo siteconfig.php existente. Por favor borre ese archivo antes de iniciar la instalación.',
    'siteconfig_not_found' => 'No fue posible encontrar el archivo siteconfig.php, ¿Está seguro de que se trata de una actualización?',
    'siteconfig_not_writable' => 'El archivo siteconfig.php no tiene permisos de escritura, o el directorio en donde siteconfig.php está almacenado no tiene permisos de escritura. Por favor, corrija este inconveniente antes de proceder.',
    'sitedata_help' => 'Selecciona el tipo de base de datos que utilizarás. Generalmente <strong>MySQL</strong>. También selecciona si deseas utilizar el conjunto de caracteres <strong>UTF-8</strong> (<i>este debería ser seleccionado para sitios multilenguaje.</i>)<br /><br /><br />Ingresa el nombre del servidor de la base de datos. No debería ser el mismo servidor web, en caso de duda verifica con tu proveedor de alojamiento.<br /><br />Ingresa el nombre de tu base de datos. <strong>La base de datos debe de existir.</strong> si no conoces el nombre de la base de datos, contacta a tu proveedor de alojamiento.<br /><br />Ingresa el nombre de usuario para conectarse a la base de datos. Si no conoces el nombre del usuario de la base de datos, contacta a tu proveedor de alojamiento.<br /><br /><br />Ingresa la contraseña para conectarse a la base de datos. Si no conoces la contraseña de la base de datos, contacta a tu proveedor de alojamiento.<br /><br />Ingresa un prefijo a ser utilizado en la tabla para las tablas de la base de datos. Esto es de utilidad para separar múltiples sitios o sistemas en la misma base de datos.<br /><br />Ingresa el nombre de tu sitio. Será mostrado en la cabecera el sitio. Por ejemplo, glFusion o Mark Marbles. No te preocupes, luego podrás cambiarlo.<br /><br />Ingresa el lema de tu sitio. Será mostrado en la cabecera de tu sitio debajo del nombre del sitio. Por ejemplo, sinergia - estabilidad - estilo. No te preocupes, luego podrás cambiarlo.<br /><br />Ingresa la dirección de correo electrónico principal de tu sitio. Esta es la dirección de correo electrónico por defecto para la cuenta del Administrador. No te preocupes, luego podrás cambiarla.<br /><br />Ingresa la dirección de correo electrónico de no réplica de tu sitio. Será utilizada para enviar automáticamente  nuevos usuarios, contraseñas, restablecimiento, y otras notificaciones por correo electrónico. No te preocupes, luego podrás cambiarlo. <br /><br />Por favor confirma que ésta es la dirección web o URL utilizada para acceder a la página principal de tu sitio.<br /><br /><br />Por favor confirma que ésta es la dirección web o URL utilizada para acceder a la sección de administración de tu sitio.',
    'sitedata_missing' => 'Los siguientes problemas fueron encontrados con la información que ha ingresado del sitio:',
    'system_path' => 'Configuración de rutas',
    'unable_mkdir' => 'No fue posible crear el directorio',
    'unable_to_find_ver' => 'No fue posible determinar la versión de glFusion.',
    'upgrade_error' => 'Error de actualización',
    'upgrade_error_text' => 'Un error ocurrió mientras se estaba actualizando la instalación de glFusion.',
    'upgrade_steps' => 'PASOS DE ACTUALIZACIÓN',
    'upload_max_filesize' => 'glFusion le permite cargar extensiones, imágenes y archivos. Deberías permitir al menos 8M para la carga.',
    'use_utf8' => 'Utilizar UTF-8',
    'welcome_help' => 'Bienvenido al Asistente de instalación del CMS glFusion. Puedes instalar un nuevo sitio glFusion o actualizar un sitio glFusion existente.<br /><br />Por favor selecciona el idioma para el asistente y la tarea a realizar, luego, haz clic sobre el botón: <strong>Siguiente</strong>.',
    'wizard_version' => 'v'.GVERSION.' Asistente de instalación',
    'system_path_prompt' => 'Ingresa la ruta completa, ruta absoluta de tu servidor al directorio: <strong>private/</strong> de glFusion.<br /><br />Este directorio contiene el archivo: <strong>db-config.php.dist</strong> o <strong>db-config.php</strong>.<br /><br />Ejemplos: /home/www/glfusion/private o c:/www/glfusion/private.<br /><br /><strong>Sugerencia:</strong> La ruta absoluta de tu directorio: <strong>public_html/</strong> <i>(no <strong>private/</strong>)</i> parece ser:<br />%s<br /><br /><strong>Configuraciones avanzadas</strong> Te permite sobre escribir varias de las rutas por defecto. Generalmente no necesitas modificar o definir esas rutas. El sistema las determinará automáticamente.',
    'advanced_settings' => 'Configuraciones Avanzadas',
    'log_path' => 'Ruta de Registros',
    'lang_path' => 'Ruta de Idiomas',
    'backup_path' => 'Ruta de Respaldos',
    'data_path' => 'Ruta de la información',
    'language_support' => 'Soporte de Idioma',
    'language_pack' => 'glFusion viene en idioma inglés, pero una vez instalado puedes descargar e instalar un <a href="http://www.glfusion.org/filemgmt/viewcat.php?cid=18" target="_blank">Paquete de idioma</a> de los múltiples idiomas soportados.',
    'libcustom_not_found' => 'No se pudo localizar lib-custom.php.dist.',
    'no_db_driver' => 'Debes tener cargada la extensión MySQL en PHP apra instalar glFusion',
    'version_check' => 'Verificar Actualizaciones',
    'check_for_updates' => 'Goto the Command & Control -Upgrade Check to see if there are any glFusion CMS or Plugin updates available.',
    'quick_start' => 'glFusion Quick Start Guide',
    'quick_start_help' => 'Please review  the <a href="https://www.glfusion.org/wiki/glfusion:quickstart">glFusion CMS Quick Start Guide</a> and the full <a href="https://www.glfusion.org/wiki/">glFusion CMS Documentation</a> site for details on configurating your new glFusion site.',
    'upgrade' => 'Upgrade',
    'support_resources' => 'Support Resources',
    'plugins' => 'glFusion Plugins',
    'support_forums' => 'glFusion Support Forums'
);

// +---------------------------------------------------------------------------+
// success.php

$LANG_SUCCESS = array(
    0 => 'Instalación completada',
    1 => 'Instalación de glFusion ',
    2 => ' ¡Completado!',
    3 => 'Felicidades, usted ha logrado exitosamente ',
    4 => ' glFusion. Por favor, toma un minuto para leer la información mostrada a continuación.',
    5 => 'Para ingresar en su nuevo sitio glFusion, por favor utilice esta cuenta:',
    6 => 'Nombre de usuario:',
    7 => 'Admin',
    8 => 'Contraseña:',
    9 => 'password',
    10 => 'Advertencia de seguridad',
    11 => 'No olvide',
    12 => 'cosas',
    13 => 'Remover o renombrar el directorio de instalación,',
    14 => 'Cambie la',
    15 => 'contraseña de la cuenta.',
    16 => 'Definir permisos',
    17 => 'y',
    18 => 'retroceder',
    19 => '<strong>Nota:</strong> Debido a que el modelo de seguridad ha cambiado, hemos creado una nueva cuenta con los privilegios necesarios para que usted administre su nuevo sitio. El nombre de usuario para esta nueva cuenta es <b>NuevoAdministrador/b> y la contraseña es <b>contraseña</b>',
    20 => 'instalado',
    21 => 'actualizado'
);

?>