<?php

/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Geeklog Forums Plugin 2.0 for Geeklog - The Ultimate Weblog               |
// | Official release date: Feb 7,2003                                         |
// +---------------------------------------------------------------------------+
// | german.php                                                                |
// | Translation by Dirk Haun <dirk AT haun-online DOT de>                     |
// +---------------------------------------------------------------------------+
// | Copyright (C) 2000,2001 by the following authors:                         |
// | Geeklog Author: Tony Bibbs       - tony@tonybibbs.com                     |
// +---------------------------------------------------------------------------+
// | FORUM Plugin Authors                                                      |
// | Prototype & Concept    :  Mr.GxBlock of www.gxblock.com                   |
// | Co-Developed by Matthew and Blaine                                        |
// | Matthew DeWyer, contact: matt@mycws.com          www.cweb.ws              |
// | Blaine Lang,    contact: geeklog@langfamily.ca   www.langfamily.ca        |
// +---------------------------------------------------------------------------+
// |                                                                           |
// | This program is free software; you can redistribute it and/or             |
// | modify it under the terms of the GNU General Public License               |
// | as published by the Free Software Foundation; either version 2            |
// | of the License, or (at your option) any later version.                    |
// |                                                                           |
// | This program is distributed in the hope that it will be useful,           |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of            |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the             |
// | GNU General Public License for more details.                              |
// |                                                                           |
// | You should have received a copy of the GNU General Public License         |
// | along with this program; if not, write to the Free Software Foundation,   |
// | Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.           |
// |                                                                           |
// +---------------------------------------------------------------------------+
//

$LANG_GF00 = array (
    'admin_only'        => 'Sorry Admins Only. If you are an Admin please login first.',
    'plugin'            => 'Plugin',
    'pluginlabel'       => 'Forum',         // What shows up in the siteHeader
    'searchlabel'       => 'Forum',
    'statslabel'        => 'Total Forum Posts',
    'statsheading1'     => 'Forum Top 10 Viewed Topics',
    'statsheading2'     => 'Forum Top 10 Replied Topics',
    'statsheading3'     => 'No topics to report on',
    'searchresults'     => 'Suchergebnisse %s',
    'useradminmenu'     => 'Forum-Einstellungen',
    'useradmintitle'    => 'Forum-Einstellungen',
    'access_denied'     => 'Zugriff verweigert',
    'access_denied_msg' => 'Only Root Users have Access to this Page.  Your user name and IP have been recorded.',
    'admin'             => 'Plugin Admin',
    'install_header'    => 'Install/Uninstall Plugin',
    'installed'         => 'The Plugin and Block are now installed,<p><i>Enjoy,<br><a href="MAILTO:langmail@sympatico.ca">Blaine</a></i>',
    'uninstalled'       => 'The Plugin is Not Installed',
    'install_success'   => 'Installation Successful<p><b>Next Steps</b>: 
        <ol><li>Use the Forum Admin to configure your new forum
        <li>Review Forum Settings and personalize
        <li>Create at least one Forum and one Category</ol>
        <p>Review the <a href="%s">Install Notes</a> for more information.',
        
    'install_failed'    => 'Installation Failed -- See your error log to find out why.',
    'uninstall_msg'     => 'Plugin Successfully Uninstalled',
    'install'           => 'Install',
    'uninstall'         => 'UnInstall',
    'enabled'           => '<br>Plugin is installed and enabled.<br>Disable first if you want to De-Install it.<p>',
    'warning'           => 'Forum De-Install Warning',
    'uploaderr'         => 'File Upload Error'
);


$PLG_forum_MESSAGE1 = 'Forum Plugin Upgrade: Update completed successfully.';
$PLG_forum_MESSAGE2 = 'Forum Plugin upgrade: We are unable to update this version automatically. Refer to the plugin documentation.';

$LANG_GF01['LOGIN']          = 'Login';
$LANG_GF01['FORUM']          = 'Forum';
$LANG_GF01['ALL']            = 'All'; 
$LANG_GF01['YES']            = 'Ja';
$LANG_GF01['NO']             = 'Nein';
$LANG_GF01['NEW']            = 'Neu';
$LANG_GF01['PREV']           = 'zurück';
$LANG_GF01['NEXT']           = 'weiter';
$LANG_GF01['ERROR']          = 'Fehler!';
$LANG_GF01['CONFIRM']        = 'Confirm';
$LANG_GF01['UPDATE']         = 'Update';
$LANG_GF01['SAVE']           = 'Sichern';
$LANG_GF01['CANCEL']         = 'Abbruch';
$LANG_GF01['CLOSE']          = 'Schließen';
$LANG_GF01['ON']             = 'Am: ';
$LANG_GF01['ON2']            = '&nbsp;&nbsp;<b>Am: </b>';
$LANG_GF01['IN']             = 'In: ';
$LANG_GF01['BY']             = 'Von: ';
$LANG_GF01['RE']             = 'Re: ';
$LANG_GF01['NA']             = 'n/v';
$LANG_GF01['DATE']           = 'Datum';
$LANG_GF01['VIEWS']          = 'Gelesen';
$LANG_GF01['REPLIES']        = 'Antworten';
$LANG_GF01['NAME']           = 'Name:';
$LANG_GF01['DESCRIPTION']    = 'Beschreibung: ';
$LANG_GF01['TOPIC']          = 'Thema';
$LANG_GF01['TOPICS']         = 'Themen';
$LANG_GF01['TOPICSUBJECT']   = 'Betreff';
$LANG_GF01['FROM']           = 'Von';
$LANG_GF01['REPLY']          = 'Antwort';
$LANG_GF01['PM']             = 'PM';
$LANG_GF01['HOME']           = 'Zum Forum';
$LANG_GF01['HOMEPAGE']       = 'Home';
$LANG_GF01['SUBJECT']        = 'Betreff';
$LANG_GF01['HELLO']          = 'Hallo ';
$LANG_GF01['MEMBERS']        = 'Mitglieder';
$LANG_GF01['MOVED']          = 'Verschoben';
$LANG_GF01['REMOVE']         = 'Löschen';
$LANG_GF01['CURRENT']        = 'Current';
$LANG_GF01['STARTEDBY']      = 'Begonnen von';
$LANG_GF01['POSTS']          = 'Beiträge';
$LANG_GF01['LASTPOST']       = 'Letzter Beitrag';
$LANG_GF01['POSTEDON']       = 'Geschrieben am';
$LANG_GF01['POSTEDBY']       = 'Geschrieben von';
$LANG_GF01['PAGE']           = 'Seite';
$LANG_GF01['PAGES']          = 'Seiten';
$LANG_GF01['ANONYMOUS']      = 'Gast:';
$LANG_GF01['TODAY']          = 'Heute um ';
$LANG_GF01['WELCOME']        = 'Willkommen ';
$LANG_GF01['REGISTER']       = 'Register';
$LANG_GF01['REGISTERED']     = 'Mitglied seit';
$LANG_GF01['MOSTPOPULAR']    = 'Most Popular';
$LANG_GF01['ORDERBY']        = 'Sortieren nach:&nbsp;';
$LANG_GF01['ORDER']          = 'Order:';
$LANG_GF01['USER']           = 'User';
$LANG_GF01['GROUP']          = 'Group';
$LANG_GF01['ANON']           = 'Gast: ';
$LANG_GF01['ADMIN']          = 'Admin';
$LANG_GF01['AUTHOR']         = 'Autor';
$LANG_GF01['LOCATION']       = 'Ort';
$LANG_GF01['WEBSITE']        = 'Website';
$LANG_GF01['EMAIL']          = 'E-Mail';
$LANG_GF01['MOOD']           = 'Stimmung';
$LANG_GF01['NOMOOD']         = 'Keine Stimmung';
$LANG_GF01['REQUIRED']       = '[benötigt]';
$LANG_GF01['OPTIONAL']       = '[optional]';
$LANG_GF01['SUBMIT']         = 'Abschicken';
$LANG_GF01['PREVIEW']        = 'Vorschau';
$LANG_GF01['NOTIFY']         = 'Notify:';
$LANG_GF01['REMOVE']         = 'Löschen';
$LANG_GF01['KEYWORDS']       = 'Keywords';
$LANG_GF01['EDIT']           = 'Ändern';
$LANG_GF01['DELETE']         = 'Löschen';
$LANG_GF01['MESSAGE']        = 'Nachricht:';
$LANG_GF01['OPTIONS']        = 'Optionen:';
$LANG_GF01['MISSINGSUBJECT'] = 'Leerer Betreff';
$LANG_GF01['MAY']            = 'darfsz';
$LANG_GF01['IS']             = 'ist';
$LANG_GF01['FOR']            = 'für';
$LANG_GF01['ARE']            = 'werden';
$LANG_GF01['NOT']            = 'nicht';
$LANG_GF01['YOU']            = 'Du';
$LANG_GF01['HTML']           = 'HTML';
$LANG_GF01['FULLHTML']       = 'HTML';
$LANG_GF01['WORDS']          = 'Wörter';
$LANG_GF01['SMILIES']        = 'Smilies';
$LANG_GF01['MIGRATE_NOW']    = 'Migrate Now'; 
$LANG_GF01['FILTERLIST']     = 'Gefiltertes';
$LANG_GF01['SELECTFORUM']    = 'Select Forum';
$LANG_GF01['DELETEAFTER']    = 'Delete after';
$LANG_GF01['TITLE']          = 'Title';
$LANG_GF01['COMMENTS']       = 'Kommentare'; 
$LANG_GF01['SUBMISSIONS']    = 'Submissions';

$LANG_GF01['HTML_FILTER_MSG']  = 'Gefiltertes HTML erlaubt';
$LANG_GF01['HTML_FULL_MSG']  = 'Alle HTML-Tags erlaubt';
$LANG_GF01['HTML_MSG']       = 'HTML erlaubt';
$LANG_GF01['CENSOR_PERM_MSG']  = 'Beiträge "entschärfen"';
$LANG_GF01['ANON_PERM_MSG']    = 'Beiträge von Gästen';
$LANG_GF01['POST_PERM_MSG1']   = 'Schreiben erlaubt';
$LANG_GF01['POST_PERM_MSG2']   = 'Gäste können schreiben';
$LANG_GF01['CENSORED']       = 'zensiert';
$LANG_GF01['ALLOWED']        = 'erlaubt';
$LANG_GF01['GO']             = 'los';
$LANG_GF01['STATUS']         = 'Status:';
$LANG_GF01['ONLINE']         = 'online';
$LANG_GF01['OFFLINE']        = 'offline';
$LANG_GF01['back2parent']    = 'Parent Topic';
$LANG_GF01['forumname']      = '';   // Enter name here if you want it to show in the footer of the admin screens
$LANG_GF01['category']       = 'Kategory: ';
$LANG_GF01['loginreqview']   = '<B>Sorry you must %s register</A> or %s login </A> to use these forums</B>';
$LANG_GF01['loginreqpost']   = '<B>Sorry you must register or login to post on these forums</B>';
$LANG_GF01['searchresults']  = '<b>»</b> Your search for <b>%s</b> %s author returned <b>%s</b> results:</b><br><br>';
$LANG_GF01['feature_not_on'] = 'Feature not enabled';
$LANG_GF01['nolastpostmsg']  = 'n/v';
$LANG_GF01['no_one']         = 'No one.';
$LANG_GF01['popular']        = 'Populär';
$LANG_GF01['notify']         = 'Notifications';
$LANG_GF01['PM']             = 'PMs';
$LANG_GF01['NEW_PM']         = 'Neue PM';
$LANG_GF01['DELALL_PM']      = 'Delete All';
$LANG_GF01['DELOLDER_PM']    = 'Delete older';
$LANG_GF01['members']        = 'Mitglieder';
$LANG_GF01['save_sucess']    = 'Save Sucessful';
$LANG_GF01['trademark']      = '<BR><CENTER><B>Geeklog Forum Project version 2.0</B> &copy; 2002</B></CENTER>';
$LANG_GF01['back2top']       = 'Back to top';
$LANG_GF01['POSTMODE']       = 'Post Mode:';
$LANG_GF01['TEXTMODE']       = 'Text-Modus';
$LANG_GF01['HTMLMODE']       = 'HTML-Modus';
$LANG_GF01['TopicPreview']   = 'Vorschau';
$LANG_GF01['moderator']      = 'Moderator';
$LANG_GF01['admin']          = 'Admin';
$LANG_GF01['DATEADDED']      = 'Hinzugefügt';
$LANG_GF01['PREVTOPIC']      = 'Vorheriges Thema';
$LANG_GF01['NEXTTOPIC']      = 'Nächstes Thema';
$LANG_GF01['admin']          = 'Admin';
$LANG_GF01['CONTENT']        = 'Inhalt';
$LANG_GF01['QUOTE_begin']    = '[Zitat&nbsp;';
$LANG_GF01['QUOTE_by'   ]    = 'von&nbsp;';
$LANG_GF01['RESYNC']         = "ReSync";
$LANG_GF01['RESYNCCAT']      = "ReSync Category Forums";  
$LANG_GF01['PROFILE']        = "Profil";
$LANG_GF01['DELETECONFIRM']  = "Are you sure you want to DELETE this record?";
$LANG_GF01['website']        = 'Website';
$LANG_GF01['EDITICON']       = 'Ändern';
$LANG_GF01['QUOTEICON']      = 'Zitat';
$LANG_GF01['ProfileLink']    = 'Profil';
$LANG_GF01['WebsiteLink']    = 'Website';
$LANG_GF01['PMLink']         = 'PM';
$LANG_GF01['EmailLink']      = 'E-Mail';
$LANG_GF01['FORUMSUBSCRIBE'] = 'Forum abonnieren';
$LANG_GF01['FORUMUNSUBSCRIBE'] = 'Forum-Abo beenden';
$LANG_GF01['NEWTOPIC']       = 'Neues Thema';
$LANG_GF01['POSTREPLY']      = 'Antwort schreiben';
$LANG_GF01['SubscribeLink']  = 'Abonnieren';
$LANG_GF01['unSubscribeLink'] = 'Abo beenden';
$LANG_GF01['FORUMSUBSCRIBE'] = 'Forum-Abo beenden';
$LANG_GF01['NEWTOPIC']       = 'Neues Thema';
$LANG_GF01['POSTREPLY']      = 'Antwort schreiben';
$LANG_GF01['SUBSCRIPTIONS']  = 'Abonnements';
$LANG_GF01['TOP']            = 'Top of Post';
$LANG_GF01['PRINTABLE']      = 'Druckfähige Version';
$LANG_GF01['ForumProfile']   = 'Forum-Optionen';
$LANG_GF01['USERPREFS']      = 'Forum-Einstellungen';
$LANG_GF01['SPEEDLIMIT']     = '"Your last comment was %s seconds ago.<br>This site requires at least %s seconds between forum posts."';
$LANG_GF01['ACCESSERROR']    = 'ACCESS ERROR';
$LANG_GF01['LEGEND']         = 'Legende';
$LANG_GF01['ACTIONS']        = 'Aktionen';
$LANG_GF01['DELETEALL']      = 'Alle ausgewählten Einträge löschen';
$LANG_GF01['DELCONFIRM']     = 'Bist Du sicher, dass Du die ausgewählten Einträge löschen willst?';
$LANG_GF01['DELALLCONFIRM']  = 'Bist Du sicher, dass Du ALLE ausgewählten Einträge löschen willst?';
$LANG_GF01['STARTEDBY']      = 'Begonnen von ';
$LANG_GF01['WARNING']        = 'Warnung';
$LANG_GF01['MODERATED']      = 'Moderatoren: %s';
$LANG_GF01['NOTIFYNOT']      = 'NICHT!';
$LANG_GF01['LASTREPLYBY']    = 'Letzter Beitrag von:&nbsp;%s';
$LANG_GF01['UID']            = 'UID';
$LANG_GF01['ANON_POST_BEGIN'] = 'Anonymous messages';
$LANG_GF01['ANON_POST_END']   = 'viewable';
$LANG_GF01['INDEXPAGE']      = 'Alle Foren';
$LANG_GF01['FEATURE']        = 'Feature';
$LANG_GF01['SETTING']        = 'Einstellung';
$LANG_GF01['MARKALLREAD']    = 'Mark All Read';

// Language for bbcode toolbar
$LANG_GF01['CODE']           = 'Code';
$LANG_GF01['FONTCOLOR']      = 'Schriftfarbe';
$LANG_GF01['FONTSIZE']       = 'Schriftgröße';
$LANG_GF01['CLOSETAGS']      = 'Tags schließen';
$LANG_GF01['CODETIP']        = 'Tip: Styles can be applied quickly to selected text';
$LANG_GF01['TINY']           = 'Winzig';
$LANG_GF01['SMALL']          = 'Klein';
$LANG_GF01['NORMAL']         = 'Normal';
$LANG_GF01['LARGE']          = 'Groß';
$LANG_GF01['HUGE']           = 'Riesig';
$LANG_GF01['DEFAULT']        = 'Standard';
$LANG_GF01['DKRED']          = 'Dunkelrot';
$LANG_GF01['RED']            = 'Rot';
$LANG_GF01['ORANGE']         = 'Orange';
$LANG_GF01['BROWN']          = 'Braun';
$LANG_GF01['YELLOW']         = 'Gelb';
$LANG_GF01['GREEN']          = 'Grün';
$LANG_GF01['OLIVE']          = 'Olive';
$LANG_GF01['CYAN']           = 'Cyan';
$LANG_GF01['BLUE']           = 'Blau';
$LANG_GF01['DKBLUE']         = 'Dunkelblau';
$LANG_GF01['INDIGO']         = 'Indigo';
$LANG_GF01['VIOLET']         = 'Lila';
$LANG_GF01['WHITE']          = 'Weiß';
$LANG_GF01['BLACK']          = 'Schwarz';

$LANG_GF01['b_help']         = "Fettschrift: [b]text[/b]";
$LANG_GF01['i_help']         = "Schräg gestellt: [i]text[/i]";
$LANG_GF01['u_help']         = "Unterstrichen: [u]text[/u]";
$LANG_GF01['q_help']         = "Zitat: [quote]text[/quote]";
$LANG_GF01['c_help']         = "Quelltext: [code]code[/code]";
$LANG_GF01['l_help']         = "Liste: [list]text[/list]";
$LANG_GF01['o_help']         = "Nummerierte Liste: [olist]text[/olist]";
$LANG_GF01['p_help']         = "[img]http://image_url[/img] oder [img w=100 h=200][/img]";
$LANG_GF01['w_help']         = "URL: [url]http://url[/url] or [url=http://url]URL text[/url]";
$LANG_GF01['a_help']         = "Alle offenen BBcode-Tags schließen";
$LANG_GF01['s_help']         = "Schriftfarbe: [color=red]text[/color]  Tipp: Du kannst auch color=#FF0000 benutzen";
$LANG_GF01['f_help']         = "Schriftgröße: [size=x-small]small text[/size]";
$LANG_GF01['h_help']         = 'Ausführliche Hilfe';


$LANG_GF02['msg01']    = 'Sorry you must register to use these forums';
$LANG_GF02['msg02']    = 'You should not be here!<br>Restricted access to this forum only';
$LANG_GF02['msg03']    = '';
$LANG_GF02['msg04']    = '';
$LANG_GF02['msg05']    = '<CENTER><I>Sorry, no topics have been created yet.</CENTER></I>';
$LANG_GF02['msg06']    = ' new posts since your last visit';
$LANG_GF02['msg07']    = 'Wer ist online?';
$LANG_GF02['msg08']    = '<br><B>Total Registered Users to Date:</B> %s';
$LANG_GF02['msg09']    = '<br><B>Total Posts to Date:</B> %s <br>';
$LANG_GF02['msg10']    = '<B>Total Topics to Date:</B> %s <br>';
$LANG_GF02['msg11']    = 'Back to Forum Index';
$LANG_GF02['msg12']    = 'Back to Main Homepage';
$LANG_GF02['msg13']    = 'Registration Required, you currently must register or login to use this feature.';
$LANG_GF02['msg14']    = 'Sorry, You have been banned from making entries.<br>';
$LANG_GF02['msg15']    = 'If you feel this is an error, contact <A HREF="mailto:%s?subject=Forum IP Ban">Site Admin</A>.';
$LANG_GF02['msg16']    = 'These are the most popular posts, you may order them by views or replies.';
$LANG_GF02['msg17']    = 'Message Edited, Your message has been edited sucessfully. Returning to your message.';
$LANG_GF02['msg18']    = 'Fehler! Es wurden nicht alle benötigten Felder ausgefüllt oder die Einträge waren zu kurz.';
$LANG_GF02['msg19']    = 'Dein Beitrag wurde veröffentlicht.';
$LANG_GF02['msg20']    = 'Reply Added, Your reply has been posted. Returning to Forum';
$LANG_GF02['msg21']    = 'Sorry, you are unauthorized to do this. Please <a href="javascript:history.back()">Go Back</a> or <a href="%s/users.php?mode=login">Login</a><br><br>'; 
$LANG_GF02['msg22']    = '- Neuer Beitrag im Forum';
$LANG_GF02['msg23a']   = "Zum Thema '%s' hat %s eine Antwort geschrieben.\n\nDas Thema wurde von %s im %s-Forum begonnen. ";
$LANG_GF02['msg23b']   = "Ein neues Thema, '%s', wurde von %s im %s-Forum auf der %s-Website begonnen. Du kannst den Beitrag hier lesen:\n%s/forum/viewtopic.php?showtopic=%s\n";
$LANG_GF02['msg23c']   = "Du kannst den Beitrag hier lesen:\n%s/forum/viewtopic.php?showtopic=%s&lastpost=true\n";
$LANG_GF02['msg24']    = 'You may see the thread and reply at: %s/forum/viewtopic.php?forum=%s&showtopic=%s';
$LANG_GF02['msg25']    = "\nViel Spaß,\n";
$LANG_GF02['msg26']    = "\nDu bekommst diese E-Mail, da Du die Benachrichtigung für dieses Thema aktiviert hast. ";
$LANG_GF02['msg27']    = "Deine Benachrichtigungen kannst Du unter <%s> löschen.\n";
$LANG_GF02['msg28']    = 'Error, No subject for this post';
$LANG_GF02['msg29']    = 'Your signature will be placed here.';
$LANG_GF02['msg30']    = 'Back to top';
$LANG_GF02['msg31']    = '<b>You can still edit it here:</b>';
$LANG_GF02['msg32']    = '<b>Edit Message</b>';
$LANG_GF02['msg33']    = 'Autor: ';
$LANG_GF02['msg34']    = 'E-Mail:';
$LANG_GF02['msg35']    = 'Website:';
$LANG_GF02['msg36']    = 'Stimmung:';
$LANG_GF02['msg37']    = 'Message:';
$LANG_GF02['msg38']    = 'Bei Antworten benachrichtigen ';
$LANG_GF02['msg39']    = '<br>There are no topic reviews for this new topic.';
$LANG_GF02['msg40']    = '<br>Sorry, but you have already asked to be notified of replies to this topic.<br><br>';
$LANG_GF02['msg41']    = '<br>Thank you! You will now be notified of replies to topic %s .<br><br>';
$LANG_GF02['msg42']    = 'Thank you! You have now deleted notifications on this topic.';
$LANG_GF02['msg43']    = 'Are you sure you want to delete this notification?.';
$LANG_GF02['msg44']    = '<p style="margin:0px; padding:5px;">Du hast keine Benachrichtigungen aktiviert.</p>';
$LANG_GF02['msg45']    = 'Search the Forum';
$LANG_GF02['msg46']    = 'You can search the forum by entering keywords:';
$LANG_GF02['msg47']    = 'You can also specify an author to search under:';
$LANG_GF02['msg48']    = '<br>The Chatterblock Plugin needs to be installed first.';
$LANG_GF02['msg49']    = '(%s Mal gelesen) ';
$LANG_GF02['msg50']    = 'Signatur n/v';
$LANG_GF02['msg51']    = "%s\n\n<br>[Edited at %s by %s]";
$LANG_GF02['msg52']    = 'Confirmed:';
$LANG_GF02['msg53']    = 'Returning to topic..';
$LANG_GF02['msg54']    = 'Post Edited.';
$LANG_GF02['msg55']    = 'Post Deleted.';
$LANG_GF02['msg56']    = 'IP Banned.';
$LANG_GF02['msg57']    = 'Topic Made Sticky.';
$LANG_GF02['msg58']    = 'Topic Un-Stuck.';
$LANG_GF02['msg59']    = 'Normales Thema';
$LANG_GF02['msg60']    = 'Neuer Beitrag';
$LANG_GF02['msg61']    = 'Wichtiges Thema';
$LANG_GF02['msg62']    = 'Benachrichtigung bei neuen Beiträgen';
$LANG_GF02['msg63']    = 'Profil';
$LANG_GF02['msg64']    = 'Are you sure you want to delete topic %s titled: %s ?';
$LANG_GF02['msg65']    = '<br>This is a parent topic, so all replies posted to it will also be deleted.';
$LANG_GF02['msg66']    = 'Confirm Delete Post';
$LANG_GF02['msg67']    = 'Edit Forum Post';
$LANG_GF02['msg68']    = 'Note: BE CAREFUL WHEN YOU BAN, only admins have the rights to unban someone.';
$LANG_GF02['msg69']    = 'Do you really want to ban the ip address: %s?';
$LANG_GF02['msg70']    = 'Confirm Ban';
$LANG_GF02['msg71']    = 'No function selected, choose a post and then a moderator function.<br>Note: You must be a moderator to perform these functions.';
$LANG_GF02['msg72']    = 'Warning, you do not have rights to perform this moderation function.';
$LANG_GF02['msg74']    = 'Latest %s Forum Posts';
$LANG_GF02['msg75']    = 'Top %s Topics By Views';
$LANG_GF02['msg76']    = 'Top %s Topics By Posts';
$LANG_GF02['msg77']    = '<br><p style="padding-left:10px;">You should not be here!<br>Restricted access to this forum only.<p />';
$LANG_GF02['msg78']    = '<br>You should not be here!<br>Invalid Forum.';
$LANG_GF02['msg81']    = '- Topic Edit Notification';
$LANG_GF02['msg82']    = '<p>Your message "%s" has been edited by the moderator %s.<p>';
$LANG_GF02['msg83']    = '<br><br>You need to be signed in to use this forum feature.<p />';
$LANG_GF02['msg84']    = 'Alle Themen als gelesen markieren';
$LANG_GF02['msg85']    = 'Seite:';
$LANG_GF02['msg86']    = '&nbsp;Letzte 10 Beiträge&nbsp;';
$LANG_GF02['msg87']    = '<br>Warning: This topic has been locked by the moderator.<br>No additional posts are permitted';
$LANG_GF02['msg88']    = 'Mitglieder';
$LANG_GF02['msg88b']   = 'Nur im Forum aktive Mitglieder';
$LANG_GF02['msg89']    = 'Meine Benachrichtigungen';
$LANG_GF02['msg100']   = 'Information:';
$LANG_GF02['msg101']   = 'Forum Rules:';
$LANG_GF02['msg102']   = 'Legende:';
$LANG_GF02['msg103']   = 'Forum Jump:';
$LANG_GF02['msg104']   = 'post messages';
$LANG_GF02['msg105']   = 'edit your posts';
$LANG_GF02['msg106']   = 'Forum wählen';
$LANG_GF02['msg107']   = 'Legende:';
$LANG_GF02['msg108']   = 'Aktives Forum';
$LANG_GF02['msg109']   = 'Thema geschlossen';
$LANG_GF02['msg110']   = 'Transferring to message edit page..';
$LANG_GF02['msg111']   = 'Neue Beiträge seit Deinem letzten Besuch';
$LANG_GF02['msg112']   = 'Alle neuen Beiträge anzeigen';
$LANG_GF02['msg113']   = 'Neue Beiträge anzeigen';
$LANG_GF02['msg114']   = 'Thema geschlossen';
$LANG_GF02['msg115']   = 'Wichtiges Thema mit neuen Beiträgen';
$LANG_GF02['msg116']   = 'Geschlossenes Thema mit neuen Beiträgen';
$LANG_GF02['msg117']   = 'Suchen';
$LANG_GF02['msg118']   = 'Im Forum Suchen';
$LANG_GF02['msg119']   = 'Ergebnisse für die Suche nach:';
$LANG_GF02['msg120']   = 'Beliebteste Themen, sortiert nach';
$LANG_GF02['msg121']   = 'Zeitzone: %s. Es ist jetzt %s Uhr.';
$LANG_GF02['msg122']   = 'Beliebtheits-Limit';
$LANG_GF02['msg123']   = 'Anzahl Beiträge, ab denen ein Thema als beliebt gilt';
$LANG_GF02['msg124']   = 'Beiträge pro Seite';
$LANG_GF02['msg125']   = 'Nur für Moderatoren: Beitragsübersicht';
$LANG_GF02['msg126']   = 'Suchergebnisse';
$LANG_GF02['msg127']   = 'Anzahl Zeilen im Suchergebnis';
$LANG_GF02['msg128']   = 'Mitglieder pro Seite';
$LANG_GF02['msg129']   = 'Für die Mitglieder-Liste';
$LANG_GF02['msg130']   = 'Beiträge von Gästen';
$LANG_GF02['msg131']   = 'Beiträge von nicht-angemeldeten Usern zeigen?';
$LANG_GF02['msg132']   = 'Immer benachrichtigen';
$LANG_GF02['msg133']   = 'Automatische Benachrichtigung für alle Themen, die ich beginne oder kommentiere?';
$LANG_GF02['msg134']   = 'Subscription Added';
$LANG_GF02['msg135']   = 'You will now be notified of all posts to this forum.';
$LANG_GF02['msg136']   = 'You must choose a forum to subscribe to.';
$LANG_GF02['msg137']   = 'Notification for topic enabled';
$LANG_GF02['msg138']   = '<b>Ganzes Forum abonniert</b>';
$LANG_GF02['msg139']   = '%s Click <a href="%s">here</a> to continue.';
$LANG_GF02['msg140']   = 'to continue';
$LANG_GF02['msg141']   = 'Es geht gleich weiter. Wenn Du nicht warten willst, bitte <a href="%s">hier</a> klicken.';
$LANG_GF02['msg142']   = 'Notification saved.';
$LANG_GF02['msg143']   = 'Subscribe to this topic';
$LANG_GF02['msg144']   = 'Return to topic';
$LANG_GF02['msg145']   = 'Topic Review';
$LANG_GF02['msg146']   = 'Notification Deleted';
$LANG_GF02['msg147']   = 'Forum [printable version of topic';
$LANG_GF02['msg148']   = '<a href="javascript:history.back()">Zurück.</a>';
$LANG_GF02['msg149']   = 'Send %s an instant message';
$LANG_GF02['msg150']   = 'Your post in %s';
$LANG_GF02['msg151']   = 'Recent forum topics';
$LANG_GF02['msg152']   = 'Most popular viewed topics';
$LANG_GF02['msg153']   = 'Most popular replied to topics';
$LANG_GF02['msg154']   = 'Recent forum topics';
$LANG_GF02['msg155']   = 'No user posts.';
$LANG_GF02['msg156']   = 'Total number of forum posts';
$LANG_GF02['msg157']   = 'Last 10 Forum posts';
$LANG_GF02['msg158']   = 'Last 10 Forum posts by ';
$LANG_GF02['msg159']   = 'Are you sure you want to DELETE these selected Moderator records?';
$LANG_GF02['msg160']   = 'View last page of topic';
$LANG_GF02['msg161']   = 'Return to members list';
$LANG_GF02['msg162']   = 'To return to the forum index now click <a href="%s">here</a><br><p />Default is to  return automatically to view your post.<br>If you do not wish to wait, click <a href="%s">here</a> now.';
$LANG_GF02['msg163']   = 'Post moved';
$LANG_GF02['msg164']   = 'Mark all Categories and Topics Read';
$LANG_GF02['msg165']   = 'ERROR<p />Matching <b>QUOTE</b> tag missing. Unable to format message.<p />';
$LANG_GF02['msg166']   = 'ERROR: Invalid topic or Topic not found';
$LANG_GF02['msg167']   = 'Notification Option';
$LANG_GF02['msg168']   = 'Setting of No will disable email notifictions';
$LANG_GF02['msg169']   = 'Return to Members listing';
$LANG_GF02['msg170']   = 'Aktuelle Themen im Forum';
$LANG_GF02['msg171']   = 'Forum Access Error';
$LANG_GF02['msg172']   = 'Topic does not exist. It possibly has been deleted';
$LANG_GF02['msg173']   = 'Transferring to Post Message page..';
$LANG_GF02['msg174']   = 'Unable to BAN Member - Invalid or Empty IP Address';
$LANG_GF02['msg175']   = 'Zurück zur Forum-Übersicht';
$LANG_GF02['msg176']   = 'Mitglied auswählen';
$LANG_GF02['msg177']   = 'Alle Mitglieder';
$LANG_GF02['msg178']   = 'Nur die Start-Postings';
$LANG_GF02['msg179']   = 'Erzeugt in %s Sekunden';
$LANG_GF02['msg180']   = 'Forum Posting Alert';
$LANG_GF02['msg181']   = 'You don\'t have access to any other forum as a moderator';
$LANG_GF02['msg182']   = 'Moderator Confirmation';
$LANG_GF02['msg183']   = 'New topic was created from this post in forum: %s';
$LANG_GF02['msg184']   = 'Nur einmal benachrichtigen';
$LANG_GF02['msg185']   = 'Soll für neue Beiträge seit meinem letzten Besuch nur eine Benachrichtigung geschickt werden?';
$LANG_GF02['msg186']   = 'New Topic Title';
$LANG_GF02['msg187']   = 'Return to topic - click <a href="%s">here</a>';
$LANG_GF02['msg188']   = 'Zum letzten Beitrag springen';
$LANG_GF02['msg189']   = 'Error: You can not edit this post anymore';
$LANG_GF02['msg190']   = 'Stille Änderung';
$LANG_GF02['msg191']   = 'Edit not permitted. Allowable edit timeframe expired or you need moderator rights';
$LANG_GF02['msg192']   = 'Completed ... Migrated %s topics and %s comments.';
$LANG_GF02['msg193']   = 'STORY&nbsp;&nbsp;TO&nbsp;&nbsp;FORUM&nbsp;&nbsp;MIGRATION&nbsp;&nbsp;UTILITY';
$LANG_GF02['msg194']   = 'Wenig aktives Forum';
$LANG_GF02['msg195']   = 'Zum Forum springen';
$LANG_GF02['msg196']   = 'View the main forum index';
$LANG_GF02['msg197']   = 'Mark topics in all categories as read';
$LANG_GF02['msg198']   = 'Update your forum settings';
$LANG_GF02['msg199']   = 'View or remove forum notifications';
$LANG_GF02['msg200']   = 'View site members report';
$LANG_GF02['msg201']   = 'View popular topics report';


$LANG_GF02['StatusHeading']   = 'Nur zur Information';
$LANG_GF02['PostReply']   = 'Post New Reply';
$LANG_GF02['PostTopic']   = 'Post New Topic';
$LANG_GF02['EditTopic']   = 'Edit Topic';
$LANG_GF02['quietforum']  = 'Keine neuen Beiträge';

$LANG_GF03 = array (
    'welcomemsg'        => 'Hallo Moderator',
    'title'             => 'Moderator-Funktionen:&nbsp;',
    'delete'            => 'Beitrag löschen',
    'edit'              => 'Beitrag ändern',
    'move'              => 'Thema verschieben',
    'split'             => 'Thema aufteilen',
    'ban'               => 'IP sperren',
    'stick'             => 'Wichtiges Thema',
    'unstick'           => 'Normales Thema',
    'movetopic'         => 'Thema verschieben',
    'movetopicmsg'      => '<br>Topic to be moved: "<b>%s</b>"',
    'splittopicmsg'     => '<br>Create a new Topic with this post: "<b>%s</b>"<br><em>By:</em>&nbsp;%s&nbsp <em>On:</em>&nbsp;%s',
    'selectforum'       => 'Select new forum:',
    'lockedpost'        => 'Add Reply Post',
    'splitheading'      => 'Split thread option:',
    'splitopt1'         => 'Move all posts from this point',
    'splitopt2'         => 'Move only this one post'
);

$LANG_GF04 = array (
    'label_forum'             => 'Forum Profile',
    'label_location'          => 'Ort',
    'label_aim'               => 'AIM Handle',
    'label_yim'               => 'YIM Handle',
    'label_icq'               => 'ICQ Identity',
    'label_msnm'              => 'MS Messenger Name',
    'label_interests'         => 'Interests',
    'label_occupation'        => 'Occupation',
);

/* Settings for Additional User profile - Instant Messenging links */
$LANG_GF05 = array (
    'aim_link'               => '&nbsp;<a href="aim:goim?screenname=',
    'aim_linkend'            => '>',
    'aim_hello'              => '&message=Hi.+Are+you+there?',
    'aim_alttext'            => 'AIM:&nbsp;',
    'icq_link'               => '&nbsp;',
    'icq_alttext'            => 'ICQ #:&nbsp;',
    'msn_link'               => '&nbsp;<a href="javascript:MsgrApp.LaunchIMUI(',
    'msn_linkend'            => ')">',
    'msn_alttext'            => 'Messenger:&nbsp;',
    'yim_link'               => '&nbsp;<a href="ymsgr:sendIM?',
    'yim_linkend'            => '">',
    'yim_alttext'            => 'YIM:&nbsp;',
);


/* Admin Navbar */
$LANG_GF06 = array (
    1   => 'Statistik',
    2   => 'Einstellungen',
    3   => 'Foren',
    4   => 'Moderatoren',
    5   => 'Konvertieren',
    6   => 'Beiträge',
    7   => 'IP-Verw.'
);


/* User Functions Navbar */
$LANG_GF07 = array (
    1   => 'View Forums',
    2   => 'Einstellungen',
    3   => 'Beliebte Themen',
    4   => 'Abonnements',
    5   => 'Mitglieder'
);


/* Forum User Features */
$LANG_GF08 = array (
    1   => 'Benachrichtigungen für Themen',
    2   => 'Benachrichtigungen für ganze Foren',
    3   => 'Ausnahmen'
);


$LANG_GF90 = array (
    'viewforums'        => 'Index',
    'stats'             => 'Statistics',
    'settings'          => 'Settings',
    'boardadmin'        => 'Forums',
    'migrate'           => 'Convert',
    'mods'              => 'Moderator',
    'messages'          => 'Messages',
    'ipman'             => 'IP Mgmt'
);

$LANG_GF91 = array (
    'gfstats'            => 'Discussion Forum Stats',
    'statsmsg'           => 'Here are the current statistics for your forum:',
    'totalcats'          => 'Total Categories:',
    'totalforums'        => 'Total Forums:',
    'totaltopics'        => 'Total Topics:',
    'totalposts'         => 'Total Posts:',
    'totalviews'         => 'Total Views:',
    'avgpmsg'            => 'Average posts per:',
    'category'           => 'Category:',
    'forum'              => 'Forum:',
    'topic'              => 'Topic:',
    'avgvmsg'            => 'Average views per:'
);

// Settings.php 
$LANG_GF92 = array (
    'gfsettings'         => 'Discussion Forum Settings',
    'gensettings'        => 'General Settings',
    'gensettings'        => 'General Settings',
    'topicsettings'      => 'Topic Posting Settings',
    'blocksettings'      => 'Latest Posts Block Settings',
    'ranksettings'       => 'Ranking Description Settings',
    'htmlsettings'       => 'Html Settings',
    'avsettings'         => 'Avatar Settings',
    'ranksettings'       => 'Rank Settings',
    'savesettings'       => '    Update Settings    ',
    'allowhtml'          => 'Allow HTML',
    'allowhtmldscp'      => 'Enable HTML to be used in posts. If set to NO then users will only be able to post in TEXT Mode but still use bbcode',
    'glfilter'           => 'Geeklog Filter',
    'glfilterdscp'       => 'Enable Geeklog filtering of HTML',
    'censor'             => 'Censor',
    'censordscp'         => 'Enable Geeklog filtering of bad words',
    'showmoods'          => 'Allow Moods',
    'showmoodsdscp'      => 'Enable moods to be selected per post',
    'allowsmilies'       => 'Allow Smilies',
    'allowsmiliesdscp'   => 'Enable smilies to be used',
    'allownotify'        => 'Allow Notification',
    'allownotifydscp'    => 'Enable Topic update email notification',
    'showiframe'         => 'Themenübersicht',
    'showiframedscp'     => 'Beim Beantworten die bisherige Diskussion in einem IFRAME einblenden',
    'autorefresh'        => 'Auto Refresh',
    'autorefreshdscp'    => 'Automatically refresh page after a submission',
    'refreshdelay'       => 'Pause Delay',
    'refreshdelaydscp'   => 'Pause delay in seconds if autorefresh mode used',
    'xtrausersettings'   => 'Extra User Settings',
    'xtrausersettingsdscp'    => 'Enable optional extra user settings',
    'titleleng'          => 'Title Length',
    'titlelengdscp'      => 'Maximum length (characters) for the topic subject',
    'topicspp'           => 'Themen pro Seite',
    'topicsppdscp'       => 'Anzahl Themen auf der Forum-Indexseite',
    'postspp'            => 'Beiträge pro Seite',
    'postsppdscp'        => 'Anzahl Beiträge pro Seite im Thema',
    'regview'            => 'Register - View',
    'regviewdscp'        => 'Do you need to be registered to view posts',
    'regpost'            => 'Register - Post',
    'regpostdscp'        => 'Do you need to be registered to create posts',
    'imgset'             => 'Image Set',
    'lev1'               => 'Level 1',
    'lev1dscp'           => 'Rank 1 - Desciption and post threshold',
    'lev2'               => 'Level 2',
    'lev2dscp'           => 'Rank 2 - Desciption and post threshold',
    'lev3'               => 'Level 3',
    'lev3dscp'           => 'Rank 3 - Desciption and post threshold ',    
    'lev4'               => 'Level 4',
    'lev4dscp'           => 'Rank 4 - Desciption and post threshold',    
    'lev5'               => 'Level 5',
    'lev5dscp'           => 'Rank 5 - Desciption and post threshold',
    'setsave'            => 'Settings Saved',
    'setsavemsg'         => 'Settings saved.',
    'allownotify'        => 'Allow Notification',
    'allownotifydscp'    => 'Do you want to allow people to be notified?',
    'defaultmode'        => 'Default Post Mode',
    'defaultmodedscp'    => 'Enable HTML Mode as default - set to Yes.<br>Enable Text mode as default (safest) - set to No',
    'cbsettings'         => 'Centerblock Settings',
    'cbenable'           => 'Enable Centerblock',
    'cbenabledscp'       => '',
    'cbhomepage'         => 'Homepage Only',
    'cbhomepagedscp'     => 'Enabled will only show when on page 1',
    'cbposition'         => 'Location',
    'cbpositiondscp'     => 'Placement on the page',
    'position'           => 'Position ',
    'all_topics'         => 'All',
    'no_topic'           => 'Homepage Only',
    'position_top'       => 'Top Of Page',
    'position_feat'      => 'After Featured Story',
    'position_bottom'    => 'Bottom Of Page',
    'messagespp'         => 'Messages per Page',
    'messagesppdscp'     => 'Messages Admin screen - number of messages lines per page',
    'searchespp'         => 'Search Results',
    'searchesppdscp'     => 'Number of records to show per page when viewing search results',
    'minnamelength'      => 'Min Name Length',
    'minnamedscp'        => 'Minimum length in characters requied for members name or anonymous name',
    'mincommentlength'   => 'Min Post Length',
    'mincommentdscp'     => 'Minimum length in characters contents of post must be',
    'minsubjectlength'   => 'Min Subject Length',
    'minsubjectdscp'     => 'Minimum length in characters required for Topic Subject to post',
    'popular'            => 'Popular Posts',
    'populardscp'        => 'Number of views required for topic to have popular rating',
    'convertbreak'       => 'Convert Newlines',
    'convertbreakdscp'   => 'Convert new lines to HTML &lt;BR&gt; tags when viewing posts',
    'speedlimit'         => 'Posting Speedlimit',
    'speedlimitdscp'     => 'Time in seconds required between posts - to prevent spamming',
    'cb_subjectsize'     => 'Title Length',
    'cb_subjectsizedscp' => 'Number of characters allowed in displayed subject',
    'cb_numposts'        => 'Number of posts',
    'cb_numpostsdscp'    => 'Number of posts to show in Centerblock',
    'sb_subjectsize'     => 'Title Length',
    'sb_subjectsizedscp' => 'Number of characters allowed in displayed subject',
    'sb_numposts'        => 'Number of posts',
    'sb_numpostsdscp'    => 'Number of posts to show in latestposts block',
    'sb_latestposts'     => 'Latest Posts',
    'sb_latestpostsdscp' => 'Only show the latest post per topic',
    'userdatefmt'        => 'Date Format',
    'userdatefmtdscp'    => 'Use the user defined preference for Date/Time format where required',
    'spamxplugin'        => 'SpamX Plugin',
    'spamxplugindscp'    => 'Enable the Spam-X Plugin to filter out possible spam on all posts before saving',
    'pmplugin'           => 'PM Plugin',
    'pmplugindscp'       => 'Private Message Plugin is installed and should be enabled',
    'smiliesplugin'       => 'Smilies Plugin',
    'smiliesplugindscp'  => 'Smilies Plugin or external functions should be used for handling smilies',
    'geshiformat'        => 'Code Formatting',
    'geshiformatdscp'    => 'Use the Geshi Code Formatting Feature',
    'edit_timewindow'    => 'Edit Timeframe',
    'edit_timewindowdscp' => 'Allowed time (min) to allow members to edit their posts'


);

// Board Admin
$LANG_GF93 = array (
    'gfboard'            => 'Discussion Forum Board Admin',
    'vieworder'          => 'View Order',
    'addcat'             => 'Add Forum Category',
    'addforum'           => 'Add A Forum',
    'order'              => 'Order:',
    'catorder'           => 'Category Order',
    'forumorder'         => 'Forum Order',
    'catadded'           => 'Category Added.',
    'catdeleted'         => 'Category Deleted',
    'catedited'          => 'Category Edited.',
    'forumadded'         => 'Forum Added.',
    'forumaddError'      => 'Error Adding Forum.',
    'forumdeleted'       => 'Forum Deleted',
    'forumedited'        => 'Forum Edited',
    'forumordered'       => 'Forum Order Edited',
    'transfer'           => 'Transfering to board index..',
    'vieworder'          => 'View Order',
    'back'               => 'Back',
    'addnote'            => 'Note: You can edit these values.',
    'editnote'           => 'Edit Forum Details for: ',
    'editforumnote'      => 'Edit Forum Details for: <b>"%s"</b>',
    'deleteforumnote1'   => 'Do you want to delete the forum <b>"%s"</b>&nbsp;?',
    'editcatnote'        => 'Edit Category Details for: <b>"%s"</b>',
    'deletecatnote1'     => 'Do you want to delete the category <b>"%s"</b>&nbsp;?',
    'deletecatnote2'     => 'All forums and topics posted under those forums will also be deleted.',
    'undercat'           => 'Under Category',
    'deleteforumnote2'   => 'All topics posted under it will also be deleted.',
    'groupaccess'        => 'Group Access: ',
    'rebuild'            => 'Rebuild LastPost Table',
    'action'             => 'Aktionen',
    'forumdescription'   => 'Forum Description',
    'posts'              => 'Posts',
    'ordertitle'         => 'Order',
    'ModDel'             => 'Del',
    'ModEdit'            => 'Edit',
    'ModMove'            => 'Move',
    'ModStick'           => 'Stick',
    'ModBan'             => 'Ban',
    'addmoderator'       => "Add Record",
    'delmoderator'       => " Delete\nSelected",
    'moderatorwarning'   => '<b>Warning: No Forums Defined</b><br><br>Setup Forum Categories and Add at least 1 forum<br>before attempting to add Modertators',
    'private'           => 'Private Forum',
    'filtertitle'       => 'Select Moderator records to view',
    'addmessage'        => 'Add new Moderator',
    'allowedfunctions'  => 'Allowed Functions',
    'userrecords'       => 'User Records',
    'grouprecords'      => 'Group Records',
    'filterview'        => 'Filter View'


);

$LANG_GF94 = array (
    'mod_title'          => 'Forum Moderators',
    'createmod'          => 'Create Moderator',
    'deletemod'          => 'Delete Moderator',
    'currentmods'        => 'Current Moderators:',
    'moderates'          => 'Moderates',
    'deletemsg'          => '(Note: Mod will be deleted immediately you click this button.)',
    'username'           => 'Username:',
    'forforum'           => 'For Forum:',
    'modper'             => 'Permissions:',
    'candelete'          => 'Can Delete:',
    'canban'             => 'Can Ban:',
    'canedit'            => 'Can Edit:',
    'canmove'            => 'Can Move:',
    'canstick'           => 'Can Make Sticky:',
    'addsuc'             => 'Moderator record(s) added successfully.',
    'editsuc'            => 'Moderator edited sucessfully.',
    'removesuc'          => 'Moderator removed successfully from forum: ',
    'removesuc2'         => 'Moderator record(s) removed successfully from all forums.',
    'modexists'          => 'Moderator Exists',
    'modexistsmsg'       => 'Error: Sorry this moderator already exists.',
    'transfer'           => 'Transfering to mod index..',
    'removemodnote1'     => 'Are you want to remove moderator %s from the forum %s?',
    'removemodnote2'     => 'Once deleted, they will no longer be able to moderate that forum.',
    'removemodnote3'     => 'Are you want to remove moderator %s from all forums?',
    'removemodnote4'     => 'Once deleted, they will no longer be able to moderate any forums.',
    'allforums'          => 'All Forums'
);


$LANG_GF95 = array (
    'header1'           => 'Discussion Board Messages',
    'header2'           => 'Discussion Board Messages for forum&nbsp;&raquo;&nbsp;%s',
    'notyet'            => 'Feature has not been implemented yet',
    'delall'            => 'Delete All',
    'delallmsg'         => 'Are you sure you want to delete all messages from: %s?',
    'underforum'        => '<b>Under Forum: %s (ID #%s)',
    'moderate'          => 'Moderieren',
    'nomess'            => 'There have been no messages posted yet! '
);

$LANG_GF96 = array (
    'gfipman'            => 'IP Management',
    'ban'                => 'Ban',
    'noips'              => '<p style="margin:0px; padding:5px;">No IPs have been banned yet!</p>',
    'unban'              => 'Un-Ban',
    'ipbanned'           => 'IP Address Banned',
    'banip'              => 'Ban IP Confirmation',
    'banipmsg'           => 'Are you sure you want to ban the ip %s?',
    'specip'             => 'Please specify an IP Address to ban!',
    'ipunbanned'         => 'IP Address Un-Banned.'
);

// IM.php
$LANG_GF97 = array (
    'msgsent'            => 'Message Sent!',
    'msgsave'            => 'Your message to %s has been sent.',
    'msgreturn'          => 'to return to your inbox.',
    'msgerror'           => 'Your message has not been sent. Please go <a href="javascript:history.back()">back</a> and make sure you have all fields filled.',
    'msgdelok'           => 'Delete Successful',
    'msgdelsuccess'      => 'You have sucessfully deleted this message.',
    'msgdelerr'          => 'The message has not been deleted. Please go <a href=\"javascript:history.back()\">back</a> and choose one.',
    'msgpriv'            => 'Private Messages',
    'msgprivnote1'       => 'You have %s private message.',
    'msgprivnote2'       => 'You have %s private messages.',
    'msgto'              => 'To Username:',
    'msgmembers'         => 'Member List.'
);


$PLG_forum_MESSAGE5 = 'Forum Plugin Upgrade failed - check error.log';

?>
