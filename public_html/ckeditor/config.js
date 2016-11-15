/**
 * @license Copyright (c) 2003-2014, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.html or http://ckeditor.com/license
 */
var tbitems = {
	items: {
		'clipboard'     : ['Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord'],
		'undo'          : ['Undo', 'Redo'],
		'editing'       : ['Find', 'Replace', '-',  'Scayt'],
		'links'         : ['Link', 'Unlink'],
		'basicstyles'   : ['Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', '-', 'RemoveFormat'],
		'insert'        : ['Image', 'Mediagallery', 'Youtube2', 'Vimeo','Table', 'HorizontalRule', 'SpecialChar'],
		'paragraph'     : ['NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote', 'CreateDiv', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight'],
		'styles'        : ['Styles', 'Format', 'Font', 'FontSize'],
		'colors'        : ['TextColor', 'BGColor'],
		'tools'         : ['Maximize', 'ShowBlocks'],
		'source'        : ['Source'],
		'about'         : ['About'],
		'basicstyles_basic': ['Bold', 'Italic'],
		'paragraph_basic': ['NumberedList', 'BulletedList'],
		'insert_advanced': ['Image', 'Flash', 'Table', 'HorizontalRule', 'Smiley', 'SpecialChar']
	},
};
CKEDITOR.editorConfig = function( config ) {
	config.toolbar = 'standard';
	config.toolbar_basic = [
		tbitems.items['source'],
		tbitems.items['undo'],
		tbitems.items['basicstyles_basic'],
		tbitems.items['links'],
		tbitems.items['paragraph_basic'],
	];
	config.toolbar_standard = [
		tbitems.items['source'],
		tbitems.items['clipboard'],
		tbitems.items['undo'],
		tbitems.items['editing'],
		tbitems.items['links'],
		tbitems.items['insert'],
		tbitems.items['basicstyles'],
		tbitems.items['paragraph'],
		tbitems.items['styles'],
		tbitems.items['colors'],
		tbitems.items['tools']
	];
	// Set the most common block elements.
	config.format_tags = 'p;h1;h2;h3;pre';
	config.extraPlugins = 'youtube2,vimeo';
	config.autoParagraph = false;
	config.entities_latin = false;

    // FileMan
	config.filebrowserBrowseUrl =  site_url + '/ckeditor/plugins/fileman/index.html?type=file';
	config.filebrowserImageBrowseUrl = site_url + '/ckeditor/plugins/fileman/index.html?type=image';
};
