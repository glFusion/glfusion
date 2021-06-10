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
    'newpage' => 'Yeni Sayfa',
    'adminhome' => 'Yönetim Sayfasý',
    'staticpages' => 'Statik Sayfalar',
    'staticpageeditor' => 'Statik Sayfa Düzenleyicisi',
    'writtenby' => 'Yazan',
    'date' => 'Son Güncelleme',
    'title' => 'Baþlýk',
    'content' => 'Ýçerik',
    'hits' => 'Hit',
    'staticpagelist' => 'Statik Sayfa Listesi',
    'url' => 'Link',
    'edit' => 'Düzenle',
    'lastupdated' => 'Son Güncelleme',
    'pageformat' => 'Sayfa Formatý',
    'leftrightblocks' => 'Sol & Sað Bloklar',
    'blankpage' => 'Boþ Sayfa',
    'noblocks' => 'Bloklar Yok',
    'leftblocks' => 'Sol Bloklar',
    'rightblocks' => 'Sağ Bloklar',
    'addtomenu' => 'Menüye Ekle',
    'label' => 'Etiket',
    'nopages' => 'Henüz sistemde statik sayfalar yok',
    'save' => 'kaydet',
    'preview' => 'önizleme',
    'delete' => 'sil',
    'cancel' => 'vazgeç',
    'access_denied' => 'Giriþ Engellendi',
    'access_denied_msg' => 'Statik Sayfalar yönetim sayfalarýna yetkisiz giriþ demesi yapýyorsunuz. Not: Bu sayfalara yetkisiz giriþ denemelerinin hepsi kaydedilmektedir',
    'all_html_allowed' => 'Bütün HTML kodlarý kullanýlabilir',
    'results' => 'Statik Sayfalar Sonuçlarý',
    'author' => 'Yazar',
    'no_title_or_content' => 'En azýndan <b>Baþlýk</b> ve <b>Ýçerik</b> bölümlerini doldurmalýsýnýz.',
    'no_such_page_anon' => 'Lütfen giriþ yapýn..',
    'no_page_access_msg' => "Bu olabilir çünkü giriþ yapmadýnýz yada {$_CONF['site_name']} nin kayýtlý bir üyesi deðilsiniz. {$_CONF['site_name']} nin tüm üyelik giriþlerini elde etmek için lütfen <a href=\"{$_CONF['site_url']}/users.php?mode=new\"> yeni bir üye olun</a>",
    'php_msg' => 'PHP: ',
    'php_warn' => 'Uyarý: Þayet bu seçeneði kullanýrsanýz, sayfanýz PHP kodunda deðerlendirilir. Dikkatli kullanýn !!',
    'exit_msg' => 'Çýkýþ Tipi: ',
    'exit_info' => 'Giriþ Mesajý Ýstemeyi olanaklý kýlar. Normal güvenlik kontrolü ve mesajý için iþareti kaldýrýn.',
    'deny_msg' => 'Bu sayfaya giriþ engellendi. Bu sayfa taþýndý yada kaldýrýldý veya yeterli izniniz yok.',
    'stats_headline' => 'Top On Statik Sayfa',
    'stats_page_title' => 'Sayfa Baþlýðý',
    'stats_hits' => 'Hit',
    'stats_no_hits' => 'It appears that there are no static pages on this site or no one has ever viewed them.',
    'id' => 'Kimlik',
    'duplicate_id' => 'Bu statik sayfa için seçtiðiniz ID zaten kullanýlýyor. Lütfen baþka ID seçin.',
    'instructions' => 'Bir statik sayfayý düzenlemek yada silmek isterseniz, aþaðýdaki sayfa numarasýna týklayýnýz. Bir statik sayfayý görüntüleme, görmek istediðiniz sayfanýn baþlýðýna týklyýnýz. Yeni bir statik sayfa yaratmak için üstteki Yeni Sayfa linkine týklayýn. [C] \'ye týklayarak varolan sayfanýn bir kopyasýný yaratýrsýnýz.',
    'centerblock' => 'Ortablok: ',
    'centerblock_msg' => 'Ýþaretlenirse, bu statik sayfa index sayfasýnda bir orta blokda görüntülenecektir.',
    'topic' => 'Konu: ',
    'position' => 'Pozisyon: ',
    'all_topics' => 'Hepsi',
    'no_topic' => 'Sadece Ana Sayfa',
    'position_top' => 'Sayfanýn Üstü',
    'position_feat' => 'Günün Yazýsýndan Sonra',
    'position_bottom' => 'Sayfanýn Altý',
    'position_entire' => 'Tam Sayfa',
    'position_nonews' => 'Sadece Başka Haber Yoksa',
    'head_centerblock' => 'Ortablok',
    'centerblock_no' => 'Yok',
    'centerblock_top' => 'Üst',
    'centerblock_feat' => 'Gün. Yazýsý',
    'centerblock_bottom' => 'Alt',
    'centerblock_entire' => 'Tam Sayfa',
    'centerblock_nonews' => 'Haber Yoksa',
    'inblock_msg' => 'Bir blokta: ',
    'inblock_info' => 'Wrap Static Page in a block.',
    'title_edit' => 'Sayfayı düzenle',
    'title_copy' => 'Bu sayfanın bir kopyasını oluşturun',
    'title_display' => 'Sayfayı görüntüle',
    'select_php_none' => 'pHP\'yi çalıştırmayın',
    'select_php_return' => 'pHP\'yi çalıştır (dönüş)',
    'select_php_free' => 'pHP\'yi çalıştır',
    'php_not_activated' => "The use of PHP in static pages is not activated. Please see the <a href=\"{$_CONF['site_url']}/docs/staticpages.html#php\">documentation</a> for details.",
    'printable_format' => 'Yazdırılabilir Format',
    'copy' => 'Kopyala',
    'limit_results' => 'Sonuç Limiti',
    'search' => 'Search',
    'submit' => 'Gönder',
    'delete_confirm' => 'Bu sayfayı silmek istediğinize emin misiniz?',
    'allnhp_topics' => 'Tüm Konular (Ana Sayfa Yok)',
    'page_list' => 'Statik Sayfa Listesi',
    'instructions_edit' => 'Bu ekran, yeni bir statik sayfa oluşturmanıza / düzenlemenize olanak tanır. Sayfalar PHP kodu ve HTML kodu içerebilir.',
    'attributes' => 'Öznitelikler',
    'preview_help' => 'Önizleme görüntüsünü yenilemek için <b> Önizleme </b> düğmesini seçin',
    'page_saved' => 'Değişiklikler başarıyla kaydedildi.',
    'page_deleted' => 'Sayfa başarıyla silindi.',
    'searchable' => 'Ara',
);

$LANG_SP_AUTOTAG = array(
    'desc_staticpage'           => 'Bağlantı: bu sitedeki statik bir sayfaya; bağlantı_metni varsayılan olarak statik sayfa başlığına ayarlanır. kullanım: [staticpage: <i> page_id </i> {link_text}]',
    'desc_staticpage_content'   => 'HTML: bir statik sayfanın içeriğini oluşturur.  kullanım: [staticpage_content: <i> page_id </i>]',
);

$PLG_staticpages_MESSAGE19 = 'Boş mesaj';
$PLG_staticpages_MESSAGE20 = 'Boş mesaj20';

// Messages for the plugin upgrade
$PLG_staticpages_MESSAGE3001 = 'Eklenti yükseltmesi desteklenmiyor.';
$PLG_staticpages_MESSAGE3002 = $LANG32[9];

// Localization of the Admin Configuration UI
$LANG_configsections['staticpages'] = array(
    'label' => 'Static Pages',
    'title' => 'Static Pages Configuration'
);

$LANG_confignames['staticpages'] = array(
    'allow_php' => 'Allow PHP?',
    'sort_by' => 'Sort Centerblocks by',
    'sort_menu_by' => 'Sort Menu Entries by',
    'delete_pages' => 'Delete Pages with Owner?',
    'in_block' => 'Wrap Pages in Block?',
    'show_hits' => 'Show Hits?',
    'show_date' => 'Show Date?',
    'filter_html' => 'Filter HTML?',
    'censor' => 'Censor Content?',
    'default_permissions' => 'Varsayılan Sayfa İzinleri',
    'aftersave' => 'Sayfayı Kaydettikten Sonra',
    'atom_max_items' => 'Max. Pages in Webservices Feed',
    'comment_code' => 'Yorum Varsayılanı',
    'include_search' => 'Site Arama Varsayılanı',
    'status_flag' => 'Varsayılan Sayfa Modu',
);

$LANG_configsubgroups['staticpages'] = array(
    'sg_main' => 'Ana Ayarlar'
);

$LANG_fs['staticpages'] = array(
    'fs_main' => 'Static Pages Main Settings',
    'fs_permissions' => 'Varsayılan İzinler'
);

// Note: entries 0, 1, 9, and 12 are the same as in $LANG_configselects['Core']
$LANG_configSelect['staticpages'] = array(
    0 => array(1=>'Doğru', 0=>'Yanlış'),
    1 => array(true=>'Doğru', false=>'Yanlış'),
    2 => array('date'=>'Tarih', 'id'=>'Sayfa Kimliği', 'title'=>'Başlık'),
    3 => array('date'=>'Tarih', 'id'=>'Sayfa Kimliği', 'title'=>'Başlık', 'label'=>'Etiket'),
    9 => array('item'=>'Sayfaya Yönlendir', 'list'=>'Yönetici Listesini Görüntüle', 'plugin'=>'Genel Listeyi Görüntüle', 'home'=>'Ana Ekranı Görüntüle', 'admin'=>'Ekran Yöneticisi'),
    12 => array(0=>'Erişim yok', 2=>'Salt Okunur', 3=>'Okunur/Yazılır'),
    13 => array(1=>'Etkin', 0=>'Devre dışı'),
    17 => array(0=>'Yorum Ekleme ızinsiz', 1=>'Yorum Ekleme ızinli'),
);

?>
