<?php
// +--------------------------------------------------------------------------+
// | FileMgmt Plugin - glFusion CMS                                           |
// +--------------------------------------------------------------------------+
// | spanish_colombia_utf-8.php                                               |
// |                                                                          |
// | Spanish (Colombia) language file                                         |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2008-2015 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// |                                                                          |
// | Copyright (C) 2014 by NewRoute Inc.                                      |
// | Author:                                                                  |
// | John Toro            john.toro@newroute.net                              |
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

$LANG_FM00 = array (
    'access_denied'     => 'Acceso Denegado',
    'access_denied_msg' => 'Sólo los administradores tiene acceso a esta página.',
    'admin'             => 'Administración de Extensiones',
    'install_header'    => 'Instalar/Desinstalar Extensión',
    'installed'         => 'La extensión y el Bloque están instalados,<p><i>Disfrutala,<br><a href="MAILTO:blaine@portalparts.com">Blaine</a></i>',
    'uninstalled'       => 'La Extensión no esta instalada',
    'install_success'   => 'Instalación completa <p><b>Siguientes Pasos</b>:
        <ol><li>Usa el Filemgmt Admin para completar la configuración</ol>
        <p>Revisa las <a href="%s">notas de instalación</a>para mas información.',
    'install_failed'    => 'Instalación fallida -- revisa el error.log .',
    'uninstall_msg'     => 'Extensión desinstalada correctamente',
    'install'           => 'Instalar',
    'uninstall'         => 'Desinstalar',
    'editor'            => 'Editor de la Extensión',
    'warning'           => 'Advertencia de desinstalación',
    'enabled'           => '<p style="padding: 15px 0px 5px 25px;">La Extensión está instalada y habilitada.<br>deshabilitala primero si quieres desinstalarla.</p><div style="padding:5px 0px 5px 25px;"><a href="'.$_CONF['site_admin_url'].'/plugins.php">Editor de la Extensión </a></div',
    'WhatsNewLabel'    => 'Nuevas Descargas',
    'WhatsNewPeriod'   => 'últimos %s días',
    'new_upload'        => 'Archivo enviado el ',
    'new_upload_body'   => 'Un archivo ha sido enviado a la cola de carga el ',
    'details'           => 'Detalles',
    'filename'          => 'Documento',
    'uploaded_by'       => 'Subido Por',
);

// Admin Navbar
$LANG_FM02 = array(
    'instructions' => 'Para modificar o borrar un archivo, haz clic sobre el icono: <img src="/layout/'.$_CONF['theme'].'/images/admin/edit.png" /> o <img src="/layout/'.$_CONF['theme'].'/images/admin/delete.png" /> correspondiente. Para ver o modificar categorías, selecciona la opción: <b>Categorías</b> arriba.',
    'nav1'  => 'Opciones',
    'nav2'  => 'Categorías',
    'nav3'  => 'Cargar Archivo',
    'nav4'  => 'Para Aprobar (%s)',
    'nav5'  => 'Archivos rotos (%s)',
    'edit'  => 'Modificar',
    'file'  => 'Archivo',
    'category' => 'Categoría',
    'version' => 'Versión',
    'size'  => 'Tamaño',
    'date' => 'Fecha',
);

$LANG_FILEMGMT = array(
    'newpage' => "Nueva página",
    'adminhome' => "Admin Home",
    'plugin_name' => "Descargas",
    'searchlabel' => "Lista de archivos",
    'searchlabel_results' => "Resultados de la lista de archivos",
    'downloads' => "Descargas",
    'report' => "Top descargas",
    'usermenu1' => "Descargas",
    'usermenu2' => "&nbsp;&nbsp;Mejor votadas",
    'usermenu3' => "Cargar Archivos",
    'admin_menu' => "Descargas",
    'writtenby' => "Escrito por",
    'date' => "Última actualización",
    'title' => "Título",
    'content' => "Contenido",
    'hits' => "Accesos",
    'Filelisting' => "Lista de archivos",
    'DownloadReport' => "Historial de descargas para archivos simples",
    'StatsMsg1' => "Archivos más accesados",
    'StatsMsg2' => "Parece que no hay archivos definidos en la Extensión filemgmt o nadie ha accesado nunca a ella.",
    'usealtheader' => "Use Alt. Header",
    'url' => "URL",
    'edit' => "Modificar",
    'lastupdated' => "Última actualización",
    'pageformat' => "Formato de página",
    'leftrightblocks' => "Bloque derecho e izquierdo",
    'blankpage' => "Página en blanco",
    'noblocks' => "Sin bloques",
    'leftblocks' => "Bloque izquierdo",
    'addtomenu' => 'Agregar al Menú',
    'label' => 'Etiqueta',
    'nofiles' => 'Número de archivos en el depósito (Descargas)',
    'save' => 'Guardar',
    'preview' => 'Vista previa',
    'delete' => 'Borrar',
    'cancel' => 'Cancelar',
    'access_denied' => 'Acceso Denegado',
    'invalid_install' => 'Alguien ha intentado accesar ilegalmente a la página de instalación del Administrador de Archivos. Id del usuario: ',
    'start_install' => 'Intentando instalar la Extensión Filemgmt ',
    'start_dbcreate' => 'Intentando crear una base de datos para la Extensión File mgmt',
    'install_skip' => '... saltado por filemgmt.cfg',
    'access_denied_msg' => 'Estás tratando de accesar ilegalmente a la página del administrador de archivos.  Todos los intentos ilegales de accesar a esta página están siendo registrados',
    'installation_complete' => 'Instalación Finalizada',
    'installation_complete_msg' => 'Las estructuras de datos para la Extensión File Mgmt  para glFusion han sido instaladas satisfactoriamente!  si alguna vez necesitas desinstalar esta extensión , por favor lee el archivo readme.',
    'installation_failed' => 'Instalación Fallida',
    'installation_failed_msg' => 'La instalación de la extensión File Mgmt ha fallado. Por favor revisa el: error.log de glFusion ',
    'system_locked' => 'Sistema cerrado',
    'system_locked_msg' => 'La instalación de la extensión File Mgmt esta realizada y cerrada. Si estas intentando desinstalar la extensión, por favor lee el archivo Readme que viene con ella',
    'uninstall_complete' => 'Desinstalación Completa',
    'uninstall_complete_msg' => 'Las estructuras de datos para la extensión File Mgmt han sido removidas de la base de datos<br><br>Necesitas borrar los archivos del servidor manualmente.',
    'uninstall_failed' => 'Desinstalación fallida.',
    'uninstall_failed_msg' => 'La Desinstalación ha fallado.  Por favor revisa tu glFusion error.log ',
    'install_noop' => 'Instalar Extensión',
    'install_noop_msg' => 'La instalación de la extensión filemgmt fue ejecutada pero no había nada que hacer.<br><br>Revisa el archivo: install.cfg de la extensión.',
    'all_html_allowed' => 'Todo HTML es permitido',
    'no_new_files'  => 'No hay archivos nuevos',
    'no_comments'   => 'No hay comentarios nuevos',
    'more'          => '<em>más ...</em>'
);

$LANG_FILEMGMT_AUTOTAG = array(
    'desc_file'                 => 'Link: to a File download detail page.  link_text defaults to the file title. usage: [file:<i>file_id</i> {link_text}]',
    'desc_file_download'        => 'Link: to a direct File download.  link_text defaults to the file title. usage: [file_download:<i>file_id</i> {link_text}]',
);


// Localization of the Admin Configuration UI
$LANG_configsections['filemgmt'] = array(
    'label'                 => 'FileMgmt',
    'title'                 => 'FileMgmt Configuration'
);
$LANG_confignames['filemgmt'] = array(
    'whatsnew'              => 'Enable WhatsNew Listing?',
    'perpage'               => 'Displayed Downloads per Page',
    'popular_download'      => 'Hits to be Popular',
    'newdownloads'          => 'Number of Downloads as New on Top Page',
    'trimdesc'              => 'Trim File Descriptions in Listing',
    'dlreport'              => 'Restrict access to Download report',
    'selectpriv'            => 'Restrict access to group \'Logged-In Users\' only',
    'uploadselect'          => 'Allow Logged-In uploads',
    'uploadpublic'          => 'Allow Anonymous uploads',
    'useshots'              => 'Display Category Images',
    'shotwidth'             => 'Thumbnail Img Width',
    'Emailoption'           => 'Email submitter if file approved',
    'FileStore'             => 'Directory to store files',
    'SnapStore'             => 'Directory to store file thumbnails',
    'SnapCat'               => 'Directory to store category thumbnails',
    'FileStoreURL'          => 'URL to files',
    'FileSnapURL'           => 'URL to file thumbnails',
    'SnapCatURL'            => 'URL to category thumbnails',
    'whatsnewperioddays'    => 'What\'s New Days',
    'whatsnewtitlelength'   => 'What\'s New Title Length',
    'showwhatsnewcomments'  => 'Show Comment in What\'s New Block?',
    'numcategoriesperrow'   => 'Categories per row',
    'numsubcategories2show' => 'Sub Categories per row',
    'outside_webroot'       => 'Store Files Outside Web Root',
    'enable_rating'         => 'Habilitar Calificaciones',
    'displayblocks'         => 'Display glFusion Blocks',
    'silent_edit_default'   => 'Silent Edit Default',
);
$LANG_configsubgroups['filemgmt'] = array(
    'sg_main'               => 'Main Settings'
);
$LANG_fs['filemgmt'] = array(
    'fs_public'             => 'Public FileMgmt Settings',
    'fs_admin'              => 'Admin Settings',
    'fs_permissions'        => 'Permisos',
    'fm_access'             => 'Control de Accesos',
    'fm_general'            => 'General',
);
// Note: entries 0, 1 are the same as in $LANG_configselects['Core']
$LANG_configselects['filemgmt'] = array(
    0 => array('True' => 1, 'False' => 0),
    1 => array('True' => TRUE, 'False' => FALSE),
    2 => array(' 5' => 5, '10' => 10, '15' => 15, '20' => 20, '25' => 25,'30' => 30,'50' => 50),
    3 => array('Left Blocks' => 0, 'Right Blocks' => 1, 'Left & Right Blocks' => 2, 'None' => 3)
);



$PLG_filemgmt_MESSAGE1 = 'Instalación de la extensión Filemgmt abortada<br>El archivo: plugins/filemgmt/filemgmt.php no se puede escribir';
$PLG_filemgmt_MESSAGE3 = 'Esta extensión requiere la versión 1.0 de glFusion o una mas nueva, actualización abortada.';
$PLG_filemgmt_MESSAGE4 = 'Código de la versión 1.5 de la Extensión no detectado - actualización abortada.';
$PLG_filemgmt_MESSAGE5 = 'Actualización de la extensión Filemgmt abortada<br>la versión actual no es la 1.3';


// Language variables used by the plugin - general users access code.

define("_MD_THANKSFORINFO","Gracias por la información. Daremos un vistazo a tu petición lo más rápido posible.");
define("_MD_BACKTOTOP","Volver al top de descargas");
define("_MD_THANKSFORHELP","Gracias por ayudar a mantener la integridad de las descargas.");
define("_MD_FORSECURITY","Por razones de seguridad tu nombre de usuario y tu dirección IP serán grabadas.");

define("_MD_SEARCHFOR","Buscar");
define("_MD_MATCH","Exacta");
define("_MD_ALL","Todo");
define("_MD_ANY","Cualquiera");
define("_MD_NAME","Nombre");
define("_MD_DESCRIPTION","Descripción");
define("_MD_SEARCH","Buscar");

define("_MD_MAIN","Principal");
define("_MD_SUBMITFILE","Enviar archivo");
define("_MD_POPULAR","Popular");
define("_MD_NEW","Nuevo");
define("_MD_TOPRATED","Top rankeado");

define("_MD_NEWTHISWEEK","Nuevo esta semana");
define("_MD_UPTHISWEEK","Actualizado esta semana");

define("_MD_POPULARITYLTOM","Popularidad (De menos a más Accesos)");
define("_MD_POPULARITYMTOL","Popularidad (De más a menos Accesos)");
define("_MD_TITLEATOZ","Título (A hasta la Z)");
define("_MD_TITLEZTOA","Título (Z hasta la A)");
define("_MD_DATEOLD","Fecha (Archivos antiguos mostrados de primero)");
define("_MD_DATENEW","Fecha (Archivos nuevos mostrados de primero)");
define("_MD_RATINGLTOH","Calificación (Desde la más baja hasta la más alta)");
define("_MD_RATINGHTOL","Calificación (Desde la más alta hasta la más baja)");

define("_MD_NOSHOTS","No hay vistas en miniatura disponibles");
define("_MD_EDITTHISDL","Modificar esta descarga");

define("_MD_LISTINGHEADING","<b>Listado de archivos: hay %s archivos en la base de datos</b>");
define("_MD_LATESTLISTING","<b>Último listado:</b>");
define("_MD_DESCRIPTIONC","Descripción:");
define("_MD_EMAILC","Email: ");
define("_MD_CATEGORYC","Categoría: ");
define("_MD_LASTUPDATEC","Última actualización: ");
define("_MD_DLNOW","Descargar!");
define("_MD_VERSION","Versión");
define("_MD_SUBMITDATE","Fecha");
define("_MD_DLTIMES","Descargado %s veces");
define("_MD_FILESIZE","Tamaño");
define("_MD_SUPPORTEDPLAT","Plataformas soportadas");
define("_MD_HOMEPAGE","Página de inicio");
define("_MD_HITSC","Hits: ");
define("_MD_RATINGC","Calificación: ");
define("_MD_ONEVOTE","1 voto");
define("_MD_NUMVOTES","(%s)");
define("_MD_NOPOST","N/A");
define("_MD_NUMPOSTS","%s votos");
define("_MD_COMMENTSC","Comentarios: ");
define ("_MD_ENTERCOMMENT", "Crear primer comentario");
define("_MD_RATETHISFILE","Vota por esta descarga");
define("_MD_MODIFY","Modificar");
define("_MD_REPORTBROKEN","Reportar archivo dañado");
define("_MD_TELLAFRIEND","Decir a un amigo");
define("_MD_VSCOMMENTS","Ver/Enviar Comentarios");
define("_MD_EDIT","Modificar");

define("_MD_THEREARE","Existen %s archivos en la base de datos");
define("_MD_LATESTLIST","Últimos listados");

define("_MD_REQUESTMOD","Pedir la modificación de una descarga");
define("_MD_FILE","Archivo");
define("_MD_FILEID","ID: ");
define("_MD_FILETITLE","Título: ");
define("_MD_DLURL","Dirección URL del archivo: ");
define("_MD_HOMEPAGEC","Página de inicio: ");
define("_MD_VERSIONC","Versión: ");
define("_MD_FILESIZEC","Tamaño: ");
define("_MD_NUMBYTES","%s bytes");
define("_MD_PLATFORMC","Plataforma: ");
define("_MD_CONTACTEMAIL","Email de Contacto : ");
define("_MD_SHOTIMAGE","Vista miniatura: ");
define("_MD_SENDREQUEST","Enviar Petición");

define("_MD_VOTEAPPRE","Su voto es valorado por nosotros,  gracias por votar.");
define("_MD_THANKYOU","Gracias por tomarse el tiempo de votar en  %s");// %s is your site name
define("_MD_VOTEFROMYOU","Las votaciones suministradas por usuarios como usted ayudarán a futuros usuarios a decidir la descarga apropiada.");
define("_MD_VOTEONCE","Por favor no vote por la misma descarga más de una vez.");
define("_MD_RATINGSCALE","La escala es de 1 - 10, siendo 1 mala y 10 excelente.");
define("_MD_BEOBJECTIVE","Por favor sea objetivo, si todos reciben 1 o 10, las calificaciones no serán muy útiles.");
define("_MD_DONOTVOTE","No votes por tu propio recurso.");
define("_MD_RATEIT","Votar");

define("_MD_INTFILEAT","Descargas interesantes en  %s");// %s is your site name
define("_MD_INTFILEFOUND","Aqui hay una descarga interesante que he encontrado en  %s");// %s is your site name

define("_MD_RECEIVED","Hemos recibido la información de tu descarga. gracias!");
define("_MD_WHENAPPROVED","Usted recibirá un E-mail cuando sea aprobado.");
define("_MD_SUBMITONCE","Envía tu archivo solo una vez.");
define("_MD_APPROVED", "Tu archivo ha sido aprobado");
define("_MD_ALLPENDING","Toda la información de los archivos serán publicados después de su aprobación.");
define("_MD_DONTABUSE","El nombre de usuario e IP son grabados, por favor no abuse del sistema.");
define("_MD_TAKEDAYS","Tomara varios días para que su archivo sean agregados al sistema.");
define("_MD_FILEAPPROVED", "Su archivo ha sido agregado a las descargas");

define("_MD_RANK","Posición");
define("_MD_CATEGORY","Categoría");
define("_MD_HITS","Hits");
define("_MD_RATING","Calificación");
define("_MD_VOTE","Voto");

define("_MD_SEARCHRESULT4","Resultados de la búsqueda para <b>%s</b>:");
define("_MD_MATCHESFOUND","%s resultado(s) encontrado(s).");
define("_MD_SORTBY","Ordenar por:");
define("_MD_TITLE","Título");
define("_MD_DATE","Fecha");
define("_MD_POPULARITY","Popularidad");
define("_MD_CURSORTBY","Archivos ordenados por: ");
define("_MD_FOUNDIN","Encontrado en:");
define("_MD_PREVIOUS","Anterior");
define("_MD_NEXT","Siguiente");
define("_MD_NOMATCH","No hay coincidencias encontradas para");

define("_MD_TOP10","Los 10 mas de %s ");// %s is a downloads category name
define("_MD_CATEGORIES","Categorías");

define("_MD_SUBMIT","Enviar");
define("_MD_CANCEL","Cancelar");

define("_MD_BYTES"," Bytes");
define("_MD_ALREADYREPORTED","Usted ya ha enviado un reporte para este link dañado.");
define("_MD_MUSTREGFIRST","Disculpa, no tienes los permisos para realizar esta acción.<br>Por favor registrate o ingresa al sistema primero!");
define("_MD_NORATING","Sin selección de calificación.");
define("_MD_CANTVOTEOWN","Usted no puede votar en su propio archivo.<br>Todos los votos son grabados y revisados.");

// Language variables used by the plugin - Admin code.

define("_MD_RATEFILETITLE","Grabar su calificación del archivo");
define("_MD_ADMINTITLE","Descargas");
define("_MD_UPLOADTITLE","File Management - Agregar un archivo nuevo");
define("_MD_CATEGORYTITLE","Lista de Archivos - Ver categoría");
define("_MD_DLCONF","Configuración de las descargas");
define("_MD_GENERALSET","Opciones de Configuración");
define("_MD_ADDMODFILENAME","Agregar nuevo archivo");
define ("_MD_ADDCATEGORYSNAP", "Imagen opcional: <small>(<i>Sólo Categorías de alto nivel</i>)</small>");
define ("_MD_ADDIMAGENOTE", "(<i>Alto de la imagen será modificada a 50 px)</i>");
define("_MD_ADDMODCATEGORY","<b>Categorías:</b> agregar, Modificar, y borrar Categorías");
define("_MD_DLSWAITING","Descargas esperando por validación");
define("_MD_BROKENREPORTS","Reporte de  archivos dañados");
define("_MD_MODREQUESTS","Descargar las peticiones de modificaciones");
define("_MD_EMAILOPTION","Envío de e-mail si el archivo es aprobado: ");
define("_MD_COMMENTOPTION","Permitir comentarios:");
define("_MD_SUBMITTER","Enviado por: ");
define("_MD_DOWNLOAD","Descargar");
define("_MD_FILELINK","Dirección del archivo");
define("_MD_SUBMITTEDBY","Enviado por: ");
define("_MD_APPROVE","Aprobar");
define("_MD_DELETE","Borrar");
define("_MD_NOSUBMITTED","No hay archivos enviados.");
define("_MD_ADDMAIN","Agregar Categoría Principal");
define("_MD_TITLEC","Título: ");
define("_MD_CATSEC", "Pueden ver la categoría: ");
define("_MD_UPLOADSEC", "Pueden subir archivos: ");
define("_MD_IMGURL","<br>Tamaño de la imagen <font size='-2'> (Localizado en el directorio filemgmt_data/category_snaps- Alto de la imagen será modificada a 50 px)</font>");
define("_MD_ADD","Agregar");
define("_MD_ADDSUB","Agregar SUB-Categoría");
define("_MD_IN","Divisón de");
define("_MD_ADDNEWFILE","Cargar archivo");
define("_MD_MODCAT","Modificar Categoría");
define("_MD_MODDL","Modificar información de la descarga");
define("_MD_USER","Usuario");
define("_MD_IP","Dirección IP");
define("_MD_USERAVG","Promedio de calificación del usuario");
define("_MD_TOTALRATE","Calificaciones totales");
define("_MD_NOREGVOTES","Votos de usuario no registrados");
define("_MD_NOUNREGVOTES","Votos de usuario registrados");
define("_MD_VOTEDELETED","Data de votos eliminada.");
define("_MD_NOBROKEN","No hay archivos dañados reportados.");
define("_MD_IGNOREDESC","Ignorar (Ignora el reporte y solo elimina este reporte enviado</b>)");
define("_MD_DELETEDESC","Delete (Elimina <b>El archivo de reporte enviado en el depósito</b> pero no el archivo actual)");
define("_MD_REPORTER","Reporte enviado por");
define("_MD_FILESUBMITTER","Archivo enviado por");
define("_MD_IGNORE","Ignorar");
define("_MD_FILEDELETED","Archivo eliminado.");
define("_MD_FILENOTDELETED","El registro fue eliminado pero el archivo no.<p>Más de un registro relacionado a un archivo.");
define("_MD_BROKENDELETED","Reporte de archivo roto eliminado.");
define("_MD_USERMODREQ","Petición de modificación de archivo");
define("_MD_ORIGINAL","Original");
define("_MD_PROPOSED","Propuesto");
define("_MD_OWNER","Propietario: ");
define("_MD_NOMODREQ","Sin petición de modificación de la descarga.");
define("_MD_DBUPDATED","Actualización de la base de datos completada!");
define("_MD_MODREQDELETED","Petición de modificación eliminada.");
define("_MD_IMGURLMAIN","Imagen (Alto de la imagen será modificada a 50 px): ");
define("_MD_PARENT","Categoría relacionada:");
define("_MD_SAVE","Guardar cambios");
define("_MD_CATDELETED","Categoría eliminada.");
define("_MD_WARNING","Advertencia: ¿Está seguro que desea borrar esta categoría con todos sus archivos y comentarios?");
define("_MD_YES","Si");
define("_MD_NO","No");
define("_MD_NEWCATADDED","Nueva categoría agregada satisfactoriamente!");
define("_MD_CONFIGUPDATED","Nueva configuración guardada");
define("_MD_ERROREXIST","ERROR: la información de descarga que usted envió ya se encuentra en la base de datos!");
define("_MD_ERRORNOFILE","ERROR: Archivo no encontrado en el registro de la base de datos!");
define("_MD_ERRORTITLE","ERROR: Necesitas escribir el título!");
define("_MD_ERRORDESC","ERROR: Necesitas escribir la descripción!");
define("_MD_NEWDLADDED","Nueva descarga agregada a la base de datos.");
define("_MD_NEWDLADDED_DUPFILE","Advertencia: Archivo duplicado. Nueva descarga agregada a la base de datos.");
define("_MD_NEWDLADDED_DUPSNAP","Advertencia: Snap Duplicado. Nueva descarga agregada a la base de datos.");
define("_MD_HELLO","Hola %s");
define("_MD_WEAPPROVED","Hemos aprobado tu descarga enviada. El nombre del archivo es: ");
define("_MD_THANKSSUBMIT","Gracias por tu envío!");
define("_MD_UPLOADAPPROVED","Tu envío ha sido aprobado!");
define("_MD_DLSPERPAGE","Descargas mostradas por página: ");
define("_MD_HITSPOP","Accesos para ser popular: ");
define("_MD_DLSNEW","Número de descargas como Nuevo en la página de los Más: ");
define("_MD_DLSSEARCH","Número de descargas en los resultados de la búsqueda: ");
define("_MD_TRIMDESC","Cortar descripción de los archivos en la lista: ");
define("_MD_DLREPORT","Restringir acceso al reporte de descargas");
define("_MD_WHATSNEWDESC","Habilitar la lista de Que hay nuevo");
define("_MD_SELECTPRIV","Restringir acceso a grupo solo 'Usuarios registrados': ");
define("_MD_ACCESSPRIV","Habilitar acceso a anónimos: ");
define("_MD_UPLOADSELECT","Permitir subir archivos a usuarios registrados:");
define("_MD_UPLOADPUBLIC","Permitir subir archivos a anónimos: ");
define("_MD_USESHOTS","Mostrar las imágenes de las Categorías: ");
define("_MD_IMGWIDTH","Ancho de la imagen de miniatura: ");
define("_MD_MUSTBEVALID","Vista en miniatura de la imagen debe estar bajo el directorio %s (ej shot.gif). Dejalo en blanco si no hay archivo de imagen.");
define("_MD_REGUSERVOTES","Votos de usuarios registrados (total: %s)");
define("_MD_ANONUSERVOTES","Votos de usuarios anónimos (total: %s)");
define("_MD_YOURFILEAT","Tu archivo enviado a %s");// this is an approved mail subject. %s is your site name
define("_MD_VISITAT","Visita nuestra sección de descarga en  %s");
define("_MD_DLRATINGS","Calificación de descargas (<i>total de votos: %s</i>)");
define("_MD_CONFUPDATED","Configuración actualizada satisfactoriamente!");
define("_MD_NOFILES","No hay archivos encontrados");
define("_MD_APPROVEREQ","* La carga necesita ser aprobada en esta categoría");
define("_MD_REQUIRED","* Campo requerido");
define("_MD_SILENTEDIT","Silent Edit: ");

// Additional glFusion Defines
define("_MD_NOVOTE","No ha sido votado todavía");
define("_IFNOTRELOAD","Si la página no carga automáticamente, haz clic <a href=%s>aquí</a>");
define("_GL_ERRORNOACCESS","ERROR: Sin acceso al depósito de esta sección");
define("_GL_ERRORNOUPLOAD","ERROR: No tienes privilegios para subir archivos");
define("_GL_ERRORNOADMIN","ERROR: Esta función está restringida");
define("_GL_NOUSERACCESS","Sin acceso al deposito de documentos");
define("_MD_ERRUPLOAD","Filemgmt: No puede subir archivos - Chequea los permisos para los directorios de archivado");
define("_MD_DLFILENAME","Archivo: ");
define("_MD_REPLFILENAME","Archivo de reemplazo: ");
define("_MD_SCREENSHOT","Foto");
define("_MD_SCREENSHOT_NA",'&nbsp;');
define("_MD_COMMENTSWANTED","Sus comentarios son valorados");
define("_MD_CLICK2SEE","Clic para ver: ");
define("_MD_CLICK2DL","Clic para descargar: ");
define("_MD_ORDERBY","Ordenar por: ");
?>
