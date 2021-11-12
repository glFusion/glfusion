<?php
/**
* glFusion CMS
*
* glFusion Installation UTF-8 Language File
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2008-2018 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*  Based on prior work Copyright (C) 2000-2008 by the following authors:
*   Tony Bibbs          tony AT tonybibbs DOT com
*   Mark Limburg        mlimburg AT users DOT sourceforge DOT net
*   Jason Whittenburg   jwhitten AT securitygeeks DOT com
*   Dirk Haun           dirk AT haun-online DOT de
*   Randy Kolenko       randy AT nextide DOT ca
*   Matt West           matt AT mattdanger DOT net
*
*/

if (!defined ('GVERSION')) {
    die ('This file can not be used on its own.');
}

$LANG_CHARSET = 'utf-8';

$LANG_INSTALL = array(
    'adminuser'                 => 'Yönetici Kullanıcı Adı',
    'back_to_top'               => 'Başa dön',
    'calendar'                  => 'Takvim eklentisi yüklensin mi?',
    'calendar_desc'             => 'Çevrimiçi bir takvim / etkinlik sistemi. Site kullanıcıları için site çapında bir takvim ve kişisel takvimler içerir.',
    'connection_settings'       => 'Bağlantı Ayarları',
    'content_plugins'           => 'İçerik ve Eklentiler',
    'copyright'                 => '<a href = "https://www.glfusion.org" target = "_ blank"> glFusion </a>, altında yayınlanan ücretsiz bir yazılımdır. <a href = "http://www.gnu.org/licenses/gpl- 2.0.txt "target =" _ blank "> GNU / GPL v2.0 Lisansı. </a>',
    'core_upgrade_error'        => 'Çekirdek yükseltmesi gerçekleştirilirken bir hata oluştu.',
    'correct_perms'             => 'Lütfen aşağıda tanımlanan sorunları düzeltin. Düzeltildikten sonra, ortamı doğrulamak için <b> Yeniden Kontrol Et </b> düğmesini kullanın.',
    'current'                   => 'Geçerli',
    'database_exists'           => 'Veritabanı zaten glFusion tabloları içeriyor. Yeni bir kurulum gerçekleştirmeden önce lütfen glFusion tablolarını kaldırın.',
    'database_info'             => 'Veritabanı Bilgisi',
    'db_hostname'               => 'Veritabanı Ana Bilgisayar Adı',
    'db_hostname_error'         => 'Veritabanı Ana Bilgisayar Adı boş olamaz.',
    'db_name'                   => 'Veritabanı Adı',
    'db_name_error'             => 'Veritabanı Adı boş olamaz.',
    'db_pass'                   => 'Veritabanı Şifresi',
    'db_table_prefix'           => 'Veritabanı Tablo Öneki',
    'db_type'                   => 'Veritabanı Tipi',
    'db_type_error'             => 'Veritabanı Türü seçilmelidir',
    'db_user'                   => 'Veritabanı Kullanıcı Adı',
    'db_user_error'             => 'Veritabanı Kullanıcı Adı boş olamaz.',
    'db_too_old'                => 'MySQL Sürümü çok eski - MySQL v5.0.15 veya sonraki bir sürüme sahip olmalısınız',
    'dbconfig_not_found'        => 'Db-config.php veya db-config.php.dist dosyası bulunamıyor. Lütfen private dizininizin doğru yolunu girdiğinizden emin olun.',
    'dbconfig_not_writable'     => 'Db-config.php dosyası yazılabilir değil. Lütfen web sunucusunun bu dosyaya yazma iznine sahip olduğundan emin olun.',
    'directory_permissions'     => 'Dizin İzinleri',
    'enabled'                   => 'Etkin',
    'env_check'                 => 'Çevre Kontrolü',
    'error'                     => 'Hata',
    'file_permissions'          => 'Dosya İzinleri',
    'file_uploads'              => 'GlFusion\'ın birçok özelliği dosya yükleme becerisini gerektirir, bu etkinleştirilmelidir.',
    'filemgmt'                  => 'Dosya Yöneticisi eklentisi yüklensin mi?',
    'filemgmt_desc'             => 'Dosya İndirme Yöneticisi. Kategoriye göre düzenlenmiş dosya indirmeleri sağlamanın kolay bir yolu.',
    'filesystem_check'          => 'Dosya Sistem Kontrolü',
    'forum'                     => 'Forum Eklentisi Yüklensin mi?',
    'forum_desc'                => 'Çevrimiçi bir topluluk forumu sistemi. Topluluk işbirliği ve etkileşim sağlar.',
    'hosting_env'               => 'Barındırma Ortamı Kontrolü',
    'install'                   => 'Kur',
    'install_heading'           => 'glFusion Kurulumu',
    'install_steps'             => 'YÜKLEME ADIMLARI',
    'language'                  => 'Dil',
    'language_task'             => 'Dil ve Görev',
    'libcustom_not_writable'    => 'lib-custom.php yazılabilir değil.',
    'links'                     => 'Bağlantılar Eklentisi Yüklensin mi?',
    'links_desc'                => 'Bir bağlantı yönetim sistemi. Kategorilere göre düzenlenmiş diğer ilginç sitelere bağlantılar sağlar.',
    'load_sample_content'       => 'Örnek Site İçeriği Yüklensin mi?',
    'mbstring_support'          => 'Çok baytlı dizi uzantısının yüklenmiş (etkinleştirilmiş) olması önerilir. Çok baytlı dizi desteği olmadan, bazı özellikler otomatik olarak devre dışı bırakılacaktır. Özellikle, makale WYSIWYG düzenleyicisindeki Dosya Tarayıcı çalışmayacaktır.',
    'mediagallery'              => 'Medya Galerisi Eklentisi Yüklensin mi?',
    'mediagallery_desc'         => 'Bir multimedya yönetim sistemi. Basit bir fotoğraf galerisi veya ses, video ve resimleri destekleyen sağlam bir medya yönetim sistemi olarak kullanılabilir.',
    'memory_limit'              => 'Sitenizde en az 64M belleğin etkinleştirilmiş olması önerilir.',
    'missing_db_fields'         => 'Lütfen gerekli tüm veritabanı alanlarını girin.',
    'new_install'               => 'Yeni Kurulum',
    'next'                      => 'İleri',
    'no_db'                     => 'Veritabanı mevcut görünmüyor.',
    'no_db_connect'             => 'Veritabanına bağlanılamıyor',
    'no_innodb_support'         => 'InnoDB ile MySQL\'i seçtiniz, ancak veritabanınız InnoDB dizinlerini desteklemiyor.',
    'no_migrate_glfusion'       => 'Mevcut bir glFusion sitesini taşıyamazsınız. Lütfen bunun yerine Yükseltme seçeneğini seçin.',
    'none'                      => 'Hiçbiri',
    'not_writable'              => 'YAZILAMAZ',
    'notes'                     => 'Notlar',
    'off'                       => 'Kapalı',
    'ok'                        => 'Tamam',
    'on'                        => 'Açık',
    'online_help_text'          => 'Belgeleri glFusion.org adresinden yükleyin',
    'online_install_help'       => 'Çevrimiçi Kurulum Yardımı',
    'open_basedir'              => 'Sitenizde <strong> open_basedir </strong> kısıtlamaları etkinleştirilmişse, yükleme sırasında izin sorunlarına neden olabilir. Aşağıdaki Dosya Sistemi Kontrolü tüm sorunları belirtmelidir.',
    'path_info'                 => 'Yol Bilgileri',
    'path_prompt'               => 'Private / dizine giden yol',
    'path_settings'             => 'Yol Ayarları',
    'perform_upgrade'           => 'Yükseltmeyi Gerçekleştirin',
    'php_req_version'           => 'glFusion, PHP 7.0.0 veya daha yeni bir sürümünü gerektirir.',
    'php_settings'              => 'PHP Ayarları',
    'php_version'               => 'PHP Versiyonu',
    'php_warning'               => 'Aşağıdaki öğelerden herhangi biri <span class = "no"> red </span> ile işaretlenmişse, glFusion sitenizde sorunlarla karşılaşabilirsiniz.  Bu PHP ayarlarından herhangi birinin değiştirilmesiyle ilgili bilgi için barındırma sağlayıcınıza danışın.',
    'plugin_install'            => 'Eklenti Kurulumu',
    'plugin_upgrade_error'      => '%s eklentisini yükseltirken bir sorun oluştu, daha fazla ayrıntı için lütfen error.log dosyasını kontrol edin. <br />',
    'plugin_upgrade_error_desc' => 'Aşağıdaki eklentiler yükseltilmedi. Daha fazla ayrıntı için lütfen error.log\'a bakın. <br />',
    'polls'                     => 'Anket Eklentisi Yüklensin mi?',
    'polls_desc'                => 'Çevrimiçi bir oylama sistemi. Site kullanıcılarınızın çeşitli konularda oy vermeleri için anketler sağlar.',
    'post_max_size'             => 'glFusion eklentileri, resimleri ve dosyaları yüklemenize olanak tanır. Maksimum gönderi boyutu için en az 8M izin vermelisiniz.',
    'previous'                  => 'Geri',
    'proceed'                   => 'Devam edin',
    'recommended'               => 'Önerilen',
    'register_globals'          => 'PHP\'nin <strong> register_globals </strong> etkinleştirilirse, güvenlik sorunları oluşturabilir.',
    'safe_mode'                 => 'PHP\'nin <strong> safe_mode </strong> özelliği etkinleştirilmişse, glFusion\'ın bazı işlevleri düzgün çalışmayabilir. Özellikle Medya Galerisi eklentisi.',
    'samplecontent_desc'        => 'İşaretliyse, bloklar, hikayeler ve statik sayfalar gibi örnek içerik yükleyin. <strong> Bu, yeni glFusion kullanıcıları için önerilir. </strong>',
    'select_task'               => 'Görev Seç',
    'session_error'             => 'Oturumunuz sona erdi.  Lütfen kurulum sürecini yeniden başlatın.',
    'setting'                   => 'Ayar',
    'securepassword'            => 'Yönetici Şifresi',
    'securepassword_error'      => 'Yönetici parolası boş olamaz',
    'site_admin_url'            => 'Site Yöneticisi URL\'si',
    'site_admin_url_error'      => 'Site Yöneticisi URL\'si boş olamaz.',
    'site_email'                => 'Site E-Mail',
    'site_email_error'          => 'Site E-postası boş olamaz.',
    'site_email_notvalid'       => 'Site E-postası, geçerli bir e-posta adresi değil.',
    'site_info'                 => 'Site Bilgisi',
    'site_name'                 => 'Site Adı',
    'site_name_error'           => 'Site Adı boş olamaz.',
    'site_noreply_email'        => 'Site No Reply Email',
    'site_noreply_email_error'  => 'Site No Reply Email boş olamaz.',
    'site_noreply_notvalid'     => 'No Reply Email geçerli bir e-posta adresi değil.',
    'site_slogan'               => 'Site Sloganı',
    'site_upgrade'              => 'Mevcut bir glFusion Sitesini Yükseltme',
    'site_url'                  => 'Site URL\'si',
    'site_url_error'            => 'Site URL\'si boş olamaz.',
    'siteconfig_exists'         => 'Mevcut bir siteconfig.php dosyası bulundu. Yeni bir kurulum gerçekleştirmeden önce lütfen bu dosyayı silin.',
    'siteconfig_not_found'      => 'Siteconfig.php dosyası bulunamıyor, bunun bir yükseltme olduğundan emin misiniz?',
    'siteconfig_not_writable'   => 'Siteconfig.php dosyası yazılabilir değil veya siteconfig.php\'nin depolandığı dizin yazılabilir değil. Lütfen devam etmeden önce bu sorunu düzeltin.',
    'sitedata_help'             => 'Açılır listeden kullanılacak veritabanı türünü seçin. Bu genellikle <strong> MySQL </strong> \'dir. Ayrıca, <strong> UTF-8 </strong> karakter kümesinin kullanılıp kullanılmayacağını seçin (bu genellikle çok dilli siteler için kontrol edilmelidir.) <br /> <br /> Veritabanı sunucusunun ana bilgisayar adını girin. Bu, web sunucunuzla aynı olmayabilir, bu nedenle emin değilseniz barındırma sağlayıcınıza danışın. <br /> <br /> Veritabanınızın adını girin. <strong> Veritabanı zaten mevcut olmalıdır. </strong> Veritabanınızın adını bilmiyorsanız, barındırma sağlayıcınızla iletişime geçin. <br /> <br /> Veritabanına bağlanmak için kullanıcı adını girin. Veritabanı kullanıcı adını bilmiyorsanız, barındırma sağlayıcınızla iletişime geçin. <br /> <br /> Veritabanına bağlanmak için şifreyi girin. Veritabanı şifresini bilmiyorsanız, barındırma sağlayıcınızla iletişime geçin. <br /> <br /> Veritabanı tabloları için kullanılacak bir tablo öneki girin. Bu, tek bir veritabanı kullanırken birden çok siteyi veya sistemi ayırmak için yararlıdır. <br /> <br /> Sitenizin adını girin. Site başlığında görüntülenecektir. Örneğin, glFusion veya Yahya KARAGÖZ. Merak etmeyin, daha sonra her zaman değiştirilebilir. <br /> <br /> Sitenizin sloganını girin. Site adının altındaki site başlığında görüntülenecektir. Örneğin, sinerji - istikrar - tarz. Merak etmeyin, daha sonra her zaman değiştirilebilir. <br /> <br /> Sitenizin ana e-posta adresini girin. Bu, varsayılan Yönetici hesabının e-posta adresidir. Merak etmeyin, daha sonra her zaman değiştirilebilir. <br /> <br /> Sitenizin yanıt yok e-posta adresini girin. Otomatik olarak yeni kullanıcı, şifre sıfırlama ve diğer bildirim e-postalarını göndermek için kullanılacaktır. Endişelenmeyin, daha sonra her zaman değiştirilebilir. <br /> <br /> Lütfen bunun sitenizin ana sayfasına erişmek için kullanılan web adresi veya URL olduğunu doğrulayın. <br /> <br /> Lütfen bunu onaylayın bu, sitenizin yönetici bölümüne erişmek için kullanılan web adresi veya URL\'dir.',
    'sitedata_missing'          => 'Girdiğiniz site verilerinde aşağıdaki sorunlar tespit edildi:',
    'system_path'               => 'Yol Ayarları',
    'unable_mkdir'              => 'Dizin oluşturulamıyor',
    'unable_to_find_ver'        => 'GlFusion sürümü belirlenemiyor.',
    'upgrade_error'             => 'Yükseltme Hatası',
    'upgrade_error_text'        => 'GlFusion kurulumunuzu yükseltirken bir hata oluştu.',
    'upgrade_steps'             => 'YÜKSELTME ADIMLARI',
    'upload_max_filesize'       => 'glFusion eklentileri, resimleri ve dosyaları yüklemenize olanak tanır. Yükleme boyutu için en az 8M izin vermelisiniz.',
    'use_utf8'                  => 'UTF-8 Kullan',
    'welcome_help'              => 'GlFusion CMS Kurulum Sihirbazına hoş geldiniz. Yeni bir glFusion sitesi kurabilir, mevcut bir glFusion sitesini yükseltebilirsiniz. <br /> <br /> Lütfen sihirbazın dilini ve gerçekleştirilecek görevi seçin, ardından <strong> İleri </strong> \'ye basın.',
    'wizard_version'            => 'v%s Kurulum Sihirbazı',
    'system_path_prompt'        => 'Sunucunuzda glFusion\'ın <strong> özel / </strong> dizinine giden tam ve mutlak yolu girin. <br /> <br /> Yeni bir kurulum için bu, <strong> db-config.php\'yi içeren dizindir..dist </strong> dosyası veya bir yükseltme ve mevcut <strong> db-config.php </strong> dosyası için. <br /> <br /> Dizin Örnekleri: <br /> / home / www / glfusion  / private / <br /> c: / www / glfusion / private / <br /> <br /> <strong> İpucu: </strong> Güvenlik açısından bakıldığında, özel / dizini için en çok istenen konum,  web kökü.  Web kökü, sitenizin kök Url\'si (http://www.yoursite.com/) ile ilgili web barındırıcınız tarafından sunulan dizindir. <br /> <br /> Görünüşe göre  <strong> public_html / </strong> <i> (<strong> özel / </strong> değil) </i> dizininiz: <br /> <br />%s <br /> <br />  Web barındırıcınız buna izin veriyorsa, özel / dizininizi web kökünün dışında bir yere yerleştirmenizi öneririz. <br /> <br /> Web barındırıcınız dosyaların web kökü dışına yerleştirilmesine izin vermiyorsa, lütfen takip edin  glFusion Belgelerinde <a href="https://www.glfusion.org/wiki/glfusion:install:pathsetting" target="_blank"> özel / dizini genel web alanına yükleme </a> ile ilgili talimatlar  Wiki. <br /> <br /> <strong> Gelişmiş Ayarlar </strong> bazı varsayılan yolları geçersiz kılmanıza olanak tanır.  Genellikle bu yolları değiştirmeniz veya belirtmeniz gerekmez, sistem bunları otomatik olarak belirleyecektir.',
    'advanced_settings'         => 'Gelişmiş Ayarlar',
    'log_path'                  => 'Günlük Dosyaları Yolu',
    'lang_path'                 => 'Dil Yolu',
    'backup_path'               => 'Yedekleme Yolu',
    'data_path'                 => 'Veri Yolu',
    'language_support'          => 'Dil Desteği',
    'language_pack'             => 'glFusion İngilizce olarak gönderilir, ancak kurulumdan sonra <a href="http://www.glfusion.org/filemgmt/viewcat.php?cid=18" target="_blank"> Dil Paketini </a> indirip yükleyebilirsiniz. > desteklenen tüm diller için dil dosyalarını içerir.',
    'libcustom_not_found'       => 'lib-custom.php.dist bulunamadı.',
    'no_db_driver'              => 'GlFusion\'ı kurmak için PHP\'de MySQL eklentisinin yüklü olması gerekir',
    'version_check'             => 'Güncellemeleri Kontrol Et',
    'check_for_updates'         => 'Herhangi bir glFusion CMS veya Eklenti güncellemesi olup olmadığını görmek için Komut ve Kontrol -> Güncelleme Kontrolüne gidin.',
    'quick_start'               => 'glFusion Hızlı Başlangıç ​​Kılavuzu',
    'quick_start_help'          => 'Lütfen <a href="https://www.glfusion.org/wiki/glfusion:quickstart" target="_blank"> glFusion CMS Hızlı Başlangıç ​​Kılavuzunu </a> ve tam <a href = "https: /  Yeni glFusion sitenizi yapılandırmayla ilgili ayrıntılar için /www.glfusion.org/wiki/ "target =" _ blank "> glFusion CMS Belgeleri </a> sitesine bakın.',
    'upgrade'                   => 'Yükselt',
    'support_resources'         => 'Destek Kaynakları',
    'plugins'                   => 'glFusion Eklentileri',
    'support_forums'            => 'glFusion Destek Forumları',
    'community_chat'            => 'Topluluk sohbeti @ Discord',
    'instruction_step'          => 'Talimatlar',
    'install_stepheading'       => 'Yeni Kurulum Görevleri',
    'install_doc_alert'         => 'Sorunsuz bir kurulum sağlamak için, devam etmeden önce lütfen <a href="https://www.glfusion.org/wiki/glfusion:installation" target="_blank"> Kurulum Belgelerini </a> okuyun.',
    'install_header'            => 'GlFusion\'ı kurmadan önce, birkaç önemli bilgiyi bilmeniz gerekecektir. Aşağıdaki bilgileri yazın. Aşağıdaki öğelerin her biri için ne yazacağınızdan emin değilseniz, lütfen sistem yöneticinize veya barındırma sağlayıcınıza başvurun.',
    'install_bullet1'           => 'Site&nbsp;<abbr title="Uniform Resource Locator">URL</abbr>',
    'install_bullet2'           => 'Veritabanı Sunucusu',
    'install_bullet3'           => 'Veritabanı Adı',
    'install_bullet4'           => 'Veritabanı Kullanıcı Kimliği',
    'install_bullet5'           => 'Veritabanı Şifresi',
    'install_bullet6'           => 'GlFusion Özel Dosyalarının Yolu. Burası db-config.php.dist dosyasının saklandığı yerdir. <strong> bu dosyalar İnternet üzerinden kullanılamaz, bu nedenle web kök dizininizin dışına gider. </strong> Bunları web köküne yüklemeniz gerekiyorsa, lütfen <a href = "https: // www.glfusion.org/wiki/glfusion:installation:web root "target =" _ blank "> Özel Dosyaları Web köküne Yükleme </a> talimatları, bu dosyaların güvenliğinin nasıl düzgün şekilde sağlanacağını öğrenmek için.',
    'install_doc_alert2'        => 'Daha ayrıntılı yükseltme talimatları için lütfen <a href="https://www.glfusion.org/wiki/glfusion:installation" target="_blank"> glFusion Kurulum Belgelerine </a> bakın.',
    'upgrade_heading'           => 'Önemli Yükseltme Bilgileri',
    'doc_alert'                 => 'Sorunsuz bir yükseltme süreci sağlamak için, devam etmeden önce lütfen <a href="https://www.glfusion.org/wiki/glfusion:upgrade" target="_blank"> Yükseltme Belgelerini </a> okuyun.',
    'doc_alert2'                => 'Daha ayrıntılı yükseltme talimatları için lütfen <a href="https://www.glfusion.org/wiki/glfusion:upgrade" target="_blank"> Yükseltmeyle ilgili glFusion Belgelerine </a> bakın.',
    'backup'                    => 'Yedekleme, Yedekleme, Yedekleme!',
    'backup_instructions'       => 'Mevcut kurulumunuzdan, içinde herhangi bir özel kod bulunan tüm dosyaları yedeklemek için son derece dikkatli olun. Mevcut kurulumunuzdaki değiştirilmiş temaları ve görüntüleri yedeklediğinizden emin olun.',
    'upgrade_bullet1'           => 'Mevcut glFusion Veritabanınızı yedekleyin (Komut ve Kontrol altındaki Veritabanı Yönetimi seçeneği).',
    'upgrade_bullet2'           => 'Varsayılan CMS dışında bir tema kullanıyorsanız, temanızın glFusion\'ı destekleyecek şekilde güncellendiğinden emin olun.  GlFusion\'ın düzgün çalışmasını sağlamak için özel temalarda yapılması gereken birkaç tema değişikliği vardır. &nbsp;<a  target="_blank" href="https://www.glfusion.org/wiki/glfusion:template_changes" title="glfusion:template_changes">Şablon Değişiklikleri </a>&nbsp; \'ni ziyaret ederek gerekli tüm şablon değişikliklerinin yapıldığını doğrulayın.',
    'upgrade_bullet3'           => 'Tema şablonlarından herhangi birini özelleştirdiyseniz,&nbsp; <a target="_blank" href="https://www.glfusion.org/wiki/glfusion:template_changes" title="glfusion:template_changes"> Şablon Değişiklikleri </a> &nbsp;Özelleştirmelerinizde herhangi bir güncelleme yapmanız gerekip gerekmediğini görmek için.',
    'upgrade_bullet4'           => 'Uyumlu olduklarından veya güncellenmeleri gerekip gerekmediğinden emin olmak için tüm üçüncü taraf eklentilerini kontrol edin.',
    'upgrade_bullet_title'      => 'Aşağıdakileri yapmanız önerilir:',
    'cleanup'                   => 'Eski Dosya Kaldırma',
    'obsolete_confirm'          => 'Dosya Temizleme Onayı',
    'remove_skip_warning'       => 'Eski dosyaları silmeyi atlamak istediğinizden emin misiniz? Bu dosyalara artık ihtiyaç yoktur ve güvenlik nedeniyle kaldırılmalıdır. Otomatik kaldırmayı atlamayı seçerseniz, lütfen bunları manuel olarak kaldırmayı düşünün.',
    'removal_failure'           => 'Kaldırma Hataları',
    'removal_fail_msg'          => 'Aşağıdaki dosyaları manuel olarak silmeniz gerekecek. Ayrıntılı bir liste için <a href="https://www.glfusion.org/wiki/doku.php?id=glfusion:upgrade:obsolete" target="_blank"> glFusion Wiki - Eski Dosyalar </a> sayfasına bakın dosya kaldırılacak.',
    'removal_success'           => 'Eski Dosyalar Silindi',
    'removal_success_msg'       => 'Tüm eski dosyalar başarıyla kaldırıldı. Yükseltmeyi bitirmek için <b> Tamamla </b> \'yı seçin.',
    'remove_obsolete'           => 'Eski Dosyaları Kaldır',
    'remove_instructions'       => '<p> Her glFusion sürümüyle birlikte, güncellenen ve bazı durumlarda glFusion sisteminden kaldırılan dosyalar vardır. Güvenlik açısından, eski, kullanılmayan dosyaları kaldırmak önemlidir. Yükseltme Sihirbazı, isterseniz eski dosyaları kaldırabilir, aksi takdirde manuel olarak silmeniz gerekir. </p> <p> Dosyaları manuel olarak silmek isterseniz - lütfen kaldırılacak eski dosyaların bir listesini almak için <a href = "https: //www.glfusion.org/wiki/doku.php?id=glfusion:upgrade:obsolete "target =" _ blank "> glFusion Wiki - Eski Dosyalar </a>. Yükseltme işlemini tamamlamak için aşağıdaki <span class="uk-text-bold"> Atla </span> seçeneğini seçin. </p> <p> Yükseltme Sihirbazının dosyaları otomatik olarak silmesi için lütfen <b> Dosyaları Sil </b> yükseltmeyi tamamlamak için aşağıya.',
    'complete'                  => 'Tamamlandı',
    'delete_files'              => 'Dosyaları sil',
    'cancel'                    => 'Vazgeç',
    'show_files_to_delete'      => 'Silinecek Dosyaları Göster',
    'skip'                      => 'Atla',
    'no_utf8'                   => 'UTF-8 kullanmayı seçtiniz (önerilen), ancak veritabanı UTF-8 harmanlama ile yapılandırılmamış. Lütfen veritabanını uygun UTF-8 harmanlamasıyla oluşturun. Daha fazla bilgi için lütfen glFusion Documentation Wiki\'deki <a href="https://www.glfusion.org/wiki/glfusion:installation:database" target="_blank"> Veritabanı Kurulum Kılavuzu </a> \'na bakın.',
    'no_check_utf8'             => 'UTF-8 kullanmayı seçmediniz (önerilen), ancak veritabanı UTF-8 harmanlama ile yapılandırılmış. Lütfen yükleme ekranında UTF-8 seçeneğini seçin. Daha fazla bilgi için lütfen glFusion Documentation Wiki\'deki <a href="https://www.glfusion.org/wiki/glfusion:installation:database" target="_blank"> Veritabanı Kurulum Kılavuzu </a> \'na bakın.',
    'ext_installed'             => 'Kurulu',
    'ext_missing'               => 'Eksik',
    'ext_required'              => 'Zorunlu',
    'ext_optional'              => 'İsteğe bağlı',
    'ext_required_desc'         => 'pHP\'ye yüklenmeli',
    'ext_optional_desc'         => 'pHP\'ye yüklenmelidir - Eksik uzantı, glFusion\'ın bazı özelliklerini etkileyebilir.',
    'ext_good'                  => 'doğru bir şekilde kuruldu.',
    'ext_heading'               => 'PHP uzantıları',
    'curl_extension'            => 'Curl Uzantısı',
    'ctype_extension'           => 'Ctype Uzantısı',
    'date_extension'            => 'Tarih Uzantısı',
    'filter_extension'          => 'Filter Uzantısı',
    'gd_extension'              => 'GD Graphics Uzantısı',
    'gettext_extension'         => 'Gettext Uzantısı',
    'hash_extension'            => 'Karma Mesaj Özeti Uzantısı',
    'json_extension'            => 'Json Uzantısı',
    'mbstring_extension'        => 'Multibyte (mbstring) Uzantısı',
    'mysqli_extension'          => 'MySQLi Uzantısı',
    'mysql_extension'           => 'MySQL Sürücüsü (either pdo_mysql or mysqli)',
    'openssl_extension'         => 'OpenSSL Uzantısı',
    'session_extension'         => 'Oturum Uzantısı',
    'xml_extension'             => 'XML Uzantısı',
    'zlib_extension'            => 'zlib Uzantısı',
    'required_php_ext'          => 'Gerekli PHP Uzantıları',
    'all_ext_present'           => 'Tüm gerekli ve isteğe bağlı PHP uzantıları düzgün şekilde yüklendi.',
    'short_open_tags'           => 'PHP\'nin <b> short_open_tag </b> kapalı olmalıdır.',
    'max_execution_time'        => 'glFusion, minimum 30 saniyelik PHP varsayılan değerini önerir, ancak eklenti yüklemeleri ve diğer işlemler barındırma ortamınıza bağlı olarak bundan daha uzun sürebilir.  Safe_mode (yukarıda) Kapalı ise, php.ini dosyanızdaki <b> max_execution_time </b> değerini değiştirerek bunu artırabilirsiniz.',
);

// +---------------------------------------------------------------------------+
// success.php

$LANG_SUCCESS = array(
    0 => 'Kurulum tamamlandı',
    1 => 'glFusion kurulumu ',
    2 => ' tamamlandı!',
    3 => 'Tebrikler, başardın ',
    4 => ' glFusion. Lütfen aşağıda görüntülenen bilgileri okumak için bir dakikanızı ayırın.',
    5 => 'Yeni glFusion sitenize giriş yapmak için lütfen bu hesabı kullanın:',
    6 => 'Kullanıcı Adı:',
    7 => 'Yönetici', // do not translate
    8 => 'Şifre:',
    9 => 'şifre', // do not translate
    10 => 'Güvenlik Uyarısı',
    11 => 'Yapmayı unutma',
    12 => 'bir şeyler',
    13 => 'Kurulum dizinini kaldırın veya yeniden adlandırın,',
    14 => 'Değiştir',
    15 => 'hesap Şifresi.',
    16 => 'İzinleri ayarlayın',
    17 => 've',
    18 => 'geri dön',
    19 => '<strong> Not: </strong> Güvenlik modeli değiştiğinden, yeni sitenizi yönetmek için ihtiyacınız olan haklara sahip yeni bir hesap oluşturduk.  Bu yeni hesabın kullanıcı adı <b> NewAdmin </b> ve şifre <b> password </b>',
    20 => 'kurulu',
    21 => 'yükseltildi',
    22 => 'Kurulum Dizinini Kaldır',
    23 => 'Sitenizdeki install / dizinini kaldırmanız veya yeniden adlandırmanız önemlidir. Kurulum dosyalarını yerinde bırakmak bir güvenlik sorunudur. Tüm Kurulum dosyalarını otomatik olarak kaldırmak için lütfen <strong> Yükleme Dosyalarını Kaldır </strong> düğmesini seçin. Kurulum dosyalarını kaldırmamayı seçerseniz, lütfen <strong> admin / install / </strong> dizinini manuel olarak kolayca tahmin edilemeyecek bir adla yeniden adlandırın.',
    24 => 'Yükleme Dosyalarını Kaldır',
    25 => 'Ne var ne yok',
    26 => 'GlFusion\'ın bu sürümü hakkında önemli bilgiler için glFusion Wiki - <a href="https://www.glfusion.org/wiki/glfusion:upgrade:whatsnew" target="_blank"> Yenilikler Bölümü </a> \'ne bakın.',
    27 => 'Sitenize Gidin',
    28 => 'Kurulum Dosyaları Kaldırıldı',
    29 => 'Dosyaları Kaldırmada Hata',
    30 => 'Kurulum Dosyalarını Kaldırma Hatası - Lütfen bunları manuel olarak kaldırın.',
    31 => 'Lütfen yukarıdaki parolanın bir kaydını yapın - yeni sitenize giriş yapmak için buna sahip olmanız gerekir.',
    32 => 'Şifrenizi bir yere not ettiniz mi?',
    33 => 'Siteye Devam Et',
    34 => 'Vazgeç',
);
?>