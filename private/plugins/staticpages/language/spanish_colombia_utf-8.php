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
*  Based on prior work Copyright (C) 2001 by the following authors:
*  Tony Bibbs       tony AT tonybibbs DOT com
*
*/

if (!defined ('GVERSION')) {
    die ('This file can not be used on its own.');
}


global $LANG32;

###############################################################################
# Array Format:
# $LANGXX[YY]:  $LANG - variable name
#               XX    - file id number
#               YY    - phrase id number
###############################################################################

$LANG_STATIC = array(
    'newpage' => 'Nueva Página',
    'adminhome' => 'Administración',
    'staticpages' => 'Páginas',
    'staticpageeditor' => 'Editor',
    'writtenby' => 'Autor',
    'date' => 'Modificada',
    'title' => 'Título',
    'content' => 'Contenido',
    'hits' => 'Accesos',
    'staticpagelist' => 'Lista de Páginas',
    'url' => 'URL',
    'edit' => 'Editar',
    'lastupdated' => 'Última Edición',
    'pageformat' => 'Formato',
    'leftrightblocks' => 'Bloques a la Derecha e Izquierda',
    'blankpage' => 'Página en blanco',
    'noblocks' => 'Sin Bloques',
    'leftblocks' => 'Bloques a la Izquierda',
    'rightblocks' => 'Bloques a la Derecha',
    'addtomenu' => 'Añadir al menú',
    'label' => 'Etiqueta',
    'nopages' => 'Todavía no hay páginas estáticas',
    'save' => 'guardar',
    'preview' => 'vista previa',
    'delete' => 'eliminar',
    'cancel' => 'cancelar',
    'access_denied' => 'Acceso Denegado',
    'access_denied_msg' => 'Estás intentando acceder a una página de administración de Páginas Estáticas. Ten en cuenta que cualquier acceso a esta página se registra',
    'all_html_allowed' => 'Se permite cualquier etiqueta HTML',
    'results' => 'Resultado de Páginas Estáticas',
    'author' => 'Autor',
    'no_title_or_content' => 'Debes rellenar al menos los campos <b>Título</b> y <b>Contenido</b>.',
    'no_such_page_anon' => 'Por favor, regístrate..',
    'no_page_access_msg' => "Esto puede ser porque no te has registrado o no eres un miembro de {$_CONF['site_name']}. Por favor, <a href=\"{$_CONF['site_url']}/users.php?mode=new\">regístrate</a> en {$_CONF['site_name']} para obtener acceso completo",
    'php_msg' => 'PHP: ',
    'php_warn' => '<b>Aviso</b>: Si activas esta opción se interpretará el código PHP en tu página. ¡¡Usar con cuidado!!',
    'exit_msg' => 'Tipo de salida: ',
    'exit_info' => 'Activar para Mensaje de Acceso Preciso. No marcar para verificaciones de seguridad  y mensaje normales.',
    'deny_msg' => 'Acceso denegado a esta página. O bien ha sido movida/renombrada o no tienes permiso suficiente.',
    'stats_headline' => '10 páginas estáticas principales',
    'stats_page_title' => 'Título de la página',
    'stats_hits' => 'Accesos',
    'stats_no_hits' => 'Parece que no hay páginas estáticas o que nadie las ha visto nunca.',
    'id' => 'ID',
    'duplicate_id' => 'La ID elegida ya está en uso. Por favor, elige otra.',
    'instructions' => 'Para modificar o borrar, haz clic en el icono: <img src="/layout//images/admin/edit.png" /> o <img src="/layout//images/admin/delete.png" /> correspondiente. Para ver la pagina haz clic sobre el <b>Título</b>. Para crear, haz clic sobre el enlace: "<b>Crear</b>" arriba. Para crear una copia de una pagina existente, haz clic en el icono: <img src="/layout//images/admin/copy.png" /> correspondiente.',
    'centerblock' => 'Bloque Central',
    'centerblock_msg' => 'Cuando se seleccionas esta opción la página estática aparecerá como un bloque central en la página de inicio.',
    'topic' => 'Sección: ',
    'position' => 'Posición: ',
    'all_topics' => 'Todas',
    'no_topic' => 'Solo página de inicio',
    'position_top' => 'Arriba de la página',
    'position_feat' => 'Tras la noticia destacada',
    'position_bottom' => 'Abajo de la página',
    'position_entire' => 'Toda la página',
    'position_nonews' => 'Únicamente si no hay otras Noticias',
    'head_centerblock' => 'Bloque Central',
    'centerblock_no' => 'No',
    'centerblock_top' => 'Arriba',
    'centerblock_feat' => 'Noticia destacada',
    'centerblock_bottom' => 'Abajo',
    'centerblock_entire' => 'Toda la página',
    'centerblock_nonews' => 'Si no hay Noticias',
    'inblock_msg' => 'En un bloque: ',
    'inblock_info' => 'Ubicar Página en un bloque.',
    'title_edit' => 'Modificar pagina',
    'title_copy' => 'Hacer una copia de esta pagina',
    'title_display' => 'Mostrar pagina',
    'select_php_none' => 'no ejecutar PHP',
    'select_php_return' => 'ejecutar PHP (volver)',
    'select_php_free' => 'ejecutar PHP',
    'php_not_activated' => "Es uso de PHP en páginas estáticas no está activado. Por favor, véase la <a href=\"{$_CONF['site_url']}/docs/staticpages.html#php\">documentación</a> para más información.",
    'printable_format' => 'Listo para imprimir',
    'copy' => 'Copiar',
    'limit_results' => 'Limitar Resultados',
    'search' => 'Incluir en Búsquedas',
    'submit' => 'Enviar',
    'delete_confirm' => 'Esta seguro que desea borrar esta Página?',
    'allnhp_topics' => 'Todas las Secciones (Menos en la página de inicio)',
    'page_list' => 'Paginas',
    'instructions_edit' => 'Esta pantalla te permite crear/modificar/borrar una pagina. Las paginas pueden contener tanto código PHP como HTML.',
    'attributes' => 'Atributos',
    'preview_help' => 'Selecciona el botón <b>Visualizar</b> para refrescar la pantalla',
    'page_saved' => 'La Pagina ha sido correctametne guardada.',
    'page_deleted' => 'Page has been successfully deleted.',
    'searchable' => 'Buscar',
);

$LANG_SP_AUTOTAG = array(
    'desc_staticpage'           => 'Enlace: to a staticpage on this site; link_text defaults to staticpage title. usage: [staticpage:<i>page_id</i> {link_text}]',
    'desc_staticpage_content'   => 'HTML: renders the content of a staticpage.  usage: [staticpage_content:<i>page_id</i>]',
);

$PLG_staticpages_MESSAGE19 = '';
$PLG_staticpages_MESSAGE20 = '';

// Messages for the plugin upgrade
$PLG_staticpages_MESSAGE3001 = 'Actualización de la extensión no soportada.';
$PLG_staticpages_MESSAGE3002 = $LANG32[9];

// Localization of the Admin Configuration UI
$LANG_configsections['staticpages'] = array(
    'label' => 'Páginas',
    'title' => 'Configuración Páginas'
);

$LANG_confignames['staticpages'] = array(
    'allow_php' => 'Permitir código PHP',
    'sort_by' => 'Ordenar Bloques centrales por',
    'sort_menu_by' => 'Ordenar Menús por',
    'delete_pages' => 'Borrar Páginas con Propietario',
    'in_block' => 'Ubicar Pagina en un Bloque',
    'show_hits' => 'Mostrar Accesos',
    'show_date' => 'Mostrar Fecha',
    'filter_html' => 'Filtrar HTML',
    'censor' => 'Censurar Contenido',
    'default_permissions' => 'Permisos',
    'aftersave' => 'Después de guardar',
    'atom_max_items' => 'Max. Pages in Webservices Feed',
    'comment_code' => 'Comentarios',
    'include_search' => 'Incluir en Búsquedas',
    'status_flag' => 'Modo predeterminado',
);

$LANG_configsubgroups['staticpages'] = array(
    'sg_main' => 'Principal'
);

$LANG_fs['staticpages'] = array(
    'fs_main' => 'General',
    'fs_permissions' => 'Permisos'
);

// Note: entries 0, 1, 9, and 12 are the same as in $LANG_configselects['Core']
$LANG_configSelect['staticpages'] = array(
    0 => array(1=>'Si', 0=>'No'),
    1 => array(true=>'Si', false=>'No'),
    2 => array('date'=>'Fecha', 'id'=>'Page ID', 'title'=>'Título'),
    3 => array('date'=>'Fecha', 'id'=>'Page ID', 'title'=>'Título', 'label'=>'Etiqueta'),
    9 => array('item'=>'Forward to Page', 'list'=>'Ir a la lista', 'plugin'=>'Ir a la Lista Publica', 'home'=>'Ir al Inicio', 'admin'=>'Ir a Panel de C.'),
    12 => array(0=>'Sin acceso', 2=>'Sólo-Lectura', 3=>'Lectura-Escritura'),
    13 => array(1=>'Habilitado', 0=>'Deshabilitado'),
    17 => array(0=>'Comentarios Habilitados', 1=>'Comentarios Deshabilitados'),
);

?>
