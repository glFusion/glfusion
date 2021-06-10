<?php
/**
* glFusion CMS
*
* UTF-8 Language File for glFusion CKEditor Plugin
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2014-2018 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*/

if (!defined ('GVERSION')) {
    die ('This file cannot be used on its own.');
}

$LANG_CK00 = array (
    'menulabel'         => 'CKEditor',
    'plugin'            => 'ckeditor',
    'access_denied'     => 'Erişim engellendi',
    'access_denied_msg' => 'Bu sayfaya erişmek için uygun güvenlik ayrıcalığına sahip değilsiniz.  Kullanıcı adınız ve IP\'niz kaydedildi.',
    'admin'             => 'CKEditor Yönetimi',
    'install_header'    => 'CKEditor Eklentisi Yükleme / Kaldırma',
    'installed'         => 'CKEditor Kuruldu',
    'uninstalled'       => 'CKEditor Yüklü Değil',
    'install_success'   => 'CKEditor Kurulumu Başarılı.  <br /> <br /> Ayarlarınızın barındırma ortamıyla doğru şekilde eşleştiğinden emin olmak için lütfen sistem belgelerini inceleyin ve ayrıca <a href="%s"> yönetim bölümünü </a> ziyaret edin.',
    'install_failed'    => 'Kurulum Başarısız - Nedenini öğrenmek için hata günlüğünüze bakın.',
    'uninstall_msg'     => 'Eklenti Başarıyla Kaldırıldı',
    'install'           => 'Kur',
    'uninstall'         => 'Kaldır',
    'warning'           => 'Uyarı! Eklenti hala Etkin',
    'enabled'           => 'Kaldırmadan önce eklentiyi devre dışı bırakın.',
    'readme'            => 'CKEditor Eklenti Kurulumu',
    'installdoc'        => "<a href=\"{$_CONF['site_admin_url']}/plugins/ckeditor/install_doc.html\"> Belgeyi Kurun </a>",
    'overview'          => 'CKEditor, WYSIWYG düzenleyici yetenekleri sağlayan yerel bir glFusion eklentisidir.',
    'details'           => 'CKEditor eklentisi, sitenize wysiwyg düzenleyici özellikleri sağlayacaktır.',
    'preinstall_check'  => 'CKEditor aşağıdaki gereksinimlere sahiptir:',
    'glfusion_check'    => 'glFusion v1.3.0 veya üstü, bildirilen sürüm <b>%s </b>.',
    'php_check'         => 'PHP v5.2.0 veya üstü, bildirilen sürüm <b>%s </b>.',
    'preinstall_confirm'=> "CKEditor kurulumuyla ilgili tüm ayrıntılar için lütfen <a href=\"{$_CONF['site_admin_url']}/plugins/ckeditor/install_doc.html\"> Kurulum Kılavuzuna </a> bakın.",
    'visual'            => 'Görsel',
    'html'              => 'HTML',
);

// Localization of the Admin Configuration UI
$LANG_configsections['ckeditor'] = array(
    'label'                 => 'CKEditor',
    'title'                 => 'CKEditor Seçenekleri'
);
$LANG_confignames['ckeditor'] = array(
    'enable_comment'        => 'Yorumları Etkinleştir',
    'enable_story'          => 'Yazıları Etkinleştir',
    'enable_submitstory'    => 'Kullanıcı yazı önerilerini etkinleştir',
    'enable_contact'        => 'İletişimi Etkinleştir',
    'enable_emailstory'     => 'E-posta Yazıları Etkinleştir',
    'enable_sp'             => 'Sayfa Düzenleme Desteğini Etkinleştir',
    'enable_block'          => 'Blok Düzenleyiciyi Etkinleştir',
    'filemanager_fileroot'  => 'Göreli Yol (public_html\'den) Dosyalara',
    'filemanager_per_user_dir' => 'Kullanıcı Başına Dizinler',
    'filemanager_browse_only'       => 'Yanlızca Tarayıcı Modu',
    'filemanager_default_view_mode' => 'Varsayılan Görüntü Modu',
    'filemanager_show_confirmation' => 'Onayı göster',
    'filemanager_search_box'        => 'Arama kutusunu göster',
    'filemanager_file_sorting'      => 'Dosya Sıralama',
    'filemanager_chars_only_latin'  => 'Sadece latin karakterlere izin ver',
    'filemanager_date_format'       => 'Tarih/saat formatı',
    'filemanager_show_thumbs'       => 'Küçük resimleri göster',
    'filemanager_generate_thumbnails' => 'Küçük resimler oluştur',
    'filemanager_upload_restrictions' => 'İzin verilen dosya uzantıları',
    'filemanager_upload_overwrite'  => 'Mevcut dosyaların üzerine yaz',
    'filemanager_upload_images_only' => 'Sadece Resim Dosyalarını Yükle',
    'filemanager_upload_file_size_limit' => 'Dosya yükleme limiti(MB)',
    'filemanager_unallowed_files'   => 'İzin verilmeyen dosyalar',
    'filemanager_unallowed_dirs'    => 'İzin verilmeyen dizinler',
    'filemanager_unallowed_files_regexp' => 'İzin verilmeyen dosyalar için normal ifade',
    'filemanager_unallowed_dirs_regexp' => 'İzin verilmeyen dizinler için normal ifade',
    'filemanager_images_ext'        => 'Görüntü dosyası uzantıları',
    'filemanager_show_video_player' => 'Video oynatıcıyı göster',
    'filemanager_videos_ext'        => 'Video dosyası uzantıları',
    'filemanager_videos_player_width' => 'Video oynatıcı genişliği (piksel)',
    'filemanager_videos_player_height' => 'Video oynatıcı yüksekliği (piksel)',
    'filemanager_show_audio_player' => 'Ses oynatıcıyı göster',
    'filemanager_audios_ext'        => 'Ses dosyası uzantıları',
    'filemanager_edit_enabled'      => 'Düzenleyici Etkin',
    'filemanager_edit_linenumbers'  => 'Satır Numaraları',
    'filemanager_edit_linewrapping' => 'Satır Kaydırma',
    'filemanager_edit_codehighlight' => 'Kod Vurgulama',
    'filemanager_edit_editext' => 'İzin Verilen Düzenleme Uzantıları',
    'filemanager_fileperm'     => 'Yeni dosyalar için izin',
    'filemanager_dirperm'       => 'Yeni dizinler için izin',

);
$LANG_configsubgroups['ckeditor'] = array(
    'sg_main'               => 'Yapılandırma Ayarları'
);
$LANG_fs['ckeditor'] = array(
    'ck_public'                 => 'CKEditor Seçenekleri',
    'ck_integration'            => 'CKEditor Entegrasyon',
    'fs_filemanager_general'    => 'Dosya Yöneticisi Genel Ayarlar',
    'fs_filemanager_upload'     => 'Dosya Yöneticisi Yükleme Ayarları',
    'fs_filemanager_images'     => 'Dosya Yöneticisi Resim Ayarları',
    'fs_filemanager_videos'     => 'Dosya Yöneticisi Video Ayarları',
    'fs_filemanager_audios'     => 'Dosya Yöneticisi Ses Ayarları',
    'fs_filemanager_editor'     => 'Dosya Yöneticisi Gömülü Düzenleyici',
);
// Note: entries 0, 1, and 12 are the same as in $LANG_configselects['Core']
$LANG_configSelect['ckeditor'] = array(
    0 => array(1=>'Doğru', 0=>'Yanlış'),
    1 => array(true=>'Doğru', false=>'Yanlış'),
    2 => array('grid'=>'ızgara', 'list' => 'liste'),
    3 => array('default' => 'varsayılan', 'NAME_ASC'=>'İsim (artan)', 'NAME_DESC'=>'İsim (azalan)', 'TYPE_ASC'=>'Tür(artan)', 'TYPE_DESC'=>'Tür(Azalan)', 'MODIFIED_ASC'=>'Değiştirilmesi Zamanı (artan)', 'MODIFIED_DESC'=>'Değiştirilme Zamanı (Azalan)'),
);

$PLG_ckeditor_MESSAGE1 = 'CKEditor eklenti yükseltmesi: Güncelleme başarıyla tamamlandı.';
$PLG_ckeditor_MESSAGE2 = 'CKEditor eklenti yükseltmesi başarısız - error.log dosyasını kontrol edin';
$PLG_ckeditor_MESSAGE3 = 'CKEditor Eklentisi Başarıyla Yüklendi';
?>