<?php
/**
* glFusion CMS
*
* UTF-8 Language File for Polls Plugin
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

$LANG_POLLS = array(
    'polls'             => 'Anketler',
    'results'           => 'Sonuçlar',
    'pollresults'       => 'Anket Sonuçları',
    'votes'             => 'oylar',
    'vote'              => 'Oy',
    'pastpolls'         => 'Geçmiş Anketler',
    'savedvotetitle'    => 'Oylama Kaydedildi',
    'savedvotemsg'      => 'Oyunuz anket için kaydedildi',
    'pollstitle'        => 'Sistemdeki Anketler',
    'polltopics'        => 'Diğer Anketler',
    'stats_top10'       => 'Son 10 Anket',
    'stats_topics'      => 'Anket Konusu',
    'stats_votes'       => 'Oylar',
    'stats_none'        => 'Görünüşe göre bu sitede hiç anket yok veya hiç kimse oy kullanmamış.',
    'stats_summary'     => 'Sistemdeki Anketler (Cevaplar)',
    'open_poll'         => 'Oylamaya Açık',
    'answer_all'        => 'Lütfen kalan tüm soruları yanıtlayın',
    'not_saved'         => 'Sonuç kaydedilmedi',
    'upgrade1'          => 'Anket eklentisinin yeni bir sürümünü yüklediniz. Lütfen',
    'upgrade2'          => 'yükselt',
    'editinstructions'  => 'Lütfen Anket Kimliğini doldurun, bunun için en az bir soru ve iki cevap.',
    'pollclosed'        => 'Bu anket oylamaya kapalıdır.',
    'pollhidden'        => 'Anket sonuçları, yalnızca Anket kapandıktan sonra kullanılabilir olacaktır.',
    'start_poll'        => 'Anket Başlat',
    'deny_msg' => 'Bu ankete erişim reddedildi.  Anket taşındı/kaldırıldı ya da yeterli izniniz yok.',
    'login_required'    => '<a href="'.$_CONF['site_url'].'Oy vermek için /users.php" rel="nofollow">Giriş</a> gerekli',
    'username'          => 'Kullanıcı Adı',
    'ipaddress'         => 'IP adresi',
    'date_voted'        => 'Oy Verildiği Tarih',
    'description'       => 'Açıklama',
    'general'           => 'Genel',
    'poll_questions'    => 'Anket Soruları',
    'permissions'       => 'İzinler',
);

###############################################################################
# admin/plugins/polls/index.php

$LANG25 = array(
    1 => 'Mod',
    2 => 'Lütfen bir konu, en az bir soru ve bu soru için en az bir cevap girin.',
    3 => 'Anket Oluşturuldu',
    4 => "%s anketi kaydedildi",
    5 => 'Anketi Düzenle',
    6 => 'Anket Kimliği',
    7 => '(boşluk kullanmayın)',
    8 => 'Anket Bloğunda Görünür',
    9 => 'Konu',
    10 => 'Cevaplar / Oylar / Açıklama',
    11 => "%s anketi hakkında anket yanıt verisi alınırken bir hata oluştu",
    12 => "%s anketiyle ilgili anket sorusu verileri alınırken bir hata oluştu",
    13 => 'Anket Oluştur',
    14 => 'kaydet',
    15 => 'vazgeç',
    16 => 'sil',
    17 => 'Lütfen bir Anket Kimliği girin',
    18 => 'Anket Yönetimi',
    19 => 'Bir anketi değiştirmek veya silmek için anketin düzenle simgesine tıklayın.  Yeni bir anket oluşturmak için yukarıdaki "Yeni Oluştur" seçeneğine tıklayın.',
    20 => 'Oy Verenler',
    21 => 'Erişim Engellendi',
    22 => "Yetkiniz olmayan bir ankete erişmeye çalışıyorsunuz.  Bu deneme günlüğe kaydedildi.  Lütfen <a href=\"{$_CONF['site_admin_url']}/poll.php\">anket yönetim ekranına geri dönün</a>.",
    23 => 'Yeni Anket',
    24 => 'Yönetim AnaSayfa',
    25 => 'Evet',
    26 => 'Hayır',
    27 => 'Düzenle',
    28 => 'Gönder',
    29 => 'Ara',
    30 => 'Sonuç Limiti',
    31 => 'Soru',
    32 => 'Bu soruyu anketten kaldırmak için soru metnini kaldırın',
    33 => 'Oylamaya Açık',
    34 => 'Anket Konusu:',
    35 => 'Bu ankette',
    36 => 'daha fazla soru.',
    37 => 'Anket açıkken sonuçları gizle',
    38 => 'Anket açıkken sonuçları yalnızca sahip &amp; yönetici görebilir',
    39 => 'Konu yalnızca 1\'den fazla soru varsa görüntülenecektir.',
    40 => 'Bu ankete verilen tüm yanıtları görün',
    41 => 'Bu anketi silmek istediğinize emin misiniz?',
    42 => 'Bu Anketi silmek istediğinize kesinlikle emin misiniz?  Bu Anket ile ilgili tüm sorular, cevaplar ve yorumlar da veri tabanından kalıcı olarak silinecektir.',
    43 => 'Oy Vermek İçin Giriş Yapılması Gerekiyor',
);

$LANG_PO_AUTOTAG = array(
    'desc_poll'                 => 'Bağlantı: Bu sitedeki bir Ankete.  link_text varsayılan olarak Anket konusuna gelir.  kullanım: [anket:<i>poll_id</i> {link_text}]',
    'desc_poll_result'          => 'HTML: Bu sitedeki bir Anketin sonuçlarını işler.  kullanım: [poll_result:<i>poll_id</i>]',
    'desc_poll_vote'            => 'HTML: Bu sitedeki bir Anket için bir oylama bloğu oluşturur.  kullanım: [poll_vote:<i>poll_id</i>]',
);

$PLG_polls_MESSAGE19 = 'Anketiniz başarıyla kaydedildi.';
$PLG_polls_MESSAGE20 = 'Anketiniz başarıyla silindi.';

// Messages for the plugin upgrade
$PLG_polls_MESSAGE3001 = 'Eklenti yükseltmesi desteklenmiyor.';
$PLG_polls_MESSAGE3002 = $LANG32[9];


// Localization of the Admin Configuration UI
$LANG_configsections['polls'] = array(
    'label' => 'Anketler',
    'title' => 'Anket Yapılandırması'
);

$LANG_confignames['polls'] = array(
    'pollsloginrequired' => 'Anketler İçin Giriş Gerekli',
    'hidepollsmenu' => 'Anketler Menü Girişini Gizle',
    'maxquestions' => 'Maks. Anket Başına Soru',
    'maxanswers' => 'Maks. Soru Başına Seçenek',
    'answerorder' => 'Sonuçları Sırala',
    'pollcookietime' => 'Oylama Çerezinin Geçerlilik Süresi',
    'polladdresstime' => 'Oylama IP Adresi Geçerlilik Süresi',
    'delete_polls' => 'Sahibi Olunan Anketleri Sil',
    'aftersave' => 'Anketi Kaydettikten Sonra',
    'default_permissions' => 'Anket Varsayılan İzinleri',
    'displayblocks' => 'GlFusion Bloklarını Görüntüleme',
);

$LANG_configsubgroups['polls'] = array(
    'sg_main' => 'Ana Ayarlar'
);

$LANG_fs['polls'] = array(
    'fs_main' => 'Genel Anket Ayarları',
    'fs_permissions' => 'Varsayılan İzinler'
);

$LANG_configSelect['polls'] = array(
    0 => array(1=>'Doğru', 0=>'False'),
    1 => array(true=>'Doğru', false=>'False'),
    2 => array('submitorder'=>'Gönderildiği Gibi', 'voteorder'=>'Oylara Göre'),
    9 => array('item'=>'Ankete Yönlendir', 'list'=>'Yönetici Listesini Görüntüle', 'plugin'=>'Genel Listeyi Görüntüle', 'home'=>'Ana Ekranı Görüntüle', 'admin'=>'Ekran Yöneticisi'),
    12 => array(0=>'Erişim yok', 2=>'Salt Okunur', 3=>'Okunur/Yazılır'),
    13 => array(0=>'Sol Bloklar', 1=>'Sağ Bloklar', 2=>'Sol ve Sağ Bloklar', 3=>'Hiçbiri')
);

?>
