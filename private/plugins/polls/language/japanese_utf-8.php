<?php
###############################################################################
# japanese_utf-8.php
#
# This is the Japanese language file for the glFusion Polls plugin
#
# Copyright (C) 2001 Tony Bibbs
# tony AT tonybibbs DOT com
# Copyright (C) 2005 Trinity Bays
# trinity93 AT gmail DOT com
# Tranlated by glFusion Japanese group SaY and Ivy
# Copyright (C) 2008 Takahiro Kambe
# Additional translation to Japanese by taca AT back-street DOT net
#
# This program is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License
# as published by the Free Software Foundation; either version 2
# of the License, or (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
#
###############################################################################

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

$LANG_POLLS = array(
    'polls' => 'アンケート',
    'results' => '結果',
    'pollresults' => '投票結果',
    'votes' => '投票',
    'vote' => '投票する',
    'pastpolls' => 'アンケートの一覧',
    'savedvotetitle' => '投票ありがとうございました',
    'savedvotemsg' => 'テーマ：',
    'pollstitle' => 'アンケート一覧',
    'polltopics' => '他のアンケートを見る',
    'stats_top10' => 'アンケート（上位10件）',
    'stats_topics' => 'アンケートの質問',
    'stats_votes' => '投票',
    'stats_none' => 'このサイトにはアンケートがないか，まだ誰も投票していないようです。',
    'stats_summary' => 'アンケート数(投票数)',
    'open_poll' => '投票可否',
    'answer_all' => '残りのすべての質問にお答えください',
    'not_saved' => '結果は保存されませんでした',
    'upgrade1' => 'アンケートプラグインの新しいバージョンがインストールされました。',
    'upgrade2' => 'アップグレードしてください。',
    'editinstructions' => 'アンケートIDを入力してください。少なくとも1つの質問と2つの回答を用意してください。',
    'pollclosed' => 'This poll is closed for voting.',
    'pollhidden' => 'You have already voted. This poll results will only be shown when voting is closed.',
    'start_poll' => 'Start Poll',
    'deny_msg' => 'Access to this poll is denied.  Either the poll has been moved/removed or you do not have sufficient permissions.',
    'login_required' => "<a href=\"{$_CONF['site_url']}/users.php\" rel=\"nofollow\">Login</a> required to vote",
    'username' => 'Username',
    'ipaddress' => 'IP Address',
    'date_voted' => 'Date Voted',
    'description' => 'Description',
    'general' => 'General',
    'poll_questions' => 'Poll Questions',
    'permissions' => 'Permissions'
);

###############################################################################
# admin/plugins/polls/index.php

$LANG25 = array(
    1 => 'モード',
    2 => 'テーマと質問と少なくとも1つの回答を入力してください。',
    3 => '作成日時',
    4 => 'アンケート（ %s ）が保存されました',
    5 => 'アンケートの編集',
    6 => 'アンケートID',
    7 => '(スペースを含まないこと)',
    8 => 'ホームページに表示する',
    9 => 'テーマ',
    10 => '回答 / 投票数',
    11 => 'アンケート( %s )の選択肢にエラーがありました',
    12 => 'アンケート( %s )の質問項目にエラーがありました',
    13 => 'アンケートの作成',
    14 => '保存',
    15 => '中止',
    16 => '削除',
    17 => 'アンケートIDを入力してください',
    18 => 'アンケート管理',
    19 => 'アンケートの削除・編集はタイトル左のアイコンをクリック，新規に作成する場合は「新規作成」をクリックしてください。タイトルをクリックするとアンケートを閲覧できます。',
    20 => '投票者',
    21 => 'アクセスが拒否されました',
    22 => "管理権限のないアンケートを編集しようとしました。この行為は記録されます。<a href=\"{$_CONF['site_admin_url']}/poll.php\">投票の管理画面</a>に戻ってください。",
    23 => '新規アンケート',
    24 => '管理画面',
    25 => 'はい',
    26 => 'いいえ',
    27 => '編集',
    28 => '検索',
    29 => '検索条件',
    30 => '表示件数',
    31 => '質問',
    32 => '質問のテキストを削除すると、アンケートから質問が削除されます。',
    33 => '投票可能',
    34 => 'テーマ:',
    35 => 'このアンケートにはさらにもう',
    36 => '件，質問があります。',
    37 => '投票時は結果非公開',
    38 => 'アンケート実施中は，オーナーとルート管理者だけが結果を見ることができます。',
    39 => 'テーマは1つ以上の質問がある場合に表示されます。',
    40 => 'アンケートの結果を見る',
    41 => 'Are you sure you want to delete this Poll?',
    42 => 'Are you absolutely sure you want to delete this Poll?  All questions, answers and comments that are associated with this Poll will also be permanently deleted from the database.',
    43 => 'Login Required to Vote'
);

###############################################################################
# autotag descriptions

$LANG_PO_AUTOTAG = array(
    'desc_poll' => 'Link: to a Poll on this site.  link_text defaults to the Poll topic.  usage: [poll:<i>poll_id</i> {link_text}]',
    'desc_poll_result' => 'HTML: renders the results of a Poll on this site.  usage: [poll_result:<i>poll_id</i>]',
    'desc_poll_vote' => 'HTML: renders a voting block for a Poll on this site.  usage: [poll_vote:<i>poll_id</i>]'
);

$PLG_polls_MESSAGE19 = 'アンケートが登録されました。';
$PLG_polls_MESSAGE20 = 'アンケートは削除されました。';

// Messages for the plugin upgrade
$PLG_polls_MESSAGE3001 = 'プラグインのアップグレードはサポートされていません。';
$PLG_polls_MESSAGE3002 = $LANG32[9];

// Localization of the Admin Configuration UI
$LANG_configsections['polls'] = array(
    'label' => 'アンケート',
    'title' => 'アンケートの設定'
);

$LANG_confignames['polls'] = array(
    'pollsloginrequired' => 'ログインを要求する',
    'hidepollsmenu' => 'メニューに表示しない',
    'maxquestions' => 'アンケート毎の質問の最大数',
    'maxanswers' => '質問毎の選択肢の最大数',
    'answerorder' => 'アンケート結果の表示順',
    'pollcookietime' => '投票者のクッキーの有効期間',
    'polladdresstime' => '投票者のIPアドレスの有効期間',
    'delete_polls' => '所有者の削除と共に削除する',
    'aftersave' => 'アンケート保存後の画面遷移',
    'default_permissions' => 'パーミッション',
    'displayblocks' => 'Display glFusion Blocks'
);

$LANG_configsubgroups['polls'] = array(
    'sg_main' => 'メイン'
);

$LANG_fs['polls'] = array(
    'fs_main' => 'アンケートのメイン設定',
    'fs_permissions' => 'アンケートのデフォルトパーミッション（[0]所有者 [1]グループ [2]メンバー [3]ゲスト）'
);

// Note: entries 0, 1, and 12 are the same as in $LANG_configselects['Core']
$LANG_configselects['polls'] = array(
    0 => array('はい' => 1, 'いいえ' => 0),
    1 => array('はい' => true, 'いいえ' => false),
    2 => array('登録順' => 'submitorder', '得票順' => 'voteorder'),
    9 => array('作成したアンケートを表示する' => 'item', 'アンケート管理を表示する' => 'list', 'アンケート一覧を表示する' => 'plugin', 'Homeを表示する' => 'home', '管理画面TOPを表示する' => 'admin'),
    12 => array('アクセス不可' => 0, '表示' => 2, '表示・編集' => 3),
    13 => array('Left Blocks' => 0, 'Right Blocks' => 1, 'Left & Right Blocks' => 2, 'None' => 3)
);

?>