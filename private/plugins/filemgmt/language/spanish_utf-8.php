<?php
// +--------------------------------------------------------------------------+
// | FileMgmt Plugin - glFusion CMS                                           |
// +--------------------------------------------------------------------------+
// | spanish_utf-8.php                                                        |
// |                                                                          |
// | Spanish Language file                                                    |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2008-2011 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// |                                                                          |
// | Copyright (C) 2004 by Consult4Hire Inc.                                  |
// | Author:                                                                  |
// | Blaine Lang            blaine@portalparts.com                            |
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

###############################################################################

$LANG_FM00 = array(
    'access_denied' => 'Acceso Denegado',
    'access_denied_msg' => 'Solo usuarios administradores tiene acceso hacia est&aacute; p&aacute;gina. Su nombre de usuario y direcci&oacute;n IP han sido registrados en bit&aacute;cora.',
    'admin' => 'Administraci&oacute;n de Componente',
    'install_header' => 'Instalar/Desinstalar Componente',
    'installed' => 'El componente y recuadro est&aacute;n ahora instalados,<p><i>Disfruten,<br' . XHTML . '>Atentamente <a href="MAILTO:blaine@portalparts.com">Blaine</a></i>',
    'uninstalled' => 'El componente no est&aacute; instalado',
    'install_success' => "Instalaci&oacute;n exitosa<p><b>Siguientes pasos</b>:\n        <ol><li>Utilice el administrador del Gestor de Ficheros (Filemgmt) para completar la configuraci&oacute;n</ol>\n        <p>Revise las <a href=\"%s\">Notas de Instalaci&oacute;n</a> para m&aacute;s informaci&oacute;n.",
    'install_failed' => 'La instalaci&oacute;n fall&oacute; - Revise error.log para averiguar por qu&eacute;.',
    'uninstall_msg' => 'Componente desinstalado exitosamente',
    'install' => 'Instalar',
    'uninstall' => 'Desinstalar',
    'editor' => 'Editor de componente',
    'warning' => 'Advertencia de desinstalaci&oacute;n',
    'enabled' => "<p style=\"padding: 15px 0px 5px 25px;\">El componente est&aacute; instalado y habilitado.<br" . XHTML . ">Deshabilite primero si quiere desinstalar.</p><div style=\"padding:5px 0px 5px 25px;\"><a href=\"{$_CONF['site_admin_url']}/plugins.php\">Editor de Componente</a></div",
    'WhatsNewLabel' => 'Nuevos ficheros',
    'WhatsNewPeriod' => ' en %s d&iacute;as',
    'new_upload' => 'New File submitted at ',
    'new_upload_body' => 'A new file has been submitted to the upload queue at ',
    'details' => 'File Details',
    'filename' => 'Filename',
    'uploaded_by' => 'Uploaded By',
    'not_found'         => 'Download Not Found',
);

$LANG_FM02 = array(
    'instructions' => 'To modify or delete a file, click on the files\'s edit icon below. To view or modify categories, select the Categories option above.',
    'nav1' => 'Configuraci&oacute;n',
    'nav2' => 'Categor&iacute;as',
    'nav3' => 'A&ntilde;adir fichero',
    'nav4' => 'Descargas (%s)',
    'nav5' => 'Ficheros rotos (%s)',
    'edit'  => 'Edit',
    'file'  => 'Filename',
    'category' => 'Category Name',
    'version' => 'Version',
    'size'  => 'Size',
    'date' => 'Date',
);

$LANG_FILEMGMT = array(
    'newpage' => 'Nueva p&aacute;gina',
    'adminhome' => 'Inicio de Administraci&oacute;n',
    'plugin_name' => 'Administrador de ficheros',
    'searchlabel' => 'Lista de ficheros',
    'searchlabel_results' => 'Resultados del listado de ficheros',
    'downloads' => 'Descargas',
    'report' => 'Mejores descargas',
    'usermenu1' => 'Descargas',
    'usermenu2' => '&nbsp;&nbsp;Mejor calificados',
    'usermenu3' => 'A&ntilde;ade una descarga',
    'admin_menu' => 'Gestor de ficheros',
    'writtenby' => 'Escrito por',
    'date' => '&Uacute;ltima actualizaci&oacute;n',
    'title' => 'T&iacute;tulo',
    'content' => 'Contenido',
    'hits' => 'Accesos',
    'Filelisting' => 'Lista de ficheros',
    'DownloadReport' => 'Historial de descargas para un solo fichero',
    'StatsMsg1' => 'Lo m&aacute;s descargado del dep&oacute;sito',
    'StatsMsg2' => 'Al perecer este sitio carece de ficheros definidos para el Componente de Gestor de Ficheros o nadie los ha accedido todav&iacute;a.',
    'usealtheader' => 'Use Cabecera Alt.',
    'url' => 'URL',
    'edit' => 'Editar',
    'lastupdated' => 'Ultima actualizaci&oacute;n',
    'pageformat' => 'Formato de p&aacute;gina',
    'leftrightblocks' => 'Recuadros derecho e izquierdo',
    'blankpage' => 'P&aacute;gina en blanco',
    'noblocks' => 'Sin recuadros',
    'leftblocks' => 'Recuadros izquierdos',
    'addtomenu' => 'A&ntilde;adir al men&uacute;',
    'label' => 'Etiqueta',
    'nofiles' => 'N&uacute;mero de ficheros (descargas) en nuestro dep&oacute;sito',
    'save' => 'guardar',
    'preview' => 'vista previa',
    'delete' => 'eliminar',
    'cancel' => 'cancelar',
    'access_denied' => 'Acceso Denegado',
    'invalid_install' => 'Alguien intent&oacute; acceder hacia la p&aacute;gina de instalaci&oacute;n/desinstalaci&oacute;n del Componente de gestor de Ficheros (filemgmt). ID de usuario: ',
    'start_install' => 'Intentando instalar el Componente de Gestor de Ficheros (Filemgmt)',
    'start_dbcreate' => 'Intentando crear tablas para el Componente de Gestor de Ficheros (Filemgmt)',
    'install_skip' => '... omitido como se define en filemgmt.cfg',
    'access_denied_msg' => 'Est&aacute;s tratando de acceder sin autorizaci&oacute;n la p&aacute;gina de administraci&oacute;n del Componente de gestor de Ficheros. Por favor, tome nota que todos los intentos son registrados en bit&aacute;cora y son supervisados por nuestro personal.',
    'installation_complete' => 'Instalaci&oacute;n completada con &eacute;xito',
    'installation_complete_msg' => 'Las estructuras de datos del componente de Gestor de Ficheros (Filemgmt) han sido exitosamente instalados en la base de datos. Si alguna vez se requiere desinstalar este componente, por favor lea el fichero README que vino junto con este componente.',
    'installation_failed' => 'La instalaci&oacute;n fall&oacute;',
    'installation_failed_msg' => '>La instalaci&oacute;n del Componente de Gestor de Ficheros fall&oacute;. Por favor revisar error.log para averiguar por qu&eacute;.',
    'system_locked' => 'Sistema bloqueado',
    'system_locked_msg' => 'El Componente de Gestor de Fichero est&aacute; instalado y bloqueado. Si se quiere desinstalar este componente, por favor lea el fichero README que acompa&ntilde;a a &eacute;ste.',
    'uninstall_complete' => 'Desinstalaci&oacute;n completada',
    'uninstall_complete_msg' => 'Las estructuras de datos para el Componente de Gestor de Ficheros han sido eliminadas exitosamente de la base de datos de glFusion<br' . XHTML . '><br' . XHTML . '>Se requiere eliminar manualmente todos los ficheros en el dep&oacute;sito en el disco duro.',
    'uninstall_failed' => 'Fall&oacute; desinstalaci&oacute;n.',
    'uninstall_failed_msg' => 'La instalaci&oacute;n de el Componente de Gestor de Ficheros fall&oacute;. Revise el fichero error.log para averiguar por qu&eacute;',
    'install_noop' => 'Instalaci&oacute;n del componente',
    'install_noop_msg' => 'La instalaci&oacute;n del componente de gestor de Ficheros se llev&oacute; a cabo sin acci&oacute;n alguna.<br' . XHTML . '><br' . XHTML . '>Por favor revise el fichero install.cfg.',
    'all_html_allowed' => 'Todos el HTML est&aacute; permitido',
    'no_new_files' => 'Sin nuevos eventos',
    'no_comments' => 'Sin nuevos comentarios',
    'more' => '<em>m&aacute;s ...</em>'
);

$LANG_FILEMGMT_AUTOTAG = array(
    'desc_file'                 => 'Link: to a File download detail page.  link_text defaults to the file title. usage: [file:<i>file_id</i> {link_text}]',
    'desc_file_download'        => 'Link: to a direct File download.  link_text defaults to the file title. usage: [file_download:<i>file_id</i> {link_text}]',
);

$PLG_filemgmt_MESSAGE1 = 'El proceso de instalaci&oacute;n de Componente de Gestor de Ficheros (Filemgmt) fue interrumpido<br' . XHTML . '>Fichero: plugins/filemgmt/filemgmt.php sin atributos de escritura.';
$PLG_filemgmt_MESSAGE3 = 'Este componente requiere glFusion Versi&oacute;n 1.0 o m&aacute;s reciente, proceso de actualizaci&oacute;n interrumpido.';
$PLG_filemgmt_MESSAGE4 = 'Versi&oacute;n de c&oacute;digo del Componente es anterior a 1.5 - proceso de actualizaci&oacute;n interrumpido.';
$PLG_filemgmt_MESSAGE5 = 'El proceso de actualizaci&oacute;n de Componente de Gestor de Ficheros fue interrumpido.<br' . XHTML . '>La versi&oacute;n del componente es distinta a 1.3';

// Localization of the Admin Configuration UI
$LANG_configsections['filemgmt'] = array(
    'label' => 'FileMgmt',
    'title' => 'FileMgmt Configuration'
);

$LANG_confignames['filemgmt'] = array(
    'whatsnew' => 'Enable WhatsNew Listing?',
    'perpage' => 'Displayed Downloads per Page',
    'popular_download' => 'Hits to be Popular',
    'newdownloads' => 'Number of Downloads as New on Top Page',
    'trimdesc' => 'Trim File Descriptions in Listing',
    'dlreport' => 'Restrict access to Download report',
    'selectpriv' => 'Restrict access to group \'Logged-In Users\' only',
    'uploadselect' => 'Allow Logged-In uploads',
    'uploadpublic' => 'Allow Anonymous uploads',
    'useshots' => 'Display Category Images',
    'shotwidth' => 'Thumbnail Img Width',
    'Emailoption' => 'Email submitter if file approved',
    'FileStore' => 'Directory to store files',
    'SnapStore' => 'Directory to store file thumbnails',
    'SnapCat' => 'Directory to store category thumbnails',
    'FileStoreURL' => 'URL to files',
    'FileSnapURL' => 'URL to file thumbnails',
    'SnapCatURL' => 'URL to category thumbnails',
    'whatsnewperioddays' => 'What\'s New Days',
    'whatsnewtitlelength' => 'What\'s New Title Length',
    'showwhatsnewcomments' => 'Show Comment in What\'s New Block?',
    'numcategoriesperrow' => 'Categories per row',
    'numsubcategories2show' => 'Sub Categories per row',
    'outside_webroot' => 'Store Files Outside Web Root',
    'enable_rating'         => 'Enable Ratings',
    'displayblocks'         => 'Display glFusion Blocks',
    'silent_edit_default'   => 'Silent Edit Default',
);

$LANG_configsubgroups['filemgmt'] = array(
    'sg_main' => 'Main Settings'
);

$LANG_fs['filemgmt'] = array(
    'fs_public' => 'Public FileMgmt Settings',
    'fs_admin' => 'FileMgmt Admin Settings',
    'fs_permissions' => 'Default Permissions',
    'fm_access' => 'FileMgmt Access Control',
    'fm_general' => 'FileMgmt General Settings'
);

// Note: entries 0, 1, and 12 are the same as in $LANG_configselects['Core']
$LANG_configselects['filemgmt'] = array(
    0 => array('True' => 1, 'False' => 0),
    1 => array('True' => true, 'False' => false),
    2 => array(' 5' => 5, '10' => 10, '15' => 15, '20' => 20, '25' => 25, '30' => 30, '50' => 50),
    3 => array('Left Blocks' => 0, 'Right Blocks' => 1, 'Left & Right Blocks' => 2, 'None' => 3)
);

// Language variables used by the plugin - general users access code.

define("_MD_THANKSFORINFO","Gracias por la informaci&oacute;n. Revisaremos tu solicitud a la brevedad posible.");
define("_MD_BACKTOTOP","Regresar a la parte superior de la secci&oacute;n de descargas");
define("_MD_THANKSFORHELP","Gracias por ayudar a mantener la integridad de este directorio.");
define("_MD_FORSECURITY","Por motivos de seguridad tu nombre de usuario y direcci&oacute;n IP ser&aacute;n tambi&eacute;n registrados temporalmente.");

define("_MD_SEARCHFOR","B&uacute;squeda de");
define("_MD_MATCH","Coincidencia");
define("_MD_ALL","TODO");
define("_MD_ANY","CUALQUIERA");
define("_MD_NAME","Nombre");
define("_MD_DESCRIPTION","Descripci&oacute;n");
define("_MD_SEARCH","Buscar");

define("_MD_MAIN","Principal");
define("_MD_SUBMITFILE","Enviar fichero");
define("_MD_POPULAR","Popular");
define("_MD_NEW","Nuevo");
define("_MD_TOPRATED","Mejor calificados");

define("_MD_NEWTHISWEEK","Nuevo esta semana");
define("_MD_UPTHISWEEK","Actualizado esta semana");

define("_MD_POPULARITYLTOM","Popularidad (De menos a m&aacute;s accesos)");
define("_MD_POPULARITYMTOL","Popularidad (De m&aacute;s a menos accesos)");
define("_MD_TITLEATOZ","T&iacute;tulo (A a Z)");
define("_MD_TITLEZTOA","T&iacute;tulo (Z a A)");
define("_MD_DATEOLD","Fecha (ficheros viejos se listan primero)");
define("_MD_DATENEW","Fecha (ficheros nuevos se listan primero)");
define("_MD_RATINGLTOH","Calificaci&oacute;n (de la puntuaci&oacute;n m&aacute;s baja a la m&aacute;s alta)");
define("_MD_RATINGHTOL","Calificaci&oacute;n (de la puntuaci&oacute;n m&aacute;s alta a la m&aacute;s baja)");

define("_MD_NOSHOTS","Sin miniaturas disponibles");
define("_MD_EDITTHISDL","Editar esta descarga");

define("_MD_LISTINGHEADING","<b>Lista de ficheros: Hay %s ficheros en nuestra base de datos</b>");
define("_MD_LATESTLISTING","<b>&Uacute;ltima lista:</b>");
define("_MD_DESCRIPTIONC","Descripci&oacute;n:");
define("_MD_EMAILC","Correo electr&oacute;nico: ");
define("_MD_CATEGORYC","Categor&iacute;a: ");
define("_MD_LASTUPDATEC","&Uacute;ltima actualizaci&oacute;n: ");
define("_MD_DLNOW","¡Descarga ahora!");
define("_MD_VERSION","Ver");
define("_MD_SUBMITDATE","Fecha");
define("_MD_DLTIMES","Descargado %s veces");
define("_MD_FILESIZE","Tama&ntilde;o");
define("_MD_SUPPORTEDPLAT","Plataformas soportadas");
define("_MD_HOMEPAGE","P&aacute;gina de inicio");
define("_MD_HITSC","Accesos: ");
define("_MD_RATINGC","Calificaci&oacute;n: ");
define("_MD_ONEVOTE","1 voto");
define("_MD_NUMVOTES","(%s)");
define("_MD_NOPOST","N/D");
define("_MD_NUMPOSTS","%s votos");
define("_MD_COMMENTSC","Comentarios: ");
define ("_MD_ENTERCOMMENT", "Crear primer comentario");
define("_MD_RATETHISFILE","Califica esta descarga");
define("_MD_MODIFY","Modificar");
define("_MD_REPORTBROKEN","Reportar Fichero Estropeado");
define("_MD_TELLAFRIEND","Comparte con un amigo");
define("_MD_VSCOMMENTS","Ver/enviar comentarios");
define("_MD_EDIT","Editar");

define("_MD_THEREARE","Hay %s ficheros en nuestra base de datos.");
define("_MD_LATESTLIST","&Uacute;ltima lista");

define("_MD_REQUESTMOD","Solicitar modificaci&oacute;n de descarga.");
define("_MD_FILE","Fichero");
define("_MD_FILEID","ID fichero: ");
define("_MD_FILETITLE","T&iacute;tulo: ");
define("_MD_DLURL","URL de descarga: ");
define("_MD_HOMEPAGEC","P&aacute;gina de inicio: ");
define("_MD_VERSIONC","Versi&oacute;n: ");
define("_MD_FILESIZEC","Tama&ntilde;o ficheros: ");
define("_MD_NUMBYTES","%s bytes");
define("_MD_PLATFORMC","Plataforma: ");
define("_MD_CONTACTEMAIL","Correo electr&oacute;nico: ");
define("_MD_SHOTIMAGE","Imagen miniatura: ");
define("_MD_SENDREQUEST","Enviar solicitud");

define("_MD_VOTEAPPRE","Tu voto es apreciado.");
define("_MD_THANKYOU","Gracias por tomarte tu tiempo para votar aqu&iacute; en %s"); // %s is your site name
define("_MD_VOTEFROMYOU","Tu retroalimentaci&oacute;n ayudar&aacute; a otros visitantes a decidir que fichero descargar.");
define("_MD_VOTEONCE","Por favor evita votar m&aacute;s de una vez por el mismo recurso.");
define("_MD_RATINGSCALE","La escala va de 1 a 10, donde 1 significa pobre y 10 significa excelente.");
define("_MD_BEOBJECTIVE","Por favor, se objetivo. Si todos reciben un 1 o un 10, las calificaciones son de poca utilidad.");
define("_MD_DONOTVOTE","Evita votar por tu propio recurso.");
define("_MD_RATEIT","¡Calificar esta descarga!");

define("_MD_INTFILEAT","Interesante fichero de descarga en %s"); // %s is your site name
define("_MD_INTFILEFOUND","Hay una descarga interesante que encontr&eacute; en %s"); // %s is your site name

define("_MD_RECEIVED","Hemos recibido la informaci&oacute;n de la descarga. ¡Gracias!");
define("_MD_WHENAPPROVED","Recibir&aacute;s un mensaje de correo electr&oacute;nico cuando sea aprobado.");
define("_MD_SUBMITONCE","Env&iacute;a tu fichero/gui&oacute;n una sola vez.");
define("_MD_APPROVED", "Tu fichero ha sido aprobado");
define("_MD_ALLPENDING","Toda la informaci&oacute;n de ficheros/guiones son publicadas tras ser verificadas.");
define("_MD_DONTABUSE","El nombre de usuario y direcci&oacute;n IP son registrados en bit&aacute;cora, por favor evite abusar del sistema.");
define("_MD_TAKEDAYS","Pueden demorar varios d&iacute;as antes de que tu fichero/gui&oacute;n se agregado a nuestra base de datos.");

define("_MD_RANK","Calificado");
define("_MD_CATEGORY","Categor&iacute;a");
define("_MD_HITS","Accesos");
define("_MD_RATING","Calificaci&oacute;n");
define("_MD_VOTE","Votar");

define("_MD_SEARCHRESULT4","Resultados de b&uacute;squeda para <b>%s</b>:");
define("_MD_MATCHESFOUND","%s coincidencias(s) encontrada(s).");
define("_MD_SORTBY","Ordenado por:");
define("_MD_TITLE","T&iacute;tulo");
define("_MD_DATE","Fecha");
define("_MD_POPULARITY","Popularidad");
define("_MD_CURSORTBY","Ficheros actualmente ordenados por: ");
define("_MD_FOUNDIN","Encontrado en:");
define("_MD_PREVIOUS","Previo");
define("_MD_NEXT","Siguiente");
define("_MD_NOMATCH","Sin coincidencias para su consulta.");

define("_MD_TOP10","%s - Mejores 10 descargas"); // %s is a downloads category name
define("_MD_CATEGORIES","Categor&iacute;as");

define("_MD_SUBMIT","Enviar");
define("_MD_CANCEL","Cancelar");

define("_MD_BYTES","Bytes");
define("_MD_ALREADYREPORTED","Ya has enviado un reporte de Fichero Estropeado para este recurso.");
define("_MD_MUSTREGFIRST","Lo sentimos, careces de los permisos para realizar esta acci&oacute;n.<br>Por favor te sugerimos te registres en el sitio o ingreses con tu cuenta.");
define("_MD_NORATING","Sin calificaci&oacute;n seleccionada.");
define("_MD_CANTVOTEOWN","Evita votar por el recursos que tu mismo enviaste.<br>Todos los votos se registran en bit&aacute;cora y son revisados.");

// Language variables used by the plugin - Admin code.

define("_MD_RATEFILETITLE","Registro de calificaciones para fichero");
define("_MD_ADMINTITLE","Administraci&oacute;n de Gestor de Ficheros");
define("_MD_UPLOADTITLE","Administrador de ficheros - A&ntilde;adir nuevo fichero");
define("_MD_CATEGORYTITLE","Lista de ficheros - Vista por categor&iacute;a");
define("_MD_DLCONF","Configuraci&oacute;n de descargas");
define("_MD_GENERALSET","Opciones de Configuraci&oacute;n");
define("_MD_ADDMODFILENAME","Agregar nuevo fichero");
define ("_MD_ADDCATEGORYSNAP", "Imagen opcional: <small>(Solo categor&iacute;as de nivel superior)</small>");
define ("_MD_ADDIMAGENOTE", "(El tama&ntilde;o de la imagen ser&aacute; cambiado a 50 pixeles)");
define("_MD_ADDMODCATEGORY","<b>Categor&iacute;as:</b> Agrega, Modifica o elimina Categor&iacute;as");
define("_MD_DLSWAITING","Descargas esperando para ser validadas");
define("_MD_BROKENREPORTS","Reporte de ficheros rotos");
define("_MD_MODREQUESTS","Solicitudes de modificaci&oacute;n de informaci&oacute;n de descargas");
define("_MD_EMAILOPTION","Enviar mensaje de correo electr&oacute;nico al quien envi&oacute; descarga en caso de ser aprobada: ");
define("_MD_COMMENTOPTION","Habilitar comentarios:");
define("_MD_SUBMITTER","Enviado por: ");
define("_MD_DOWNLOAD","Descargar");
define("_MD_FILELINK","Enlace hacia fichero");
define("_MD_SUBMITTEDBY","Enviado por: ");
define("_MD_APPROVE","Aprobar");
define("_MD_DELETE","Eliminar");
define("_MD_NOSUBMITTED","Sin nuevas descargas enviadas.");
define("_MD_ADDMAIN","Agregar categor&iacute;a PRINCIPAL");
define("_MD_TITLEC","T&iacute;tulo: ");
define("_MD_CATSEC", "Acceso categor&iacute;a: ");
define("_MD_IMGURL","<br>Nombre de imagen <font size='-2'> (localizado en el directorio filemgmt_data/category_snaps directory - La altura de la imagen ser&aacute; cambiada a 50 pixeles)</font>");
define("_MD_ADD","A&ntilde;adir");
define("_MD_ADDSUB","A&ntilde;adir Categor&iacute;a secundaria");
define("_MD_IN","in");
define("_MD_ADDNEWFILE","A&ntilde;adir nuevo fichero");
define("_MD_MODCAT","Modificar categor&iacute;a");
define("_MD_MODDL","Modificar informaci&oacute;n de descarga");
define("_MD_USER","Usuario");
define("_MD_IP","Direcci&oacute;n IP");
define("_MD_USERAVG","Calificaci&oacute;n promedio por usuario");
define("_MD_TOTALRATE","Calificaciones totales");
define("_MD_NOREGVOTES","Sin votos de usuarios registrados");
define("_MD_NOUNREGVOTES","Sin votos de usuarios an&oacute;nimos");
define("_MD_VOTEDELETED","Datos de votaci&oacute;n eliminados.");
define("_MD_NOBROKEN","Sin reportes de ficheros rotos.");
define("_MD_IGNOREDESC","Ignorar (Ignora el reporte y solo elimina esta entrada</b>)");
define("_MD_DELETEDESC","Eliminar (Elimina <b>el registro de fichero reportado en el dep&oacute;sito</b> pero no el fichero en si)");
define("_MD_REPORTER","Reportar a quien env&iacute;a");
define("_MD_FILESUBMITTER","Usuario que envi&oacute; fichero");
define("_MD_IGNORE","Ignore");
define("_MD_FILEDELETED","Fichero eliminado.");
define("_MD_FILENOTDELETED","El registro fue eliminado pero el fichero persiste.<p>M&aacute;s de un registro apunta al mismo fichero.");
define("_MD_BROKENDELETED","Reporte de Fichero Estropeado eliminado.");
define("_MD_USERMODREQ","Solicitudes de modificaci&oacute;n de informaci&oacute;n de descargas por los usuarios");
define("_MD_ORIGINAL","Original");
define("_MD_PROPOSED","Propuesto");
define("_MD_OWNER","Propietario: ");
define("_MD_NOMODREQ","Sin solicitudes de modificaci&oacute;n de descargas.");
define("_MD_DBUPDATED","Base de datos actualizada exitosamente");
define("_MD_MODREQDELETED","Solicitud de modificaci&oacute;n eliminada.");
define("_MD_IMGURLMAIN","Imagen (la altura ser&aacute; modificada a 50 pixeles): ");
define("_MD_PARENT","Categor&iacute;a Padre:");
define("_MD_SAVE","Guardar cambios");
define("_MD_CATDELETED","Categor&iacute;a eliminada.");
define("_MD_WARNING","ADVERTENCIA: ¿Est&aacute;s seguro que quieres eliminar esta categor&iacute;a y TODOS sus ficheros y comentarios?");
define("_MD_YES","Si");
define("_MD_NO","No");
define("_MD_NEWCATADDED","¡Nueva categor&iacute;a agregada con &eacute;xito!");
define("_MD_CONFIGUPDATED","Nueva configuraci&oacute;n guardada");
define("_MD_ERROREXIST","ERROR: La informaci&oacute;n que proporcionaste acerca del fichero ya est&aacute; en la base de datos!");
define("_MD_ERRORNOFILE","ERROR: ¡Imposible encontrar registro del fichero en la base de datos!");
define("_MD_ERRORTITLE","ERROR: ¡Necesitas ingresar un T&Iacute;TULO!");
define("_MD_ERRORDESC","ERROR: ¡Necesitas ingresar DESCRIPCI&Oacute;N!");
define("_MD_NEWDLADDED","Nueva descarga a&ntilde;adida a la base de datos.");
define("_MD_NEWDLADDED_DUPFILE","ADVERTENCIA: Fichero duplicado. Nueva descarga agregada a la base de datos.");
define("_MD_NEWDLADDED_DUPSNAP","ADVERTENCIA: Imagen duplicada. Nueva descarga agregada a la base de datos.");
define("_MD_HELLO","Hola %s");
define("_MD_WEAPPROVED","Hemos aprobado tu env&iacute;o de descarga en nuestra secci&oacute;n de descargas. El nombre del fichero es: ");
define("_MD_THANKSSUBMIT","¡Gracias por tu env&iacute;o!");
define("_MD_UPLOADAPPROVED","Tu fichero subido ha sido aprobado");
define("_MD_DLSPERPAGE","Descargas mostradas por p&aacute;gina: ");
define("_MD_HITSPOP","Accesos para considerar popular: ");
define("_MD_DLSNEW","N&uacute;mero de descargas nuevas en la p&aacute;gina principal: ");
define("_MD_DLSSEARCH","N&uacute;mero de descargas en los resultados de b&uacute;squeda: ");
define("_MD_TRIMDESC","Recortar descripci&oacute;n en el listado: ");
define("_MD_DLREPORT","Restringir acceso hacia reporte de descargas");
define("_MD_WHATSNEWDESC","Habilitar listado de lo m&aacute;s nuevo");
define("_MD_SELECTPRIV","Restringir acceso solamente al grupo 'Logged-In Users': ");
define("_MD_ACCESSPRIV","Permitir acceso an&oacute;nimo: ");
define("_MD_UPLOADSELECT","Permitir subidas de usuarios registrados: ");
define("_MD_UPLOADPUBLIC","Permitir subidas an&oacute;nimas: ");
define("_MD_USESHOTS","Mostrar im&aacute;genes de categor&iacute;a: ");
define("_MD_IMGWIDTH","Anchura de img miniatura: ");
define("_MD_MUSTBEVALID","La imagen miniatura debe ser un fichero de imagen v&aacute;lido bajo el directorio %s directory (ejemplo: foto.gif). Deja en blanco el campo si se carece de fichero.");
define("_MD_REGUSERVOTES","Votos de usuarios registrados (total de votos: %s)");
define("_MD_ANONUSERVOTES","Votos de usuarios an&oacute;nimos (total de votos: %s)");
define("_MD_YOURFILEAT","Tu fichero enviado a %s"); // this is an approved mail subject. %s is your site name
define("_MD_VISITAT","Visita nuestra secci&oacute;n de descargas en %s");
define("_MD_DLRATINGS","Calificaci&oacute;n de descarga (total de votos: %s)");
define("_MD_CONFUPDATED","¡Configuraci&oacute;n actualizada con &eacute;xito!");
define("_MD_NOFILES","No se encontraron ficheros");
define("_MD_APPROVEREQ","* Upload needs to be approved in this category");
define("_MD_REQUIRED","* Required field");
define("_MD_SILENTEDIT","Silent Edit: ");

// Additional glFusion Defines
define("_MD_NOVOTE","Sin calificar a&uacute;n");
define("_IFNOTRELOAD","La p&aacute;gina recargar&aacute; autom&aacute;ticamente. Si quieres regresar ahora, has <a href=%s>clic aqu&iacute;</a>.");
define("_GL_ERRORNOACCESS","ERROR: Sin permisos de acceso a esta secci&oacute;n de dep&oacute;sito de documentos");
define("_GL_ERRORNOUPLOAD","ERROR: Sin privilegios para subir ficheros");
define("_GL_ERRORNOADMIN","ERROR: Esta funci&oacute;n est&aacute; restringida");
define("_GL_NOUSERACCESS","sin acceso al dep&oacute;sito de documentos");
define("_MD_ERRUPLOAD","Gestor de Ficheros: Imposibilitado para subir fichero - verifica los permisos para los directorios de almacenamiento de ficheros");
define("_MD_DLFILENAME","Fichero: ");
define("_MD_REPLFILENAME","Fichero de reemplazo: ");
define("_MD_SCREENSHOT","Captura de pantalla");
define("_MD_SCREENSHOT_NA",'&nbsp;');
define("_MD_COMMENTSWANTED","Comentarios son apreciados");
define("_MD_CLICK2SEE","Clic para ver: ");
define("_MD_CLICK2DL","Clic para descargar: ");
define("_MD_ORDERBY","Ordenar por: ");
?>