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
	'comdel'	    => ' yorumlar silindi.',
	'deletespam'    => 'İstenmeyen E-Postayı Sil',
	'masshead'	    => '<hr /> <h1 align = "center"> Spam Yorumlarını Toplu Silme </h1>',
	'masstb'        => '<hr /> <h1 align = "center"> Geri İzleme Spam\'ını Toplu Olarak Silme </h1>',
	'note1'		    => '<p> Not: Toplu Silme, size yardımcı olmayı amaçlamaktadır',
	'note2'		    => ' yorum spam\'i ve Spam-X onu yakalamaz. </p> <ul> <li> Önce bağlantıları veya diğer bağlantıları bulun ',
	'note3'		    => 'bu spam yorumun tanımlayıcıları ve onu kişisel kara listenize ekleyin. </li> <li> Ardından ',
	'note4'		    => 'buraya geri gelin ve Spam-X\'in en son yorumları spam için kontrol etmesini sağlayın. </li> </ul> <p> Yorumlar ',
	'note5'		    => 'en yeni yorumdan en eskiye doğru kontrol edilir - daha fazla yorum kontrol edilir ',
	'note6'		    => 'kontrol için daha fazla zaman gerekiyor.</p>',
	'numtocheck'    => 'Kontrol edilecek Yorum Sayısı',
    'RDF'           => 'RDF url: ',
    'URL'           => 'Spam-X Listesinin URL\'si: ',
    'access_denied' => 'Erişim Engellendi',
    'access_denied_msg' => 'Bu Sayfaya Sadece Kök Kullanıcılar Erişebilir.  Kullanıcı adınız ve IP\'niz kaydedildi.',
    'acmod'         => 'Spam-X Eylem Modülleri',
    'actmod'        => 'Aktif Modüller',
    'add1'          => 'Eklendi ',
    'add2'          => ' gelen girişler ',
    'add3'          => "'kara Liste.",
    'addcen'        => 'Sansür Listesine Ekle',
    'addentry'      => 'Kayıt Ekle',
    'admin'         => 'Eklenti Yönetimi',
    'adminc'        => 'Yönetim Komutları:',
    'all'           => 'Hepsi',
    'allow_url_fopen' => '<p>Maalesef web sunucusu yapılandırmanız uzak dosyaların okunmasına izin vermiyor (<code>allow_url_fopen</code> kapalı). Lütfen kara listeyi aşağıdaki URL\'den indirin ve tekrar denemeden önce onu glFusion\'ın "veri" dizini <tt>%s</tt>\'e yükleyin:',
    'auto_refresh_off' => 'Otomatik Yenileme Kapalı',
    'auto_refresh_on' => 'Otomatik Yenileme Açık',
    'availb'        => 'Kullanılabilir Kara Listeler',
    'avmod'         => 'Kullanılabilir Modüller',
    'blacklist'     => 'Kara Liste',
    'blacklist_prompt' => 'İstenmeyen postayı tetikleyecek kelimeleri girin',
    'blacklist_success_delete' => 'Seçilen öğeler başarıyla silindi',
    'blacklist_success_save' => 'Spam-X Filtresi Başarıyla Kaydedildi',
    'blocked'       => 'Engellendi',
    'cancel'        => 'Vazgeç',
    'cancel'        => 'Vazgeç',
    'clearlog'      => 'Günlük Dosyasını Temizle',
    'clicki'        => 'Kara Listeyi İçe Aktarmak İçin Tıklayın',
    'clickv'        => 'Kara Listeyi Görmek İçin Tıklayın',
    'comment'       => 'Yorum',
    'coninst'       => '<hr>Kaldırmak için Aktif bir modüle tıklayın, eklemek için Mevcut bir modüle tıklayın.<br>Modüller sunuldukları sırada yürütülür.',
    'conmod'        => 'Spam-X Modülü Kullanımını Yapılandırın',
    'content'       => 'İçerik',
    'content_type'  => 'İçerik Türü',
    'delete'        => 'Sil',
    'delete_confirm' => 'Bu öğeyi silmek istediğinize eminmisiniz?',
    'delete_confirm_2' => 'Bu öğeyi silmek istediğinize GERÇEKTEN EMİN MİSİNİZ',
    'documentation' => 'Spam-X Eklenti Belgeleri',
    'e1'            => 'Bir girişi silmek için tıklayın.',
    'e2'            => 'Bir girdi eklemek için, kutuya girin ve Ekle\'ye tıklayın.  Girişler, tam Perl Normal İfadelerini kullanabilir.',
    'e3'            => 'GlFusions CensorList\'ten kelimeleri eklemek için düğmeye basın:',
    'edit_filter_entry' => 'Filtreyi Düzenle',
    'edit_filters'  => 'Filtreleri Düzenle',
    'email'         => 'E-Posta',
    'emailmsg'      => "\"%s\" konumunda yeni bir spam gönderisi gönderildi\n Kullanıcı UID'si: \"%s\"\n\n İçerik:\"%s\"",
    'emailsubject'  => '%s adresindeki spam gönderisi',
    'enabled'       => 'Kaldırmadan önce eklentiyi devre dışı bırakın.',
    'entries'       => ' girdiler.',
    'entriesadded'  => 'Girdiler Eklendi',
    'entriesdeleted'=> 'Girdiler Silindi',
    'exmod'         => 'Spam-X İnceleme Modülleri',
    'filter'        => 'Filtre',
    'filter_instruction' => 'Burada, her kayıt ve sitede yayınlanan her gönderiye uygulanacak filtreleri tanımlayabilirsiniz. Kontrollerden herhangi biri doğru çıkarsa, kayıt / gönderi spam olarak engellenecektir',
    'filters'       => 'Filtreler',
    'forum_post'    => 'Forum Mesajı',
    'foundspam'     => 'Spam Gönderi eşleşmesi bulundu ',
    'foundspam2'    => ' kullanıcı tarafından gönderildi ',
    'foundspam3'    => ' iP\'den ',
    'fsc'           => 'Spam Gönderi eşleşmesi bulundu ',
    'fsc1'          => ' kullanıcı tarafından gönderildi ',
    'fsc2'          => ' iP\'den ',
    'headerblack'   => 'Spam-X HTTP Üstbilgisi Kara Listesi',
    'headers'       => 'İstek başlıkları:',
    'history'       => 'Son 3 Ay',
    'http_header'   => 'HTTP Başlığı',
    'http_header_prompt' => 'Başlık',
    'impinst1a'     => 'Spam-X yorumu Spam engelleyici özelliğini kullanmadan önce, diğer sitelerden kişisel Kara Listeleri görüntülemek ve içe aktarmak için',
    'impinst1b'     => ' siteler, aşağıdaki iki butona basmanızı rica ediyorum. (Sonuncuya basmanız gerekir.)',
    'impinst2'      => 'Bu, ilk olarak web sitenizi Gplugs/Spam-X sitesine gönderir, böylece ana listeye eklenebilir ',
    'impinst2a'     => 'kara listelerini paylaşan siteler. (Not: Birden fazla siteniz varsa, birini site olarak belirlemek isteyebilirsiniz ',
    'impinst2b'     => 'master ve yalnızca adını gönderin. Bu, sitelerinizi kolayca güncellemenize ve listeyi daha küçük tutmanıza olanak tanır.) ',
    'impinst2c'     => 'Gönder düğmesine bastıktan sonra, buraya dönmek için tarayıcınızda [geri] düğmesine basın.',
    'impinst3'      => 'Aşağıdaki değerler gönderilecektir: (yanlışlarsa bunları düzenleyebilirsiniz).',
    'import_failure'=> '<p><strong>Hata:</strong> Giriş bulunamadı.',
    'import_success'=> '<p>%d kara liste girişi başarıyla içe aktarıldı.',
    'initial_Pimport'=> '<p>Kişisel Kara Liste İçe Aktarma"',
    'initial_import' => 'İlk MT-Kara Liste İçe Aktarma',
    'inst1'         => '<p>Bunu yaparsanız, diğerleri ',
    'inst2'         => 'kişisel kara listenizi görüntüleyip içe aktarabilecek ve daha etkili bir ',
    'inst3'         => 'dağıtılmış veritabanı.</p><p>Web sitenizi gönderdiyseniz ve web sitenizin listede kalmasını istemediğinize karar verdiyseniz ',
    'inst4'         => '<a href="mailto:spamx@pigstye.net">spamx@pigstye.net</a> adresine bir e-posta gönderin ve bana bildirin. ',
    'inst5'         => 'Tüm istekler yerine getirilecektir.',
    'install'       => 'Kur',
    'install_failed' => 'Kurulum Başarısız - Nedenini öğrenmek için hata günlüğünüze bakın.',
    'install_header' => 'Eklentiyi Kur / Kaldır',
    'install_success' => 'Kurulum Başarılı',
    'installdoc'    => 'Belgeyi Yükle.',
    'installed'     => 'Eklenti Yüklendi',
    'instructions'  => 'Spam-X, sitenizdeki spam gönderilerini engellemek için kullanılabilecek kelimeleri, URL\'leri ve diğer öğeleri tanımlamanıza olanak tanır.',
    'interactive_tester' => 'Etkileşimli Test',
    'invalid_email_or_ip'   => 'Geçersiz e-posta adresi veya IP adresi engellendi',
    'invalid_item_id' => 'Geçersiz Kimlik',
    'ip_address'    => 'IP adresi',
    'ip_blacklist'  => 'IP Kara Liste',
    'ip_error'  => 'Giriş, geçerli bir IP veya IP aralığı gibi görünmüyor',
    'ip_prompt' => 'Engellenecek IP\'yi girin',
    'ipblack' => 'Spam-X IP Kara Listesi',
    'ipofurl'   => 'URL\'nin IP\'si',
    'ipofurl_prompt' => 'Engellenecek bağlantıların IP\'sini girin',
    'ipofurlblack' => 'URL Kara Listesinin Spam-X IP\'si',
    'logcleared' => '- Spam-X Günlük Dosyası Temizlendi',
    'mblack' => 'Kara Listem:',
    'new_entry' => 'Yeni Kayıt',
    'new_filter_entry' => 'Yeni Filtre Girişi',
    'no_bl_data_error' => 'Hata yok',
    'no_blocked' => 'Bu modül tarafından hiçbir spam engellenmedi',
    'no_filter_data' => 'Hiçbir filtre tanımlanmadı',
    'ok' => 'Tamam',
    'pblack' => 'Spam-X Kişisel Kara Liste',
    'plugin' => 'Eklenti',
    'plugin_name' => 'Spam-X',
    'readme' => 'DUR! Kuruluma basmadan önce lütfen oku ',
    'referrer'      => 'Yönlendiren',
    'response'      => 'Yanıt',
    'rlinks' => 'İlgili Bağlantılar:',
    'rsscreated' => 'RSS Akışı Oluşturuldu',
    'scan_comments' => 'Yorumları Tara',
    'scan_trackbacks' => 'Geri İzlemeleri Tara',
    'secbut' => 'Bu ikinci düğme, başkalarının listenizi içe aktarabilmesi için bir rdf beslemesi oluşturur.',
    'sitename' => 'Site Adı: ',
    'slvwhitelist' => 'SLV Beyazliste',
    'spamdeleted' => 'Silinen Spam Gönderisi',
    'spamx_filters' => 'Spam-X Filtreleri',
    'stats_deleted' => 'Gönderiler spam olarak silindi',
    'stats_entries' => 'Girdiler',
    'stats_header' => 'HTTP Başlıkları',
    'stats_headline' => 'Spam-X İstatistikleri',
    'stats_ip' => 'Engelli IP\'ler',
    'stats_ipofurl' => 'URL\'nin IP\'si tarafından engellendi',
    'stats_mtblacklist' => 'MT-Kara Liste',
    'stats_page_title' => 'Kara Liste',
    'stats_pblacklist' => 'Kişisel Kara Liste',
    'submit'        => 'Gönder',
    'submit' => 'Gönder',
    'subthis' => 'bu bilgi Spam-X Merkezi Veritabanına',
    'type'  => 'Tip',
    'uMTlist' => 'MT-Blacklist \'i Güncelle',
    'uMTlist2' => ': Eklendi ',
    'uMTlist3' => ' girdiler ve silindi ',
    'uPlist' => 'Kişisel Kara Listeyi Güncelle',
    'uninstall' => 'Kaldır',
    'uninstall_msg' => 'Eklenti Başarıyla Kaldırıldı',
    'uninstalled' => 'Eklenti Yüklü Değil',
    'user_agent'    => 'Kullanıcı Aracı',
    'username'  => 'Kullanıcı Adı',
    'value' => 'Değer',
    'viewlog' => 'Spam-X Günlüklerini Görüntüle',
    'warning' => 'Uyarı! Eklenti hala Etkin',
);


/* Define Messages that are shown when Spam-X module action is taken */
$PLG_spamx_MESSAGE128 = 'Spam algılandı. Gönderi silindi.';
$PLG_spamx_MESSAGE8   = 'Spam algılandı. Yöneticiye e-posta gönderildi.';

// Messages for the plugin upgrade
$PLG_spamx_MESSAGE3001 = 'Eklenti yükseltmesi desteklenmiyor.';
$PLG_spamx_MESSAGE3002 = $LANG32[9];


// Localization of the Admin Configuration UI
$LANG_configsections['spamx'] = array(
    'label' => 'Spam-X',
    'title' => 'Spam-X Yapılandırması'
);

$LANG_confignames['spamx'] = array(
    'action' => 'Spam-X İşlemleri',
    'notification_email' => 'Bildirim E-Postası',
    'admin_override' => "Yönetici Gönderilerini Filtreleme",
    'logging' => 'Günlük Kaydını Etkileştir',
    'timeout' => 'Zaman Aşımı',
    'sfs_username_check' => 'Kullanıcı adı doğrulamasını etkinleştir',
    'sfs_email_check' => 'E-posta doğrulamasını etkinleştir',
    'sfs_ip_check' => 'IP adresi doğrulamasını etkinleştir',
    'sfs_username_confidence' => 'Spam engellemeyi tetiklemek için Kullanıcı adı eşleşmesinde minimum güven düzeyi',
    'sfs_email_confidence' => 'İstenmeyen posta engellemesini tetiklemek için E-posta eşleşmesinde minimum güven düzeyi',
    'sfs_ip_confidence' => 'İstenmeyen posta engellemesini tetiklemek için IP adresi eşleşmesinde minimum güven düzeyi',
    'slc_max_links' => 'Yayında izin verilen maksimum Bağlantı sayısı',
    'debug' => 'Hata Ayıklama Günlüğü',
    'akismet_enabled' => 'Akismet Modülü Etkin',
    'akismet_api_key' => 'Akismet API Anahtarı (Gerekli)',
    'fc_enable' => 'Form Kontrolünü Etkinleştir',
    'sfs_enable' => 'Forum Spam\'ini Durdurmayı Etkinleştir',
    'slc_enable' => 'Spam Bağlantı Sayacını Etkinleştir',
    'action_delete' => 'Tanımlanmış Spam\'i Sil',
    'action_mail' => 'Spam Yakalandığında Posta Yöneticisi',
);

$LANG_configsubgroups['spamx'] = array(
    'sg_main' => 'Ana Ayarlar'
);

$LANG_fs['spamx'] = array(
    'fs_main' => 'Spam-X Ana Ayarları',
    'fs_sfs'  => 'Forum Spam\'ini Durdur',
    'fs_slc'  => 'Spam Bağlantı Sayacı',
    'fs_akismet' => 'Akismet',
    'fs_formcheck' => 'Form Kontrolü',
);

$LANG_configSelect['spamx'] = array(
    0 => array(1 => 'Doğru', 0 => 'False'),
    1 => array(TRUE => 'Doğru', FALSE => 'False')
);
?>
