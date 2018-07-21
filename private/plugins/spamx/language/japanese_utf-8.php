<?php
/**
 * File: japanese_utf-8.php
 * This is the Japanese language file for the glFusion Spam-X plugin
 * 
 * Copyright (C) 2004-2008 by the following authors:
 * Author        Tom Willett        tomw AT pigstye DOT net
 * Tranlated by Ivy (glFusion Japanese)
 * Copyright (C) 2008 Takahiro Kambe
 * Additional translation to Japanese by taca AT back-street DOT net
 * 
 * Licensed under GNU General Public License
 *
 * $Id: japanese_utf-8.php 2970 2008-08-24 01:32:36Z mevans0263 $
 */

if (!defined ('GVERSION')) {
    die ('This file cannot be used on its own.');
}

global $LANG32;

$LANG_SX00 = array(
    'inst1' => '<p>もしこれをおこなったら他も ',
    'inst2' => '見えます。そしてあなたのブラックリストをインポートするとさらに効果を発揮します。',
    'inst3' => 'データベースが構築されました。</p><p>もし、ウェブサイトを作ってもリスティングされないためには',
    'inst4' => ' <a href="mailto:spamx@pigstye.net">spamx@pigstye.net</a>までメールを送信してください。',
    'inst5' => 'リクエストは尊重されます。',
    'submit' => '実行',
    'subthis' => 'このSpam-X情報は、Spam-X Central Databaseを参照しています。',
    'secbut' => '2番目のボタンは、RDFフィードを作成します。他サイトから呼ぶことができます。',
    'sitename' => 'サイト名: ',
    'URL' => 'URL Spam-X リスト: ',
    'RDF' => 'RDF url: ',
    'impinst1a' => 'Spam-Xを使う前に、スパムブロッカー機能のパーソナルブラックリストを閲覧してインポートします。',
    'impinst1b' => 'サイト、つぎのボタンを。 (最後のボタンをクリック)',
    'impinst2' => 'Gplugs/Spam-X の最初の実行はマスターリストに加えられます ',
    'impinst2a' => 'ブラックリストが反映。 (註: 複数サイトあるのなら、 その中のひとつを指定したいかもしれない',
    'impinst2b' => 'マスタだけが名前を変更できます。（ これであなたにあなたのサイトをアップデートを簡単に、そしてそのリストを小さいままにできます。) ',
    'impinst2c' => '実行ボタンをクリックしたら、 [戻る]をクリックしてこのページに戻ってください。',
    'impinst3' => '以下の内容が送られるでしょう。: (もしそれらが間違っていたら、編集することができます。).',
    'availb' => 'ブラックリスト表示',
    'clickv' => 'クリックしてブラックリストを表示',
    'clicki' => 'クリックしてブラックリストをインポート',
    'ok' => 'OK',
    'rsscreated' => 'RSSフィードが作成されました',
    'add1' => '追加されました ',
    'add2' => ' エントリー: ',
    'add3' => 'のブラックリスト',
    'adminc' => '管理者コマンド:',
    'mblack' => 'マイブラックリスト:',
    'rlinks' => '関係先リンク:',
    'e3' => 'glFusionのセンサーリスト追加:',
    'addcen' => 'センサーリスト追加',
    'addentry' => 'エントリ追加',
    'e1' => 'クリックしてエントリを削除',
    'e2' => 'エントリを追加して, エントリ追加ボタンをクリックしてください。エントリーは完全なPerl正規表現を使用することができます。 ',
    'pblack' => 'Spam-X パーソナルブラックリスト',
    'conmod' => 'Spam-Xモジュール設定',
    'acmod' => 'Spam-X アクションモジュール',
    'exmod' => 'Spam-X イグザミンモジュール',
    'actmod' => 'アクティブモジュール',
    'avmod' => 'アベイラブルモジュール',
    'coninst' => '<hr>クリックしてアクティブモジュールを削除、クリックしてアベイラブルモジュールを追加。<br>モジュールは、示された順序で実行されます。 ',
    'fsc' => ' スパムポストマッチングが見つかりました。',
    'fsc1' => ' ユーザによる投稿',
    'fsc2' => ' IP から',
    'uMTlist' => 'Update MT-ブラックリスト',
    'uMTlist2' => ': 追加 ',
    'uMTlist3' => ' 投稿と削除 ',
    'entries' => ' 投稿.',
    'uPlist' => 'パーソナルブラックリストアップデート',
    'entriesadded' => 'エントリが追加されました',
    'entriesdeleted' => 'エントリが削除されました',
    'viewlog' => 'Spam-Xログ閲覧',
    'clearlog' => 'ログファイル削除',
    'logcleared' => '- Spam-X ログファイルが削除されました',
    'plugin' => 'プラグイン',
    'access_denied' => 'アクセスが拒否されました',
    'access_denied_msg' => 'ルートユーザだけがこのページにアクセスできます。あなたのユーザ名とIPアドレスを記録しました。',
    'admin' => 'プラグイン管理',
    'install_header' => 'インストール/アンインストールプラグイン',
    'installed' => 'プラグインがインストールされました',
    'uninstalled' => 'プラグインはインストールされませんでした',
    'install_success' => 'インストール成功',
    'install_failed' => 'インストール失敗 -- エラーログを見てください。',
    'uninstall_msg' => 'プラグインはアンインストールされました',
    'install' => 'インストール',
    'uninstall' => 'アンインストール',
    'warning' => '注意! プラグインがまだ有効です',
    'enabled' => 'アンインストールの前まで利用不可。',
    'readme' => 'STOP! インストールの前に読んで ',
    'installdoc' => 'インストールドキュメントを。',
    'spamdeleted' => 'スパムポスト削除',
    'foundspam' => 'スパムポストマッチングが見つかりました ',
    'foundspam2' => ' ユーザによって投稿されました ',
    'foundspam3' => ' IPから ',
    'deletespam' => 'スパム削除',
    'numtocheck' => 'コメント数チェック',
    'note1' => '<p>Note: マスデリートで攻撃から守ります。',
    'note2' => ' コメントスパムと Spam-X はキャッチしません。 </p><ul><li>最初のリンクとその他 ',
    'note3' => 'このスパムコメントをあなたのパーソナルブラックリストへ追加</li><li>',
    'note4' => 'ここに戻り、最近のコメントを Spam-X チェック</li></ul><p>',
    'note5' => '最新コメントを最後のコメントに追加チェック -- コメントチェック ',
    'note6' => 'チェックをより多く要求します。</p>',
    'masshead' => '<hr><h1 align="center">マスデリート - スパムコメント</h1>',
    'masstb' => '<hr><h1 align="center">マスデリート - トラックバックスパム</h1>',
    'comdel' => 'コメントが削除されました。',
    'initial_Pimport' => '<p>パーソナルブラックリスト インポート"',
    'initial_import' => '初期 MT-ブラックリスト インポート',
    'import_success' => '<p> %d ブラクリストエントリーがインストールできました。',
    'import_failure' => '<p><strong>エラー:</strong> エントリーがみつかりません。',
    'allow_url_fopen' => '<p>申し訳ありませんがあなたのウェブサーバのコンフィギュレーションはリモートファイルの読み込みを許可していません。 (<code>allow_url_fopen</code> がオフ). 次のURLからブラックリストをダウンロードしてglFusionの "データ" ディレクトリにアップロードしてください。<tt>%s</tt>, 再実行の前に:',
    'documentation' => 'Spam-X プラグインドキュメント',
    'emailmsg' => "新しいスパム投稿 \"%s\"\nUser UID: \"%s\"\n\nコンテンツ:\"%s\"",
    'emailsubject' => 'スパムポスト %s',
    'ipblack' => 'Spam-X IP ブラックリスト',
    'ipofurlblack' => 'Spam-X IP of URL ブラックリスト',
    'headerblack' => 'Spam-X HTTP Header ブラックリスト',
    'headers' => 'リクエストヘッダ:',
    'stats_headline' => 'Spam-Xステータス',
    'stats_page_title' => 'ブラックリスト',
    'stats_entries' => 'エントリ',
    'stats_mtblacklist' => 'MT-ブラックリスト',
    'stats_pblacklist' => 'パーソナルブラックリスト',
    'stats_ip' => 'ブロックIP',
    'stats_ipofurl' => 'URLのIPによってブロックされました',
    'stats_header' => 'HTTPヘッダ',
    'stats_deleted' => 'スパム投稿削除数',
    'plugin_name' => 'Spam-X',
    'slvwhitelist' => 'SLVホワイトリスト',
    'instructions' => 'Spam-X allows you to define words, URLs, and other items that can be used to block spam posts on your site.',
    'invalid_email_or_ip' => 'Invalid e-mail address or IP address has been blocked',
    'filters' => 'Filters',
    'edit_filters' => 'Edit Filters',
    'scan_comments' => 'Scan Comments',
    'scan_trackbacks' => 'Scan Trackbacks',
    'auto_refresh_on' => 'Auto Refresh On',
    'auto_refresh_off' => 'Auto Refresh Off',
    'type' => 'Type',
    'blocked' => 'Blocked',
    'no_blocked' => 'No spam has been blocked by this module',
    'filter' => 'Filter',
    'all' => 'All',
    'blacklist' => 'Blacklist',
    'http_header' => 'HTTP Header',
    'ip_blacklist' => 'IP Blacklist',
    'ipofurl' => 'IP of URL',
    'filter_instruction' => 'Here you can define filters which will be applied to each registration and post on the site. If any of the checks return true, the registration / post will be blocked as spam',
    'value' => 'Value',
    'no_filter_data' => 'No filters have been defined',
    'delete' => 'Delete',
    'delete_confirm' => 'Are you sure you want to delete this item?',
    'delete_confirm_2' => 'Are you REALLY SURE you want to delete this item',
    'new_entry' => 'New Entry',
    'blacklist_prompt' => 'Enter words to trigger spam',
    'http_header_prompt' => 'Header',
    'ip_prompt' => 'Enter IP to block',
    'ipofurl_prompt' => 'Enter IP of links to block',
    'content' => 'Content',
    'new_filter_entry' => 'New Filter Entry',
    'cancel' => 'Cancel',
    'ip_error' => 'The entry does not appear to be a valid IP or IP range',
    'no_bl_data_error' => 'No errors',
    'blacklist_success_save' => 'Spam-X Filter Saved Successfully',
    'blacklist_success_delete' => 'Selected items successfully deleted',
    'invalid_item_id' => 'Invalid ID',
    'edit_filter_entry' => 'Edit Filter',
    'spamx_filters' => 'Spam-X Filters',
    'history' => 'Past 3 Months',
    'interactive_tester' => 'Interactive Tester',
    'username' => 'Username',
    'email' => 'Email',
    'ip_address' => 'IP Address',
    'user_agent' => 'User Agent',
    'referrer' => 'Referrer',
    'content_type' => 'Content Type',
    'comment' => 'Comment',
    'forum_post' => 'Forum Post',
    'response' => 'Response'
);

// Define Messages that are shown when Spam-X module action is taken
$PLG_spamx_MESSAGE128 = 'スパム削除。投稿は削除されました。';
$PLG_spamx_MESSAGE8 = 'スパム削除。メールが管理者に送られました。';

// Messages for the plugin upgrade
$PLG_spamx_MESSAGE3001 = 'プラグインのアップグレードはサポートされていません。';
$PLG_spamx_MESSAGE3002 = $LANG32[9];

// Localization of the Admin Configuration UI
$LANG_configsections['spamx'] = array(
    'label' => 'Spam-X',
    'title' => 'Spam-Xの設定'
);

$LANG_confignames['spamx'] = array(
    'action' => 'Spam-X の動作',
    'notification_email' => 'メールで通知する',
    'admin_override' => '管理者の入力はフィルターしない',
    'logging' => 'ログを有効にする',
    'timeout' => 'タイムアウト',
    'sfs_username_check' => 'Enable User name validation',
    'sfs_email_check' => 'Enable email validation',
    'sfs_ip_check' => 'Enable IP address validation',
    'sfs_username_confidence' => 'Minimum confidence level on Username match to trigger spam block',
    'sfs_email_confidence' => 'Minimum confidence level on Email match to trigger spam block',
    'sfs_ip_confidence' => 'Minimum confidence level on IP address match to trigger spam block',
    'slc_max_links' => 'Maximum Links allowed in post',
    'debug' => 'Debug Logging',
    'akismet_enabled' => 'Akismet Module Enabled',
    'akismet_api_key' => 'Akismet API Key (Required)',
    'fc_enable' => 'Enable Form Check',
    'sfs_enable' => 'Enable Stop Forum Spam',
    'slc_enable' => 'Enable Spam Link Counter',
    'action_delete' => 'Delete Identified Spam',
    'action_mail' => 'Mail Admin when Spam Caught'
);

$LANG_configsubgroups['spamx'] = array(
    'sg_main' => 'メイン'
);

$LANG_fs['spamx'] = array(
    'fs_main' => 'Spam-Xの設定',
    'fs_sfs' => 'Stop Forum Spam Settings',
    'fs_slc' => 'Spam Link Counter',
    'fs_akismet' => 'Akismet',
    'fs_formcheck' => 'Form Check'
);

// Note: entries 0, 1, 9, and 12 are the same as in $LANG_configselects['Core']
$LANG_configselects['spamx'] = array(
    0 => array('はい' => 1, 'いいえ' => 0),
    1 => array('はい' => true, 'いいえ' => false)
);

?>