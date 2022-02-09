<?php
/**
* glFusion CMS
*
* UTF-8 Language File for Calendar Plugin
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2008-2018 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*  Based on prior work Copyright (C) 2001-2005 by the following authors:
*   Tony Bibbs - tony AT tonybibbs DOT com
*   Trinity Bays - trinity93 AT gmail DOT com
*
*/

if (!defined ('GVERSION')) {
    die ('This file cannot be used on its own.');
}

global $LANG32;

###############################################################################
# Array Format:
# $LANGXX[YY]:  $LANG - variable name
#               XX    - file id number
#               YY    - phrase id number
###############################################################################

# index.php
$LANG_CAL_1 = array(
    1 => 'Takvim Etkinlikleri',
    2 => 'Üzgünüm, gösterilecek bir olay yok.',
    3 => 'Ne zaman',
    4 => 'Nereye',
    5 => 'Açıklama',
    6 => 'Etkinlik Ekle',
    7 => 'Yaklaşan Etkinlikler',
    8 => 'Bu etkinliği takviminize ekleyerek, Kullanıcı İşlevleri alanından "Takvimim" öğesini tıklayarak yalnızca ilgilendiğiniz etkinlikleri hızlı bir şekilde görüntüleyebilirsiniz.',
    9 => 'Takvimime Ekle',
    10 => 'Takvimimden Kaldır',
    11 => '%s Takvimine Etkinlik Ekleniyor',
    12 => 'Etkinlik',
    13 => 'Başlangıç',
    14 => 'Bitiş',
    15 => 'Takvime Geri Dön',
    16 => 'Takvim',
    17 => 'Başlangıç Tarihi',
    18 => 'Bitiş Tarihi',
    19 => 'Takvim Gönderimleri',
    20 => 'Başlık',
    21 => 'Başlangıç Tarihi',
    22 => 'URL',
    23 => 'Etkinlikleriniz',
    24 => 'Site Etkinlikleri',
    25 => 'Yaklaşan etkinlik yok',
    26 => 'Bir Etkinlik Gönderin',
    27 => "{$_CONF['site_name']} sitesine bir etkinlik göndermek, etkinliğinizi kullanıcıların isteğe bağlı olarak kişisel takvimlerine ekleyebilecekleri ana takvime koyacaktır. Bu özellik, doğum günleri ve yıldönümleri gibi kişisel etkinliklerinizi kaydetmek için <b> DEĞİL </b>. <br> <br> Etkinliğinizi gönderdikten sonra yöneticilerimize gönderilecek ve onaylanırsa, etkinliğiniz şurada görünecektir: ana takvim.",
    28 => 'Başlık',
    29 => 'Bitiş Zamanı',
    30 => 'Başlangıç Zamanı',
    31 => 'Tüm Gün Etkinliği',
    32 => 'Adres Satırı 1',
    33 => 'Adres Satırı 2',
    34 => 'Şehir/Kasaba',
    35 => 'İlçe',
    36 => 'Posta Kodu',
    37 => 'Etkinlik Türü',
    38 => 'Etkinlik Türünü Düzenle',
    39 => 'Konum',
    40 => 'Etkinlik Ekle',
    41 => 'Ana Takvim',
    42 => 'Kişisel Takvim',
    43 => 'Bağlantı',
    44 => 'HTML etiketlerine izin verilmez',
    45 => 'Gönder',
    46 => 'Sistemdeki Etkinlikler',
    47 => 'En İyi On Etkinlik',
    48 => 'Hitler',
    49 => 'Görünüşe göre bu sitede herhangi bir etkinlik yok veya hiç kimse bir tanesine tıklamamış.',
    50 => 'Etkinlikler',
    51 => 'Sil',
    52 => 'Gönderen',
    53 => 'Takvim Görünümü',
);

$_LANG_CAL_SEARCH = array(
    'results' => 'Takvim Sonuçları',
    'title' => 'Başlık',
    'date_time' => 'Tarih ve Zaman',
    'location' => 'Konum',
    'description' => 'Açıklama'
);

###############################################################################
# calendar.php ($LANG30)

$LANG_CAL_2 = array(
    8 => 'Kişisel Etkinlik Ekle',
    9 => '%s Etkinlik',
    10 => 'Etkinlikler',
    11 => 'Ana Takvim',
    12 => 'Takvimim',
    25 => 'Geri dön ',
    26 => 'Tüm Gün',
    27 => 'Hafta',
    28 => 'Kişisel Takvim',
    29 => 'Genel Takvim',
    30 => 'etkinliği sil',
    31 => 'Ekle',
    32 => 'Etkinlik',
    33 => 'Tarih',
    34 => 'Zaman',
    35 => 'Hızlı Ekle',
    36 => 'Gönder',
    37 => 'Üzgünüz, kişisel takvim özelliği bu sitede etkin değil',
    38 => 'Kişisel Etkinlik Düzenleyicisi',
    39 => 'Gün',
    40 => 'Hafta',
    41 => 'Ay',
    42 => 'Ana Etkinlik Ekle',
    43 => 'Etkinlik Gönderimleri'
);

###############################################################################
# admin/plugins/calendar/index.php, formerly admin/event.php ($LANG22)

$LANG_CAL_ADMIN = array(
    1 => 'Etkinlik Düzenleyici',
    2 => 'Hata',
    3 => 'Gönderi Modu',
    4 => 'Etkinlik URL\'si',
    5 => 'Etkinlik Başlangıç Tarihi',
    6 => 'Etkinlik Bitiş Tarihi',
    7 => 'Etkinliğin Yeri',
    8 => 'Etkinlik Açıklaması',
    9 => '(http: // dahil)',
    10 => 'Tarihleri ​​/ saatleri, etkinlik başlığını ve açıklamasını sağlamalısınız',
    11 => 'Takvim Yöneticisi',
    12 => 'Bir etkinliği değiştirmek veya silmek için, aşağıdaki etkinliğin düzenleme simgesine tıklayın.  Yeni bir etkinlik oluşturmak için yukarıdaki "Yeni Oluştur" u tıklayın.  Mevcut bir etkinliğin bir kopyasını oluşturmak için kopyala simgesine tıklayın.',
    13 => 'Sahibi',
    14 => 'Başlangıç Tarihi',
    15 => 'Bitiş Tarihi',
    16 => '',
    17 => "Haklarına sahip olmadığınız bir etkinliğe erişmeye çalışıyorsunuz.  Bu girişim günlüğe kaydedildi.  Lütfen <a href=\"{$_CONF['site_admin_url']}/plugins/calendar/index.php\"> etkinlik yönetimi ekranına geri dönün </a>.",
    18 => 'Boş satır',
    19 => 'Boş satır',
    20 => 'kaydet',
    21 => 'vazgeç',
    22 => 'sil',
    23 => 'Hatalı başlangıç ​​tarihi.',
    24 => 'Hatalı bitiş tarihi.',
    25 => 'Bitiş tarihi, başlangıç ​​tarihinden önce.',
    26 => 'Toplu Etkinlik Yöneticisi',
    27 => 'Bunlar, veritabanınızdaki şu tarihten daha eski olaylardır ',
    28 => ' ay.  Dönemi istediğiniz gibi güncelleyin ve ardından Listeyi Güncelle öğesine tıklayın.  Görüntülenen sonuçlardan bir veya daha fazla olay seçin ve ardından bu eski olayları veritabanınızdan kaldırmak için aşağıdaki Sil simgesine tıklayın.  Yalnızca bu sayfada görüntülenen ve seçilen etkinlikler silinecektir.',
    29 => 'Boş satır',
    30 => 'Listeyi Güncelle',
    31 => 'Seçili TÜM kullanıcıları kalıcı olarak silmek istediğinizden emin misiniz?',
    32 => 'Tümünü Listele',
    33 => 'Silinmek üzere hiçbir etkinlik seçilmedi',
    34 => 'Etkinlik Kimliği',
    35 => 'silinemedi',
    36 => 'Başarıyla silindi',
    37 => 'Orta Düzey Etkinlik',
    38 => 'Toplu Etkinlik Yöneticisi',
    39 => 'Etkinlik Yöneticisi',
    40 => 'Etkinlik Listesi',
    41 => 'Bu ekran, olayları düzenlemenizi / oluşturmanızı sağlar. Aşağıdaki alanları düzenleyin ve kaydedin.',
);

$LANG_CAL_AUTOTAG = array(
    'desc_calendar' => 'Bağlantı: bu sitedeki bir Takvim etkinliğine; bağlantı_metni varsayılan olarak etkinlik başlığı:<i>event_id</i> {link_text}]',
);

$LANG_CAL_MESSAGE = array(
    'save' => 'Etkinliğiniz başarıyla kaydedildi.',
    'delete' => 'Etkinlik başarıyla silindi.',
    'private' => 'Etkinlik takviminize kaydedildi',
    'login' => 'Giriş yapana kadar kişisel takviminizi açamazsınız',
    'removed' => 'Etkinlik kişisel takviminizden başarıyla kaldırıldı',
    'noprivate' => 'Üzgünüz, kişisel takvimler bu sitede etkin değil',
    'unauth' => 'Üzgünüz, etkinlik yönetimi sayfasına erişiminiz yok.  Lütfen yetkisiz özelliklere erişmeye yönelik tüm girişimlerin günlüğe kaydedildiğini unutmayın',
    'delete_confirm' => 'Bu etkinliği silmek istediğinizden emin misiniz?'
);

$PLG_calendar_MESSAGE4 = "{$_CONF['site_name']} sitesine bir etkinlik gönderdiğiniz için teşekkür ederiz.  Personelimizin onayına sunulmuştur.  Onaylanırsa, etkinliğiniz burada, <a href=\"{$_CONF['site_url']}/calendar/index.php\"> takvim </a> bölümümüzde görülecektir.";
$PLG_calendar_MESSAGE17 = 'Etkinliğiniz başarıyla kaydedildi.';
$PLG_calendar_MESSAGE18 = 'Etkinlik başarıyla silindi.';
$PLG_calendar_MESSAGE24 = 'Etkinlik takviminize kaydedildi.';
$PLG_calendar_MESSAGE26 = 'Etkinlik başarıyla silindi.';

// Messages for the plugin upgrade
$PLG_calendar_MESSAGE3001 = 'Eklenti yükseltmesi desteklenmiyor.';
$PLG_calendar_MESSAGE3002 = $LANG32[9];

// Localization of the Admin Configuration UI
$LANG_configsections['calendar'] = array(
    'label' => 'Takvim',
    'title' => 'Takvim Yapılandırması'
);

$LANG_confignames['calendar'] = array(
    'calendarloginrequired' => 'Takvimde Giriş Gerekiyor',
    'hidecalendarmenu' => 'Takvim Menüsü Girişini Gizle',
    'personalcalendars' => 'Kişisel Takvimleri Etkinleştir',
    'eventsubmission' => 'Gönderim Sırasını Etkinleştir',
    'showupcomingevents' => 'Yaklaşan Etkinlikleri Göster',
    'upcomingeventsrange' => 'Yaklaşan Etkinlikler Aralığı',
    'event_types' => 'Etkinlik Türü',
    'hour_mode' => 'Saat Modu',
    'notification' => 'Bildirim E-Postası',
    'delete_event' => 'Sahibiyle Etkinlikleri Sil',
    'aftersave' => 'Etkinliği Kaydettikten Sonra',
    'default_permissions' => 'Etkinlik Varsayılan İzinleri',
    'only_admin_submit' => 'Yalnızca Yöneticilerin Göndermesine İzin Ver',
    'displayblocks' => 'GlFusion Bloklarını Görüntüleme',
);

$LANG_configsubgroups['calendar'] = array(
    'sg_main' => 'Ana Ayarlar'
);

$LANG_fs['calendar'] = array(
    'fs_main' => 'Genel Takvim Ayarları',
    'fs_permissions' => 'Varsayılan İzinler'
);

$LANG_configSelect['calendar'] = array(
    0 => array(1=> 'Doğru', 0 => 'False'),
    1 => array(true => 'Doğru', false => 'False'),
    6 => array(12 => '12', 24 => '24'),
    9 => array('item'=>'Etkinliğe Yönlendir', 'list'=>'Yönetici Listesini Görüntüle', 'plugin'=>'Takvimi Görüntüle', 'home'=>'Ana Ekranı Görüntüle', 'admin'=>'Ekran Yöneticisi'),
    12 => array(0=>'Erişim yok', 2=>'Salt Okunur', 3=>'Okunur/Yazılır'),
    13 => array(0=>'Sol Bloklar', 1=>'Sağ Bloklar', 2=>'Sol ve Sağ Bloklar', 3=>'Hiçbiri')
);

?>
