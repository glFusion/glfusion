<?php
/**
* glFusion CMS
*
* UTF-8 Language File for glFusion CAPTCHA Plugin
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2002-2018 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*/

if (!defined ('GVERSION')) {
    die ('This file cannot be used on its own.');
}

$LANG_CP00 = array (
    'menulabel'         => 'CAPTCHA',
    'plugin'            => 'CAPTCHA',
    'access_denied'     => 'Erişim Engellendi',
    'access_denied_msg' => 'Bu sayfaya erişmek için uygun güvenlik ayrıcalığına sahip değilsiniz.  Kullanıcı adınız ve IP\'niz kaydedildi.',
    'admin'             => 'CAPTCHA Yönetimi',
    'install_header'    => 'CAPTCHA Eklentisi Yükleme/Kaldırma',
    'installed'         => 'CAPTCHA Yüklendi',
    'uninstalled'       => 'CAPTCHA Yüklü Değil',
    'install_success'   => 'CAPTCHA Kurulumu Başarılı.  <br /> <br /> Ayarlarınızın barındırma ortamıyla doğru şekilde eşleştiğinden emin olmak için lütfen sistem belgelerini inceleyin ve ayrıca <a href="%s"> yönetim bölümünü </a> ziyaret edin.',
    'install_failed'    => 'Kurulum Başarısız - Nedenini öğrenmek için hata günlüğünüze bakın.',
    'uninstall_msg'     => 'Eklenti Başarıyla Kaldırıldı',
    'install'           => 'Kur',
    'uninstall'         => 'Kaldır',
    'warning'           => 'Uyarı! Eklenti hala Etkin',
    'enabled'           => 'Kaldırmadan önce eklentiyi devre dışı bırakın.',
    'readme'            => 'CAPTCHA Eklenti Kurulumu',
    'installdoc'        => "<a href=\"{$_CONF['site_admin_url']}/plugins/captcha/install_doc.html\"> Belgeyi Kurun </a>",
    'overview'          => 'CAPTCHA, spambotlar için ek bir güvenlik katmanı sağlayan yerel bir glFusion eklentisidir.  <br /> <br /> CAPTCHA (Carnegie Mellon Üniversitesi tarafından ticari markaya sahip "Computers and Humans Apart\'a Tamamen Otomatikleştirilmiş Kamusal Turing testi" nin kısaltması) kullanıcının insan  olup olmadığını belirlemek için hesaplamada kullanılan bir soru-yanıt testi türüdür.  Harf ve rakamlardan oluşan okunması zor bir grafik sunarak, karakterleri sadece bir insanın okuyup girebileceği varsayılır.  CAPTCHA testini uygulayarak, sitenizdeki Spambot girişlerinin sayısını azaltmaya yardımcı olabilirsiniz.',
    'details'           => 'CAPTCHA eklentisi, GD Grafik Kitaplığı veya ImageMagick\'i kullanarak dinamik görüntüler oluşturmak için CAPTCHA\'yı yapılandırmadığınız sürece statik (önceden oluşturulmuş) CAPTCHA görüntüleri kullanacaktır.  GD kitaplıklarını veya ImageMagick\'i kullanmak için True Type yazı tiplerini desteklemeleri gerekir.  TTF\'yi destekleyip desteklemediklerini öğrenmek için barındırma sağlayıcınıza danışın.',
    'preinstall_check'  => 'CAPTCHA aşağıdaki gereksinimlere sahiptir:',
    'glfusion_check'    => 'glFusion v1.4.3 veya üstü, bildirilen sürüm <b>%s </b>.',
    'php_check'         => 'PHP v5.3.3 veya üstü, bildirilen sürüm <b>%s </b>.',
    'preinstall_confirm' => "CAPTCHA kurulumuyla ilgili tüm ayrıntılar için lütfen <a href=\"{$_CONF['site_admin_url']}/plugins/captcha/install_doc.html\"> Kurulum Kılavuzuna </a> bakın.",
    'captcha_help'      => 'Kalın Metni Girin',
    'bypass_error'      => "Bu sitede CAPTCHA işlemini atlamayı denediniz, lütfen kaydolmak için Yeni Kullanıcı bağlantısını kullanın.",
    'bypass_error_blank' => "Bu sitede CAPTCHA işlemini atlamaya çalıştınız, lütfen CAPTCHA'yı tamamlayın.",
    'entry_error'       => 'Girilen CAPTCHA dizesi grafikteki karakterlerle eşleşmedi, lütfen tekrar deneyin. <b> Bu, büyük / küçük harfe duyarlıdır. </b>',
    'entry_error_pic'   => 'Seçilen CAPTCHA resimleri grafikteki istekle eşleşmedi, lütfen tekrar deneyin.',
    'captcha_info'      => 'CAPTCHA Eklentisi, glFusion siteniz için SpamBot\'lara karşı başka bir koruma katmanı sağlar.  Daha fazla bilgi için <a href="%s"> Çevrimiçi Belgeler Wiki </a> bölümüne bakın.',
    'enabled_header'    => 'Güncel CAPTCHA Ayarları',
    'on'                => 'Açık',
    'off'               => 'Kapalı',
    'captcha_alt'       => 'Grafik metnini girmelisiniz - grafiği okuyamıyorsanız Site Yöneticisi ile iletişime geçin',
    'save'              => 'Kaydet',
    'cancel'            => 'Vazgeç',
    'success'           => 'Yapılandırma Seçenekleri başarıyla kaydedildi.',
    'reload'            => 'Yeniden Yükle',
    'reload_failed'     => 'Maalesef, CAPTCHA görüntüsü otomatik olarak yeniden yüklenemiyor. Formu gönderin ve yeni bir CAPTCHA yüklenecektir',
    'reload_too_many'   => 'En fazla 5 görsel yenileme isteyebilirsiniz',
    'session_expired'   => 'CAPTCHA Oturumunuzun süresi doldu, lütfen tekrar deneyin',
    'picture'           => 'Görüntü',
    'characters'        => 'Karakterler',
    'ayah_error'        => 'Maalesef sizi insan olarak doğrulayamadık. Lütfen tekrar deneyin.',
    'captcha_math'      => 'Cevabınızı girin',
    'captcha_prompt'    => 'İnsan mısınız?',
    'recaptcha_entry_error'  => 'CAPTCHA doğrulaması başarısız oldu. Lütfen tekrar deneyin.',
);

// Localization of the Admin Configuration UI
$LANG_configsections['captcha'] = array(
    'label'                 => 'CAPTCHA',
    'title'                 => 'CAPTCHA Yapılandırması'
);
$LANG_confignames['captcha'] = array(
    'gfxDriver'             => 'Grafik sürücüsü',
    'gfxFormat'             => 'Grafik Formatı',
    'imageset'              => 'Statik Görüntü Seti',
    'debug'                 => 'Hata Ayıklama',
    'gfxPath'               => 'ImageMagick\'in Dönüştürme Yardımcı Programına Giden Tam Yol',
    'remoteusers'           => 'Tüm Uzak Kullanıcılar için CAPTCHA\'yı Zorla',
    'logging'               => 'Geçersiz CAPTCHA Girişimlerini Kaydet',
    'anonymous_only'        => 'Sadece İsimsiz',
    'enable_comment'        => 'Yorumu Etkinleştir',
    'enable_story'          => 'Yazıları Etkinleştir',
    'enable_registration'   => 'Kayıtları Etkinleştir',
    'enable_loginform'      => 'Oturum Açmayı Etkinleştir',
    'enable_forgotpassword' => 'Parolamı Unuttum\'u Etkinleştir',
    'enable_contact'        => 'İletişimi Etkinleştir',
    'enable_emailstory'     => 'E-posta Yazıları Etkinleştir',
    'enable_forum'          => 'Forumu Etkinleştir',
    'enable_mediagallery'   => 'Medya Galerisini (Kartpostallar) Etkinleştir',
    'enable_rating'         => 'Derecelendirme Eklentisi Desteğini Etkinleştir',
    'enable_links'          => 'Bağlantılar Eklentisi Desteğini Etkinleştir',
    'enable_calendar'       => 'Takvim Eklentisi Desteğini Etkinleştir',
    'expire'                => 'Bir CAPTCHA Oturumu Kaç Saniye Geçerli',
    'publickey'             => 'reCAPTCHA Genel Anahtar - <a href="https://www.google.com/recaptcha/admin/create" target=_blank> reCAPTCHA Kaydı </a>',
    'privatekey'            => 'reCAPTCHA Özel Anahtarı',
    'recaptcha_theme'       => 'reCAPTCHA Tema',

);
$LANG_configsubgroups['captcha'] = array(
    'sg_main'               => 'Yapılandırma Ayarları'
);
$LANG_fs['captcha'] = array(
    'cp_public'                 => 'Genel Ayarlar',
    'cp_integration'            => 'CAPTCHA Entegrasyonu',
);

$LANG_configSelect['captcha'] = array(
    0 => array(1=>'True', 0=>'False'),
    1 => array(true=>'True', false=>'False'),
    2 => array(0=>'GD Libs', 3=>'reCAPTCHA', 6=>'Matematik Denklemi'),
    4 => array('default'=>'Varsayılan','simple'=>'Temel'),
    5 => array('jpg'=>'JPG','png'=>'PNG'),
    6 => array('light' => 'aydınlık','dark' => 'karanlık'),
);

$PLG_captcha_MESSAGE1 = 'CAPTCHA eklenti yükseltmesi: Güncelleme başarıyla tamamlandı.';
$PLG_captcha_MESSAGE2 = 'CAPTCHA eklenti yükseltmesi başarısız oldu - error.log dosyasını kontrol edin';
$PLG_captcha_MESSAGE3 = 'CAPTCHA Eklentisi Başarıyla Yüklendi';
?>