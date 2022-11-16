<?php
/**
* glFusion CMS
*
* UTF-8 Language File for FileMgt Plugin
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2008-2021 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*  Based on prior work Copyright (C) 2004 by the following authors:
*   Consult4Hire Inc.
*   Blaine Lang  - blaine AT portalparts DOT com
*
*/

if (!defined ('GVERSION')) {
    die ('This file cannot be used on its own.');
}

$LANG_FM00 = array (
    'access_denied'     => 'Erişim Engellendi',
    'access_denied_msg' => 'Bu Sayfaya Sadece Kök Kullanıcılar Erişebilir.  Kullanıcı adınız ve IP\'niz kaydedildi.',
    'admin'             => 'Eklenti Yöneticisi',
    'install_header'    => 'Eklentiyi Kur / Kaldır',
    'installed'         => 'Eklenti ve Blok artık yüklendi, <p> <i> Keyfini Çıkarın, <br> <a href="MAILTO:support@glfusion.org"> glFusion Ekibi </a> </i>',
    'uninstalled'       => 'Eklenti Yüklü Değil',
    'install_success'   => 'Kurulum Başarılı <p> <b> Sonraki Adımlar </b>:
         <ol> <li> Eklenti yapılandırmasını tamamlamak için Filemgmt Admin\'i kullanın </ol>
         <p> Daha fazla bilgi için <a href="%s"> Yükleme Notlarını </a> inceleyin.',
    'install_failed'    => 'Kurulum Başarısız - Nedenini öğrenmek için hata günlüğünüze bakın.',
    'uninstall_msg'     => 'Eklenti Başarıyla Kaldırıldı',
    'install'           => 'Kur',
    'uninstall'         => 'Kaldır',
    'editor'            => 'Eklenti Düzenle',
    'warning'           => 'Kaldırma Uyarısı',
    'enabled'           => '<p style="padding: 15px 0px 5px 25px;"> Eklenti yüklendi ve etkinleştirildi. <br> Kaldırmak istiyorsanız önce devre dışı bırakın. </p><div style="padding:5px 0px 5px 25px;"><a href ="'.$_CONF['site_admin_url'].'/plugins.php"> Eklenti Düzenleyicisi </a> </div',
    'WhatsNewLabel'     => 'Dosyalar',
    'WhatsNewPeriod'    => ' son %s Gün',
    'new_upload'        => 'Yeni Dosya gönderildi ',
    'new_upload_body'   => 'Adresindeki yükleme kuyruğuna yeni bir dosya gönderildi ',
    'details'           => 'Dosya Detayları',
    'filename'          => 'Dosya Adı',
    'uploaded_by'       => 'Yükleyen',
    'not_found'         => 'İndirme Bulunamadı',
);

// Admin Navbar
$LANG_FM02 = array(
    'instructions' => 'Bir dosyayı değiştirmek veya silmek için aşağıdaki dosyaların düzenleme simgesine tıklayın. Kategorileri görüntülemek veya değiştirmek için yukarıdaki Kategoriler seçeneğini seçin.',
    'nav1'  => 'Ayarlar',
    'nav2'  => 'Kategoriler',
    'nav3'  => 'Dosya Ekle',
    'nav4'  => 'İndirilenler (%s)',
    'nav5'  => 'Bozuk Dosyalar (%s)',
    'edit'  => 'Düzenle',
    'file'  => 'Dosya Adı',
    'category' => 'Kategori Adı',
    'version' => 'Sürüm',
    'size'  => 'Boyut',
    'date' => 'Tarih',
);

$LANG_FILEMGMT = array(
    'newpage'               => "Yeni Sayfa",
    'adminhome'             => "Yönetim AnaSayfa",
    'plugin_name'           => "Dosyalar Yönetimi",
    'searchlabel'           => "İndirilenler",
    'searchlabel_results'   => "İndirme SonuçlarI",
    'downloads'             => "İndirilenler",
    'report'                => "En çok indirilenler",
    'usermenu1'             => "İndirilenler",
    'usermenu2'             => "&nbsp;&nbsp;En Çok Oylananlar",
    'usermenu3'             => "Dosya Yükle",
    'admin_menu'            => "Filemgmt Yöneticisi",
    'writtenby'             => "Yazan",
    'date'                  => "Son Güncelleme",
    'title'                 => "Başlık",
    'content'               => "İçerik",
    'hits'                  => "Hitler",
    'Filelisting'           => "Dosya Listesi",
    'DownloadReport'        => "Tek dosya için İndirme Geçmişi",
    'StatsMsg1'             => "Depodaki Erişilen İlk On Dosya",
    'StatsMsg2'             => "Görünüşe göre bu sitede filemgmt eklentisi için tanımlanmış dosya yok veya hiç kimse bunlara erişmemiş.",
    'usealtheader'          => "Alt kullanın. Üstbilgi",
    'url'                   => "URL",
    'edit'                  => "Düzenle",
    'lastupdated'           => "Son Güncelleme",
    'pageformat'            => "Sayfa Biçimi",
    'leftrightblocks'       => "Sol ve Sağ Bloklar",
    'blankpage'             => "Boş Sayfa",
    'noblocks'              => "Bloklar Olmasın",
    'leftblocks'            => "Sol Bloklar",
    'addtomenu'             => 'Menüye Ekle',
    'label'                 => 'Etiket',
    'nofiles'               => 'Depomuzdaki dosya sayısı (İndirilenler)',
    'save'                  => 'Kaydet',
    'preview'               => 'Ön İzleme',
    'delete'                => 'Sil',
    'cancel'                => 'Vazgeç',
    'access_denied'         => 'Erişim Engellendi',
    'invalid_install'       => 'Birisi Dosya Yönetimi yükleme / kaldırma sayfasına yasadışı olarak erişmeye çalıştı.  Kullanıcı kimliği: ',
    'start_install'         => 'Filemgmt Eklentisini kurmaya çalışıyor',
    'start_dbcreate'        => 'Filemgmt eklentisi için tablolar oluşturulmaya çalışılıyor',
    'install_skip'          => '... filemgmt.cfg uyarınca atlandı',
    'access_denied_msg'     => 'Yasadışı bir şekilde Dosya Yönetimi yönetim sayfalarına erişmeye çalışıyorsunuz.  Lütfen bu sayfaya yasa dışı olarak erişmeye yönelik tüm girişimlerin günlüğe kaydedildiğini unutmayın',
    'installation_complete' => 'Kurulum Tamamlandı',
    'installation_complete_msg' => 'GlFusion için Dosya Yönetimi eklentisinin veri yapıları veritabanınıza başarıyla yüklendi!  Bu eklentiyi kaldırmanız gerekirse, lütfen bu eklenti ile birlikte gelen README belgesini okuyun.',
    'installation_failed'   => 'Kurulum Başarısız Oldu',
    'installation_failed_msg' => 'Dosya Yönetimi eklentisinin kurulumu başarısız oldu.  Teşhis bilgileri için lütfen glFusion error.log dosyanıza bakın',
    'system_locked'         => 'Sistem Kilitli',
    'system_locked_msg'     => 'Dosya Yönetimi eklentisi zaten yüklenmiş ve kilitlenmiştir.  Bu eklentiyi kaldırmaya çalışıyorsanız, lütfen bu eklenti ile birlikte gelen README belgesini okuyun',
    'uninstall_complete'    => 'Kaldırma İşlemi Tamamlandı',
    'uninstall_complete_msg' => 'Dosya Yönetimi eklentisinin veri yapıları glFusion veritabanınızdan başarıyla kaldırıldı <br> <br> Dosya deponuzdaki tüm dosyaları manuel olarak kaldırmanız gerekecek.',
    'uninstall_failed'      => 'Kaldırma Başarısız.',
    'uninstall_failed_msg'  => 'Dosya Yönetimi eklentisi kaldırılamadı.  Teşhis bilgileri için lütfen glFusion error.log dosyanıza bakın',
    'install_noop'          => 'Eklenti Kurulumu',
    'install_noop_msg'      => 'Filemgmt eklenti kurulumu yürütüldü ancak yapacak bir şey yok. <br> <br> Eklenti install.cfg dosyanızı kontrol edin.',
    'all_html_allowed'      => 'Tüm HTML kodları izinli',
    'no_new_files'          => 'Yeni dosya yok',
    'no_comments'           => 'Hiç yeni yorum yok',
    'more'                  => '<em> daha fazla ... </em>',
    'newly_uploaded'        => 'Yeni Yüklendi',
    'click_to_view'         => 'Görmek için buraya tıkla',
    'no_file_uploaded'      => 'Hiç Dosya Yüklenmedi',
    'description'           => 'Açıklama',
    'category'              => 'Kategori',
    'err_req_fields'        => 'Bazı zorunlu alanlar sağlanmadı',
    'go_back'               => 'Geri Git',
    'err_demomode'          => 'Yüklemeler demo modunda devre dışı bırakılmıştır',
    'edit_category'         => 'Kategori Düzenle',
    'create_category'       => 'Kategori Oluştur',
    'can_view'              => 'Görüntüleyebilir',
    'can_upload'            => 'Dosya Yükleyebilir',
    'delete_category'       => 'Kategoriyi Sil',
    'new_category'          => 'Yeni Kategori',
    'new_file'              => 'Yeni Dosya',
    'remote_ip'             => 'Uzak IP',
    'back_to_listing'       => 'Listeye geri dön',
    'remote_url'            => 'Uzak URL',
    'file_missing'          => 'Dosya Kayıp',
    'submitterview'     => 'Logged-In Submitters may view regardless of view access',
);

$LANG_FILEMGMT_ERRORS = array(
    "1101" => "Yükleme Onayı Hatası: Geçici dosya bulunamadı. error.log'u kontrol et",
    "1102" => "Dosya Gönderme Hatası: Geçici dosya deposuna dosya oluşturulamadı. error.log'u kontrol et",
    "1103" => "Sağladığınız indirme bilgisi zaten veritabanında mevcut!",
    "1104" => "İndirme bilgisi tamamlanmadı - Yeni dosya için bir başlık girmeniz gerekiyor",
    "1105" => "İndirme bilgisi tamamlanmadı - Yeni dosya için bir açıklama girmeniz gerekiyor",
    "1106" => "Dosya Ekleme Hatası: Yeni dosya oluşturulamadı. error.log'u kontrol et",
    "1107" => "Dosya Ekleme Hatası: Geçici dosya bulunamadı. error.log'u kontrol et",
    "1108" => "Yinelenen dosya - dosya deposunda zaten mevcut",
    "1109" => "Bu dosya türüne izin verilmez",
    "1110" => "Yüklenen dosya için bir kategori tanımlamalı ve seçmelisiniz",
    "1111" => "Dosya Boyutu, site tarafından izin verilen maksimum %s boyutunu aşıyor",
    "9999" => "Bilinmeyen Hata"
);

$LANG_FILEMGMT_AUTOTAG = array(
    'desc_file'                 => 'Bağlantı: bir Dosya indirme ayrıntı sayfasına.  bağlantı_metni varsayılan olarak dosya başlığına ayarlanır.  kullanım: [dosya: <i> dosya_kimliği </i> {bağlantı_metni}]',
    'desc_file_download'        => 'Bağlantı: doğrudan Dosya indirme.  bağlantı_metni varsayılan olarak dosya başlığına ayarlanır.  kullanım: [file_download: <i> file_id </i> {link_text}]',
);


// Localization of the Admin Configuration UI
$LANG_configsections['filemgmt'] = array(
    'label'                 => 'Dosya Yöneticisi',
    'title'                 => 'Dosya Yöneticisi Yapılandırması'
);
$LANG_confignames['filemgmt'] = array(
    'whatsnew'              => 'WhatsNew Listing\'i Etkinleştir',
    'perpage'               => 'Sayfa Başına Görüntülenen İndirmeler',
    'popular_download'      => 'Popüler Olacak Hitler',
    'newdownloads'          => 'Üst Sayfadaki Yeni Olarak İndirilenlerin Sayısı',
    'trimdesc'              => 'Listedeki Dosya Açıklamalarını Kırp',
    'dlreport'              => 'İndirme Raporuna Erişimi Kısıtla',
    'selectpriv'            => 'Erişimi Yalnızca \'Oturum Açmış Kullanıcılar\' Grubuna Sınırla',
    'uploadselect'          => 'Oturum Açmış Kullanıcılar İçin Yüklemelere İzin Ver',
    'uploadpublic'          => 'Anonim Kullanıcılar İçin Yüklemelere İzin Ver',
    'useshots'              => 'Kategori Görsellerini Görüntüle',
    'shotwidth'             => 'Küçük Resim Genişliği',
    'Emailoption'           => 'Dosya Onaylandıysa E-posta Gönder',
    'FileStore'             => 'Dosyaları Depolamak İçin Dizin',
    'SnapStore'             => 'Dosya Küçük Resimlerini Saklamak için Dizin',
    'SnapCat'               => 'Kategori Küçük Resimlerini Kaydetme Dizini',
    'FileStoreURL'          => 'Dosyaların URL\'si',
    'FileSnapURL'           => 'Küçük Resim Dosyasının URL\'si',
    'SnapCatURL'            => 'Küçük Resim Kategorisinin URL\'si',
    'whatsnewperioddays'    => 'What\'s New Günleri',
    'whatsnewtitlelength'   => 'What\'s New Başlık Uzunluğu',
    'showwhatsnewcomments'  => 'What\'s New Bloğunda Yorumları Göster',
    'numcategoriesperrow'   => 'Satır Başına Kategori',
    'numsubcategories2show' => 'Satır Başına Alt Kategori',
    'outside_webroot'       => 'Dosyaları Web Kökü Dışında Depolama',
    'enable_rating'         => 'Beğenilmeleri Etkinleştir',
    'displayblocks'         => 'GlFusion Bloklarını Görüntüleme',
    'silent_edit_default'   => 'Sessiz Düzenleme Varsayılanı',
    'extensions_map'        => 'İndirmeler için kullanılan uzantılar',
    'EmailOption'           => 'Dosya Onaylandıysa E-posta Gönder?',
);
$LANG_configsubgroups['filemgmt'] = array(
    'sg_main'               => 'Ana Ayarlar'
);
$LANG_fs['filemgmt'] = array(
    'fs_public'             => 'Genel FileMgmt Ayarları',
    'fs_admin'              => 'FileMgmt Yönetici Ayarları',
    'fs_permissions'        => 'Varsayılan İzinler',
    'fm_access'             => 'FileMgmt Erişim Kontrolü',
    'fm_general'            => 'Dosya Yöneticisi Genel Ayarlar',
);
// Note: entries 0, 1 are the same as in $LANG_configselects['Core']
$LANG_configSelect['filemgmt'] = array(
    0 => array(1=>'Doğru', 0=>'False'),
    1 => array(true=>'Doğru', false=>'False'),
    2 => array(5 => ' 5', 10 => '10', 15 => '15', 20 => '20', 25 => '25',30 => '30',50 => '50'),
    3 => array(0=>'Sol Bloklar', 1=>'Sağ Bloklar', 2=>'Sol ve Sağ Bloklar', 3=>'Hiçbiri')
);

$PLG_filemgmt_MESSAGE1 = 'Filemgmt Eklenti Kurulumu Durduruldu <br> Dosya: plugins / filemgmt / filemgmt.php yazılabilir değil';
$PLG_filemgmt_MESSAGE3 = 'Bu eklenti glFusion Sürüm 1.0 veya üzerini gerektirir, yükseltme iptal edildi.';
$PLG_filemgmt_MESSAGE4 = 'Eklenti sürüm 1.5 kodu tespit edilmedi - yükseltme iptal edildi.';
$PLG_filemgmt_MESSAGE5 = 'Filemgmt Eklenti Yükseltmesi Durduruldu <br> Mevcut eklenti sürümü 1.3 değil';

// Language variables used by the plugin - general users access code.

define("_MD_THANKSFORINFO","Bilgi için teşekkürler. İsteğinizi kısa süre içinde inceleyeceğiz.");
define("_MD_BACKTOTOP","En Çok İndirilenlere Geri Dön");
define("_MD_THANKSFORHELP","Bu direktörün bütünlüğünü korumaya yardımcı olduğunuz için teşekkür ederiz.");
define("_MD_FORSECURITY","Güvenlik nedeniyle, kullanıcı adınız ve IP adresiniz geçici olarak kaydedilecektir.");

define("_MD_SEARCHFOR","Arama kelimesi");
define("_MD_MATCH","Eşleşme");
define("_MD_ALL","TÜMÜ");
define("_MD_ANY","Hiçbiri");
define("_MD_NAME","Ad");
define("_MD_DESCRIPTION","Açıklama");
define("_MD_SEARCH","Ara");

define("_MD_MAIN","Ana");
define("_MD_SUBMITFILE","Dosya Gönder");
define("_MD_POPULAR","Popüler");
define("_MD_POP", "Pop");   // abbrevision for listing badge
define("_MD_NEW","Yeni");
define("_MD_TOPRATED","En Beğenilenler");

define("_MD_NEWTHISWEEK","Bu Hafta Yeni");
define("_MD_UPTHISWEEK","Bu Hafta Güncellenen");

define("_MD_POPULARITYLTOM","Popülerlik (En Azdan En Çok Hit'e)");
define("_MD_POPULARITYMTOL","Popülerlik (En Çoktan En Aza)");
define("_MD_TITLEATOZ","Başlık (A-Z)");
define("_MD_TITLEZTOA","Başlık (Z-A)");
define("_MD_DATEOLD","Tarih (Önce Eski Dosyalar)");
define("_MD_DATENEW","Tarih (Önce Yeni Dosyalar)");
define("_MD_RATINGLTOH","Puan (En Düşük Puandan En Yüksek Puana Kadar)");
define("_MD_RATINGHTOL","Puan (En Yüksek Puandan En Düşük Puana)");

define("_MD_NOSHOTS","Küçük Resim Yok");
define("_MD_EDITTHISDL","Bu İndirmeyi Düzenle");

define("_MD_LISTINGHEADING","<b> Dosya Listeleme: Veritabanımızda %s dosya var </b>");
define("_MD_LATESTLISTING","<b> Son Liste: </b>");
define("_MD_DESCRIPTIONC","Açıklama:");
define("_MD_EMAILC","E-Posta: ");
define("_MD_CATEGORYC","Kategori: ");
define("_MD_LASTUPDATEC","Son Güncelleme: ");
define("_MD_DLNOW","Şimdi İndir!");
define("_MD_VERSION","Ver");
define("_MD_SUBMITDATE","Tarih");
define("_MD_DLTIMES","%s kez indirildi");
define("_MD_FILESIZE","Dosya Boyutu");
define("_MD_SUPPORTEDPLAT","Desteklenen Platformlar");
define("_MD_HOMEPAGE","Ana Sayfa");
define("_MD_HITSC","Hitler: ");
define("_MD_RATINGC","Puanlama: ");
define("_MD_ONEVOTE","1 oy");
define("_MD_NUMVOTES","(%s)");
define("_MD_NOPOST","Yok");
define("_MD_NUMPOSTS","%s oy");
define("_MD_COMMENTSC","Yorumlar: ");
define ("_MD_ENTERCOMMENT", "İlk yorumu bırakın");
define("_MD_RATETHISFILE","Bu Dosyayı Oyla");
define("_MD_MODIFY","Değiştir");
define("_MD_REPORTBROKEN","Bozuk Bağlantı Bildirin");
define("_MD_TELLAFRIEND","Arkadaşınıza Tavsiye Edin");
define("_MD_VSCOMMENTS","Yorumları Görüntüle / Gönder");
define("_MD_EDIT","Düzenle");

define("_MD_THEREARE","Veritabanımızda %s dosya var");
define("_MD_LATESTLIST","Son Liste");

define("_MD_REQUESTMOD","İndirme Değişikliği İsteğinde Bulun");
define("_MD_FILE","Dosya");
define("_MD_FILEID","Dosya Kimliği: ");
define("_MD_FILETITLE","Başlık: ");
define("_MD_DLURL","İndirme URL'si: ");
define("_MD_HOMEPAGEC","Ana Sayfa: ");
define("_MD_VERSIONC","Sürüm: ");
define("_MD_FILESIZEC","Dosya Boyutu: ");
define("_MD_NUMBYTES","%s bayt");
define("_MD_PLATFORMC","Platform: ");
define("_MD_CONTACTEMAIL","İletişim E-posta: ");
define("_MD_SHOTIMAGE","Küçük Resim: ");
define("_MD_SENDREQUEST","İstek Gönder");

define("_MD_VOTEAPPRE","Oyunuz takdir ediliyor.");
define("_MD_THANKYOU","%s için burada oy vermeye zaman ayırdığınız için teşekkür ederiz"); // %s is your site name
define("_MD_VOTEFROMYOU","Sizin gibi kullanıcılardan gelen bilgiler, diğer ziyaretçilerin hangi dosyayı indireceklerine daha iyi karar vermelerine yardımcı olacaktır.");
define("_MD_VOTEONCE","Lütfen aynı kaynağa birden fazla oy vermeyin.");
define("_MD_RATINGSCALE","Ölçek 1 - 10'dur, 1 zayıf ve 10 mükemmeldir.");
define("_MD_BEOBJECTIVE","Lütfen tarafsız olun, eğer herkes 1 veya 10 alırsa, derecelendirmeler pek kullanışlı olmaz.");
define("_MD_DONOTVOTE","Kendi kaynağınız için oy vermeyin.");
define("_MD_RATEIT","Oyla!");

define("_MD_INTFILEAT","%s konumunda İlginç İndirme Dosyaları"); // %s is your site name
define("_MD_INTFILEFOUND","İşte %s adresinde bulduğum ilginç bir indirme dosyası"); // %s is your site name

define("_MD_RECEIVED","İndirme bilgilerinizi aldık. Teşekkürler!");
define("_MD_WHENAPPROVED","&apos; Onaylandığında bir &apos; E-posta alacaksınız.");
define("_MD_SUBMITONCE","Dosyanızı / komut dosyanızı yalnızca bir kez gönderin.");
define("_MD_APPROVED", "Dosyanız onaylandı");
define("_MD_ALLPENDING","Tüm dosya / komut dosyası bilgileri doğrulama için gönderilir.");
define("_MD_DONTABUSE","Kullanıcı adı ve IP kaydedilir, bu yüzden lütfen sistemi kötüye kullanmayın.");
define("_MD_TAKEDAYS","Dosyanızın / komut dosyanızın veritabanımıza eklenmesi birkaç gün sürebilir.");
define("_MD_FILEAPPROVED", "Dosyanız dosya havuzuna eklendi");

define("_MD_RANK","Sıra");
define("_MD_CATEGORY","Kategori");
define("_MD_HITS","Hitler");
define("_MD_RATING","Puanlama");
define("_MD_VOTE","Oy");

define("_MD_SEARCHRESULT4","<b>%s </b> için arama sonuçları:");
define("_MD_MATCHESFOUND","%s eşleşme(ler) bulundu.");
define("_MD_SORTBY","Sırala:");
define("_MD_TITLE","Başlık");
define("_MD_DATE","Tarih");
define("_MD_POPULARITY","Popülerlik");
define("_MD_CURSORTBY","Dosyalar şuna göre sıralandır: ");
define("_MD_FOUNDIN","İçinde Bulundu:");
define("_MD_PREVIOUS","Önceki");
define("_MD_NEXT","İleri");
define("_MD_NOMATCH","Sorgunuzla hiçbir eşleşme bulunamadı");

define("_MD_TOP10","%s Top 10"); // %s is a downloads category name
define("_MD_CATEGORIES","Kategoriler");

define("_MD_SUBMIT","Gönder");
define("_MD_CANCEL","Vazgeç");

define("_MD_BYTES","Bayt");
define("_MD_ALREADYREPORTED","Bu kaynak için zaten bozuk dosya raporu gönderdiniz.");
define("_MD_MUSTREGFIRST","Üzgünüz, bu eylemi gerçekleştirme izniniz yok. <br> Lütfen önce kayıt olun veya giriş yapın!");
define("_MD_NORATING","Derecelendirme seçilmedi.");
define("_MD_CANTVOTEOWN","Gönderdiğiniz kaynağa oy veremezsiniz. <br> Tüm oylar günlüğe kaydedilir ve incelenir.");

// Language variables used by the plugin - Admin code.

define("_MD_RATEFILETITLE","Dosya derecelendirmenizi kaydedin");
define("_MD_ADMINTITLE","Dosya Yönetim Yönetimi");
define("_MD_UPLOADTITLE","Dosya Yönetimi - Yeni dosya ekle");
define("_MD_CATEGORYTITLE","Dosya Listeleme - Kategori Görünümü");
define("_MD_DLCONF","İndirme Yapılandırması");
define("_MD_GENERALSET","Yapılandırma Ayarları");
define("_MD_ADDMODFILENAME","Yeni dosya ekle");
define ("_MD_ADDCATEGORYSNAP", 'İsteğe Bağlı Resim: <div style="font-size:8pt;"> Yalnızca Üst Düzey Kategoriler </div>');
define ("_MD_ADDIMAGENOTE", '<span style="font-size:8pt;"> Resim yüksekliği 50 olarak yeniden boyutlandırılacak </span>');
define("_MD_ADDMODCATEGORY","<b> Kategoriler: </b> Kategori Ekleme, Değiştirme ve Silme");
define("_MD_DLSWAITING","Doğrulama Bekleyen İndirmeler");
define("_MD_BROKENREPORTS","Bozuk Bağlantı Raporu");
define("_MD_MODREQUESTS","Bilgi Değişiklik Taleplerini İndirin");
define("_MD_EMAILOPTION","Dosya Onaylandıysa E-posta Gönder: ");
define("_MD_COMMENTOPTION","Yorumu Etkinleştir:");
define("_MD_SUBMITTER","Gönderen: ");
define("_MD_DOWNLOAD","İndir");
define("_MD_FILELINK","Dosya Bağlantısı");
define("_MD_SUBMITTEDBY","Gönderen: ");
define("_MD_APPROVE","Onayla");
define("_MD_DELETE","Sil");
define("_MD_NOSUBMITTED","Yeni Gönderilen İndirme Yok.");
define("_MD_ADDMAIN","Ana Kategori Ekle");
define("_MD_TITLEC","Başlık: ");
define("_MD_CATSEC", "Görüntüleme Erişimi: ");
define("_MD_UPLOADSEC", "Yükleme Erişimi: ");
define("_MD_IMGURL","<br>Resim Dosya Adı <font size='-2'> (filemgmt_data/category_snaps dizininizde bulunur - Resim yüksekliği 50px olarak yeniden boyutlandırılacaktır)</font>");
define("_MD_ADD","Ekle");
define("_MD_ADDSUB","Alt Kategori Ekle");
define("_MD_IN","içinde");
define("_MD_ADDNEWFILE","Yeni Dosya Ekle");
define("_MD_MODCAT","Kategoriyi düzelt");
define("_MD_MODDL","İndirme Bilgisini Düzenle");
define("_MD_USER","Kullanıcı");
define("_MD_IP","IP adresi");
define("_MD_USERAVG","Kullanıcı AVG Değerlendirmesi");
define("_MD_TOTALRATE","Toplam Derecelendirme");
define("_MD_NOREGVOTES","Kayıtlı Kullanıcı Oyu Yok");
define("_MD_NOUNREGVOTES","İsimsiz Kullanıcı Oyu Yok");
define("_MD_VOTEDELETED","Oy verileri silindi.");
define("_MD_NOBROKEN","Bildirilen bozuk dosya yok.");
define("_MD_IGNOREDESC","Yoksay (Raporu yok sayar ve yalnızca bu bildirilen girişi siler</b>)");
define("_MD_DELETEDESC","Sil (<b>Depodaki rapor edilen dosya girişini siler</b> ancak asıl dosya kalır)");
define("_MD_REPORTER","Göndereni Rapor Et");
define("_MD_FILESUBMITTER","Dosya Gönderici");
define("_MD_IGNORE","Yoksay");
define("_MD_FILEDELETED","Dosya Silindi.");
define("_MD_FILENOTDELETED","Kayıt kaldırıldı, ancak Dosya Silinmedi. <p> Aynı dosyayı işaret eden 1'den fazla kayıt.");
define("_MD_BROKENDELETED","Bozuk dosya raporu silindi.");
define("_MD_USERMODREQ","Kullanıcı İndirme Bilgisi Değişiklik İstekleri");
define("_MD_ORIGINAL","Orjinal");
define("_MD_PROPOSED","Teklif Et");
define("_MD_OWNER","Sahibi: ");
define("_MD_NOMODREQ","İndirme Değişiklik Talebi Yok.");
define("_MD_DBUPDATED","Veritabanı Başarıyla Güncellendi!");
define("_MD_MODREQDELETED","Değişiklik İsteği Silindi.");
define("_MD_IMGURLMAIN",'Resim <div style="font-size:8pt;"> Resim yüksekliği 50 piksel olarak yeniden boyutlandırılacak </div>');
define("_MD_PARENT","Üst Kategori");
define("_MD_SAVE","Değişiklikleri Kaydet");
define("_MD_CATDELETED","Kategori Silindi.");
define("_MD_WARNING","UYARI: Bu Kategoriyi, TÜM Dosyalarını ve Yorumlarını silmek istediğinize emin misiniz?");
define("_MD_YES","Evet");
define("_MD_NO","Hayır");
define("_MD_NEWCATADDED","Kategori Başarıyla Eklendi!");
define("_MD_CONFIGUPDATED","Yeni yapılandırma kaydedildi");
define("_MD_ERROREXIST","HATA: Sağladığınız indirme bilgisi zaten veritabanında!");
define("_MD_ERRORNOFILE","HATA: Veritabanında kayıtlarında dosya bulunamadı!");
define("_MD_ERRORTITLE","HATA:: BAŞLIK girmeniz gerekiyor!");
define("_MD_ERRORDESC","HATA: AÇIKLAMA girmeniz gerekiyor!");
define("_MD_NEWDLADDED","Veritabanına yeni indirme eklendi.");
define("_MD_NEWDLADDED_DUPFILE","Uyarı: Yinelenen Dosya. Veritabanına yeni indirme eklendi.");
define("_MD_NEWDLADDED_DUPSNAP","Uyarı: Yinelenen Anlık Görüntü. Veritabanına yeni indirme eklendi.");
define("_MD_DLUPDATED", "Dosya güncellendi.");
define("_MD_HELLO","Merhaba %s");
define("_MD_WEAPPROVED","İndirmeler bölümümüze indirme gönderiminizi onayladık. Dosya adı: ");
define("_MD_THANKSSUBMIT","Gönderiniz için teşekkür ederiz!");
define("_MD_UPLOADAPPROVED","Yüklediğiniz dosya onaylandı");
define("_MD_DLSPERPAGE","Sayfa Başına Görüntülenen İndirmeler: ");
define("_MD_HITSPOP","Popüler Olacak Hitler: ");
define("_MD_DLSNEW","Üst Sayfadaki Yeni Olarak İndirilenlerin Sayısı: ");
define("_MD_DLSSEARCH","Arama Sonuçlarındaki İndirme Sayısı: ");
define("_MD_TRIMDESC","Listedeki Dosya Açıklamalarını Kırp: ");
define("_MD_DLREPORT","İndirme Raporuna Erişimi Kısıtla");
define("_MD_WHATSNEWDESC","WhatsNew Listing'i Etkinleştir");
define("_MD_SELECTPRIV","Erişimi Yalnızca 'Oturum Açmış Kullanıcılar' Grubuna Sınırla: ");
define("_MD_ACCESSPRIV","İsimsiz Erişimler Aktif: ");
define("_MD_UPLOADSELECT","Oturum Açmış Kullanıcılar İçin Yüklemelere İzin Ver: ");
define("_MD_UPLOADPUBLIC","Anonim Kullanıcılar İçin Yüklemelere İzin Ver: ");
define("_MD_USESHOTS","Kategori Görsellerini Görüntüle: ");
define("_MD_IMGWIDTH","Küçük Resim Genişliği: ");
define("_MD_MUSTBEVALID","Küçük resim resmi, %s dizini altında geçerli bir resim dosyası olmalıdır (ör. shot.gif). Resim dosyası yoksa boş bırakın.");
define("_MD_REGUSERVOTES","Kayıtlı Kullanıcı Oyları (toplam oy: %s)");
define("_MD_ANONUSERVOTES","Misafir Kullanıcı Oyları (toplam oy: %s)");
define("_MD_YOURFILEAT","Dosyanız %s tarihinde gönderildi"); // this is an approved mail subject. %s is your site name
define("_MD_VISITAT","%s adresindeki indirmeler bölümümüzü ziyaret edin");
define("_MD_DLRATINGS","İndirme Derecesi (toplam oy: %s)");
define("_MD_CONFUPDATED","Yapılandırma başarıyla güncellendi!");
define("_MD_NOFILES","Dosya Bulunamadı");
define("_MD_APPROVEREQ","Bu kategoride yüklemenin onaylanması gerekiyor");
define("_MD_REQUIRED","* Gerekli Alan");
define("_MD_SILENTEDIT","Sessiz Düzenleme: ");

// Additional glFusion Defines
define("_MD_NOVOTE","Henüz derecelendirilmedi");
define("_IFNOTRELOAD","Sayfa otomatik olarak yeniden yüklenmezse, lütfen <a href=\"%s\">burayı</a> tıklayın");
define("_GL_ERRORNOACCESS","HATA: Bu Belge Havuzuna Erişim Yok");
define("_GL_ERRORNOUPLOAD","HATA: Yükleme yetkiniz yok");
define("_GL_ERRORNOADMIN","HATA: Bu işlev kısıtlı");
define("_GL_NOUSERACCESS","doküman Deposuna erişimi yok");
define("_MD_ERRUPLOAD","Dosya Yöneticisi: Yüklenemiyor - dosya deposu dizinleri için izinleri kontrol edin");
define("_MD_DLFILENAME","Dosya Adı: ");
define("_MD_REPLFILENAME","Değiştirilen Dosya: ");
define("_MD_SCREENSHOT","Ekran Görüntüsü");
define("_MD_SCREENSHOT_NA",'&nbsp;');
define("_MD_COMMENTSWANTED","Yorumlar takdir edilmektedir");
define("_MD_CLICK2SEE","Görmek için tıklayın: ");
define("_MD_CLICK2DL","İndirmek için tıklayın: ");
define("_MD_ORDERBY","Sırala: ");
