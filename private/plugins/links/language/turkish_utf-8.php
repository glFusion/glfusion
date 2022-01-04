<?php
/**
* glFusion CMS
*
* UTF-8 Language File for Links Plugin
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2008-2018 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*  Based on prior work Copyright (C) 2001-2007 by the following authors:
*   Tony Bibbs - tony AT tonybibbs DOT com
*   Trinity Bays - trinity93 AT gmail DOT com
*   Tom Willett - twillett AT users DOT sourceforge DOT net
*
*/

if (!defined ('GVERSION')) {
    die ('This file cannot be used on its own.');
}

global $LANG32;

###############################################################################
# Array Format:
# $LANGXX[YY]:    $LANG - variable name
#                 XX - file id number
#                 YY - phrase id number
###############################################################################

/**
* the link plugin's lang array
*
* @global array $LANG_LINKS
*/
$LANG_LINKS = array(
    10 => 'Gönderimler',
    14 => 'Bağlantılar',
    84 => 'Bağlantılar',
    88 => 'Yeni bağlantı yok',
    114 => 'Bağlantılar',
    116 => 'Bağlantı ekle',
    117 => 'Bozuk bağlantı bildirin',
    118 => 'Bozuk Bağlantı Raporu',
    119 => 'Aşağıdaki bağlantının kesildiği bildirildi: ',
    120 => 'Bağlantıyı düzenlemek için burayı tıklayın: ',
    121 => 'Bozuk Bağlantı şu kişi tarafından bildirildi: ',
    122 => 'Bu bozuk bağlantıyı bildirdiğiniz için teşekkür ederiz. Yönetici sorunu mümkün olan en kısa sürede düzeltecektir',
    123 => 'Teşekkürler',
    124 => 'Git',
    125 => 'Kategoriler',
    126 => 'Buradasınız:',
    'root' => 'Kök',
    'error_header'  => 'Bağlantı Gönderme Hatası',
    'verification_failed' => 'Belirtilen URL geçerli bir URL gibi görünmüyor',
    'category_not_found' => 'Kategori geçerli görünmüyor',
    'no_links'  => 'Hiçbir bağlantı girilmedi.',
);

###############################################################################
# for stats
/**
* the link plugin's lang stats array
*
* @global array $LANG_LINKS_STATS
*/
$LANG_LINKS_STATS = array(
    'links' => 'Sistemdeki Bağlantılar (Tıklamalar)',
    'stats_headline' => 'En İyi On Bağlantılar',
    'stats_page_title' => 'Bağlantılar',
    'stats_hits' => 'Hitler',
    'stats_no_hits' => 'Görünüşe göre bu sitede hiç bağlantı yok veya hiç kimse bir tanesine tıklamamış.',
);

###############################################################################
# for the search
/**
* the link plugin's lang search array
*
* @global array $LANG_LINKS_SEARCH
*/
$LANG_LINKS_SEARCH = array(
 'results' => 'Bağlantı Sonuçları',
 'title' => 'Başlık',
 'date' => 'Ekleme Tarihi',
 'author' => 'Gönderildi',
 'hits' => 'Tıklamalar'
);

###############################################################################
# for the submission form
/**
* the link plugin's lang submit form array
*
* @global array $LANG_LINKS_SUBMIT
*/
$LANG_LINKS_SUBMIT = array(
    1 => 'Bir Bağlantı Gönderin',
    2 => 'Bağlantı',
    3 => 'Kategori',
    4 => 'Diğer',
    5 => 'Diğeri ise lütfen belirtin',
    6 => 'Hata: Eksik Kategori',
    7 => '"Diğer" i seçerken lütfen bir kategori adı da girin',
    8 => 'Başlık',
    9 => 'URL',
    10 => 'Kategori',
    11 => 'Bağlantı Gönderimleri',
    12 => 'Gönderen',
);

###############################################################################
# autotag description

$LANG_LI_AUTOTAG = array(
    'desc_link'                 => 'Bağlantı: bu sitedeki bir Bağlantının ayrıntı sayfasına; bağlantı_metni varsayılan olarak bağlantı adına ayarlanır. kullanım: [bağlantı:<i>bağlantı_kimliği</i>{bağlantı_başlığı}]',
);

###############################################################################
# Messages for COM_showMessage the submission form

$PLG_links_MESSAGE1 = "{$_CONF['site_name']} bağlantısını gönderdiğiniz için teşekkür ederiz.  Personelimizin onayına sunulmuştur.  Onaylanırsa bağlantınız <a href={$_CONF['site_url']}/links/index.php> bağlantılar </a> bölümünde görülecektir.";
$PLG_links_MESSAGE2 = 'Bağlantınız başarıyla kaydedildi.';
$PLG_links_MESSAGE3 = 'Bağlantı başarıyla silindi.';
$PLG_links_MESSAGE4 = "{$_CONF['site_name']} bağlantısını gönderdiğiniz için teşekkür ederiz.  Şimdi <a href={$_CONF['site_url']}/links/index.php> bağlantılar </a> bölümünde görebilirsiniz.";
$PLG_links_MESSAGE5 = "Bu kategoriyi görüntülemek için yeterli erişim haklarına sahip değilsiniz.";
$PLG_links_MESSAGE6 = 'Bu kategoriyi düzenlemek için yeterli haklara sahip değilsiniz.';
$PLG_links_MESSAGE7 = 'Lütfen bir Kategori Adı ve Açıklaması girin.';

$PLG_links_MESSAGE10 = 'Kategoriniz başarıyla kaydedildi.';
$PLG_links_MESSAGE11 = 'Bir kategorinin kimliğini "site" veya "kullanıcı" olarak ayarlamanıza izin verilmez - bunlar dahili kullanım için ayrılmıştır.';
$PLG_links_MESSAGE12 = 'Bir üst kategoriyi kendi alt kategorisinin alt kategorisi yapmaya çalışıyorsunuz. Bu bir öksüz kategori oluşturacaktır, bu nedenle lütfen önce alt kategoriyi veya kategorileri daha yüksek bir seviyeye taşıyın.';
$PLG_links_MESSAGE13 = 'Kategori başarıyla silindi.';
$PLG_links_MESSAGE14 = 'Kategori, bağlantılar ve / veya kategoriler içeriiyor. Lütfen önce bunları kaldırın.';
$PLG_links_MESSAGE15 = 'Bu kategoriyi silmek için yeterli haklara sahip değilsiniz.';
$PLG_links_MESSAGE16 = 'Böyle bir kategori yok.';
$PLG_links_MESSAGE17 = 'Bu kategori kimliği zaten kullanılıyor.';

// Messages for the plugin upgrade
$PLG_links_MESSAGE3001 = 'Eklenti yükseltmesi desteklenmiyor.';
$PLG_links_MESSAGE3002 = $LANG32[9];

###############################################################################
# admin/link.php
/**
* the link plugin's lang admin array
*
* @global array $LANG_LINKS_ADMIN
*/
$LANG_LINKS_ADMIN = array(
    1 => 'Bağlantı Düzenleyici',
    2 => 'Bağlantı kimliği',
    3 => 'Bağlantı Başlığı',
    4 => 'Bağlantı URL\'si',
    5 => 'Kategori',
    6 => '(http: // dahil)',
    7 => 'Diğer',
    8 => 'Bağlantı Hitleri',
    9 => 'Bağlantı Açıklaması',
    10 => 'Bir bağlantı Başlığı, URL ve Açıklama sağlamanız gerekir.',
    11 => 'Bağlantı Yönetimi',
    12 => 'Bir bağlantıyı değiştirmek veya silmek için, o bağlantının aşağıdaki düzenleme simgesine tıklayın.  Yeni bir bağlantı veya yeni bir kategori oluşturmak için yukarıdaki "Yeni bağlantı" veya "Yeni kategori" üzerine tıklayın.  Birden çok kategoriyi düzenlemek için yukarıdaki "Kategorileri düzenle" yi tıklayın.',
    14 => 'Bağlantı Kategorisi',
    16 => 'Erişim Engellendi',
    17 => "Haklarına sahip olmadığınız bir bağlantıya erişmeye çalışıyorsunuz.  Bu girişim günlüğe kaydedildi.  Lütfen <a href=\"{$_CONF['site_admin_url']}/plugins/links/index.php\"> bağlantı yönetimi ekranına geri dönün </a>.",
    20 => 'Diğer ise belirtin',
    21 => 'kaydet',
    22 => 'vazgeç',
    23 => 'sil',
    24 => 'Bağlantı bulunamadı',
    25 => 'Düzenlemek için seçtiğiniz bağlantı bulunamadı.',
    26 => 'Bağlantıları Doğrula',
    27 => 'HTML Durumu',
    28 => 'Kategoriyi Düzenle',
    29 => 'Aşağıdaki ayrıntıları girin veya düzenleyin.',
    30 => 'Kategori',
    31 => 'Açıklama',
    32 => 'Kategori Kimliği',
    33 => 'Konu',
    34 => 'Üst',
    35 => 'Hepsi',
    40 => 'Bu kategoriyi düzenle',
    41 => 'Ekle',
    42 => 'Bu kategoriyi sil',
    43 => 'Site Kategorileri',
    44 => 'Alt Kategori Ekle',
    46 => 'Kullanıcı %s, erişim haklarına sahip olmadığı bir kategoriyi silmeye çalıştı',
    50 => 'Kategori Yöneticisi',
    51 => 'Yeni Bağlantı',
    52 => 'Yeni Kök Kategori',
    53 => 'Bağlantı Yöneticisi',
    54 => 'Bağlantı Kategorisi Yönetimi',
    55 => 'Aşağıdaki kategorileri düzenleyin. Başka kategoriler veya bağlantılar içeren bir kategoriyi silemeyeceğinizi unutmayın - önce bunları silmeli veya başka bir kategoriye taşımalısınız.',
    56 => 'Kategori Düzenleyici',
    57 => 'Henüz doğrulanmadı',
    58 => 'Şimdi doğrula',
    59 => '<br /> <br /> Görüntülenen tüm bağlantıları doğrulamak için lütfen aşağıdaki "Şimdi doğrula" bağlantısını tıklayın. Doğrulama işlemi, görüntülenen bağlantıların miktarına bağlı olarak biraz zaman alabilir.',
    60 => '%s kullanıcısı yasa dışı bir şekilde %s kategorisini düzenlemeyi denedi.',
    61 => 'Sahibi',
    62 => 'Son Güncelleme',
    63 => 'Bu linki silmek istediğinize emin misiniz?',
    64 => 'Bu kategoriyi silmek istediğinize eminmisiniz?',
    65 => 'Orta Bağlantı',
    66 => 'Bu ekran, bağlantı oluşturmanıza / düzenlemenize olanak tanır.',
    67 => 'Bu ekran, bir bağlantı kategorisi oluşturmanıza / düzenlemenize olanak tanır.',
);

$LANG_LINKS_STATUS = array(
    100 => "Devam et",
    101 => "Protokolleri Değiştir",
    200 => "Tamam",
    201 => "Oluşturuldu",
    202 => "Kabul edildi",
    203 => "Yetkili Olmayan Bilgiler",
    204 => "İçerik Yok",
    205 => "İçeriği Sıfırla",
    206 => "Kısmi İçerik",
    300 => "Çoklu Seçim",
    301 => "Kalıcı Olarak Taşındı",
    302 => "Bulundu",
    303 => "Diğerlerini Gör",
    304 => "Değiştirilmedi",
    305 => "Proxy kullan",
    307 => "Geçici Yönlendirme",
    400 => "Hatalı İstek",
    401 => "Yetkisiz",
    402 => "Ödeme Gerekli",
    403 => "Yasak",
    404 => "Bulunamadı",
    405 => "İzin Verilmeyen Yöntem",
    406 => "Kabul Edilemez",
    407 => "Proxy Kimlik Doğrulaması Gerekiyor",
    408 => "İstek zaman aşımına uğradı",
    409 => "Çakışma",
    410 => "Gitti",
    411 => "Uzunluk Gerekli",
    412 => "Ön Koşul Başarısız",
    413 => "Girilen veri çok fazla",
    414 => "URI Çok Uzun",
    415 => "Desteklenmeyen Medya Türü",
    416 => "Talep Edilen Aralık Karşılanamaz",
    417 => "Beklenti Başarısız",
    500 => "İç/Dahili Sunucu Hatası",
    501 => "Uygulanmadı",
    502 => "Hatalı Ağ Geçidi",
    503 => "Hizmet Kullanılamıyor",
    504 => "Ağ Geçidi Zaman Aşımı",
    505 => "HTTP Sürümü Desteklenmiyor",
    999 => "Bağlantı zaman aşımına uğradı"
);


// Localization of the Admin Configuration UI
$LANG_configsections['links'] = array(
    'label' => 'Bağlantılar',
    'title' => 'Bağlantı Yapılandırması'
);

$LANG_confignames['links'] = array(
    'linksloginrequired' => 'Bağlantılar İçin Giriş Gerekli',
    'linksubmission' => 'Bağlantı Gönderme Sırasını Etkinleştir',
    'newlinksinterval' => 'Yeni Bağlantı Aralığı',
    'hidenewlinks' => 'Yeni Bağlantıları Gizle',
    'hidelinksmenu' => 'Bağlantılar Menüsü Girişini Gizle',
    'linkcols' => 'Sütun Başına Kategori',
    'linksperpage' => 'Sayfa Başına Bağlantı',
    'show_top10' => 'İlk 10 Bağlantıyı Göster',
    'notification' => 'Bildirim E-Postası',
    'delete_links' => 'Sahipli Bağlantıları Sil',
    'aftersave' => 'Bağlantıyı Kaydettikten Sonra',
    'show_category_descriptions' => 'Kategori Açıklamasını Göster',
    'root' => 'Kök Kategorinin Kimliği',
    'default_permissions' => 'Bağlantı Varsayılan İzinleri',
    'target_blank' => 'Bağlantıları Yeni Pencerede Aç',
    'displayblocks' => 'GlFusion Bloklarını Görüntüleme',
    'submission'    => 'Bağlantı Gönderimi',
);

$LANG_configsubgroups['links'] = array(
    'sg_main' => 'Ana Ayarlar'
);

$LANG_fs['links'] = array(
    'fs_public' => 'Genel Bağlantı Listesi Ayarları',
    'fs_admin' => 'Bağlantılar Yönetici Ayarları',
    'fs_permissions' => 'Varsayılan İzinler'
);

$LANG_configSelect['links'] = array(
    0 => array(1=>'True', 0=>'False'),
    1 => array(true=>'True', false=>'False'),
    9 => array('item'=>'Bağlantılı Siteye Yönlendirme', 'list'=>'Yönetici Listesini Görüntüle', 'plugin'=>'Genel Listeyi Görüntüle', 'home'=>'Display Home', 'admin'=>'Display Admin'),
    12 => array(0=>'No access', 2=>'Salt Okunur', 3=>'Read-Write'),
    13 => array(0=>'Sol Bloklar', 1=>'Sağ Bloklar', 2=>'Sol ve Sağ Bloklar', 3=>'Hiçbiri'),
    14 => array(0=>'Hiçbiri', 1=>'Yalnızca Giriş Yapan Kullanıcılar', 2=>'Kimse', 3=>'Hiçbiri')

);

?>
