<?php
// +--------------------------------------------------------------------------+
// | CAPTCHA Plugin - glFusion CMS                                            |
// +--------------------------------------------------------------------------+
// | french_canada.php                                                        |
// |                                                                          |
// | French Canada language file                                                    |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2002-2014 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// +--------------------------------------------------------------------------+
// |                                                                          |
// | This program is free software; you can redistribute it and/or            |
// | modify it under the terms of the GNU General Public License              |
// | as published by the Free Software Foundation; either version 2           |
// | of the License, or (at your option) any later version.                   |
// |                                                                          |
// | This program is distributed in the hope that it will be useful,          |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of           |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            |
// | GNU General Public License for more details.                             |
// |                                                                          |
// | You should have received a copy of the GNU General Public License        |
// | along with this program; if not, write to the Free Software Foundation,  |
// | Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.          |
// |                                                                          |
// +--------------------------------------------------------------------------+

if (!defined ('GVERSION')) {
    die ('This file can not be used on its own.');
}

###############################################################################

$LANG_CP00 = array(
    'menulabel' => 'CAPTCHA',
    'plugin' => 'CAPTCHA',
    'access_denied' => 'Accès refusé',
    'access_denied_msg' => 'Vous n`avez pas le privilège de sécurité approprié pour accéder à cette page. Votre nom d`utilisateur et IP ont été enregistrées.',
    'admin' => 'Administration CAPTCHA',
    'install_header' => 'CAPTCHA Plugin Installer / Désinstaller',
    'installed' => 'CAPTCHA est installé',
    'uninstalled' => 'CAPTCHA n`est pas installé',
    'install_success' => 'CAPTCHA installation réussie. <br /> <br /> S`il vous plaît examiner la documentation du système et également visiter le <a href="%s">section d`administration</a> pour assurer vos paramètres correspond correctement l`environnement d`hébergement.',
    'install_failed' => 'Échec de l`installation - Consultez votre journal des erreurs pour savoir pourquoi.',
    'uninstall_msg' => 'Plugin succès Uninstalled',
    'install' => 'Installer',
    'uninstall' => 'Désinstallation',
    'warning' => 'Attention! Plugin est toujours activé',
    'enabled' => 'Désactiver le plugin avant de désinstaller.',
    'readme' => 'CAPTCHA Plugin Installation',
    'installdoc' => "<a href=\"{$_CONF['site_admin_url']}/plugins/captcha/install_doc.html\">Installez Document</a>",
    'overview' => 'CAPTCHA est un plugin glFusion natif qui fournit une couche supplémentaire de sécurité pour les robots des spammeurs. <br /> <br /> Un CAPTCHA (un acronyme pour "Entièrement test de Turing public automatisé de dire ordinateurs et les humains à part", marque déposée par la Carnegie Mellon University) est un type de test de défi-réponse utilisé en informatique pour déterminer si oui ou non l`utilisateur est un être humain. En présentant une difficile à lire graphique des lettres et des chiffres, il est supposé que seul un humain peut lire et saisir les caractères correctement. En mettant en œuvre le test CAPTCHA, il devrait contribuer à réduire le nombre d`entrées de Spambot sur ​​votre site.',
    'details' => 'Le plugin CAPTCHA utilisera statiques (déjà) générés images CAPTCHA sauf si vous configurez CAPTCHA pour créer des images dynamiques utilisant la bibliothèque graphique GD ou ImageMagick. Pour utiliser des bibliothèques GD ou ImageMagick, ils doivent prendre en charge les polices True Type. Vérifiez auprès de votre fournisseur d`hébergement pour déterminer si elles soutiennent TTF.',
    'preinstall_check' => 'CAPTCHA a les exigences suivantes:',
    'glfusion_check' => 'v1.0.1 glFusion ou plus, la version indiquée est <b>%s</b>.',
    'php_check' => 'PHP v4.3.0 ou supérieure, version rapportée est <b>%s</b>.',
    'preinstall_confirm' => "Pour plus de détails sur l`installation de CAPTCHA, s`il vous plaît se référer à la <a href=\"{$_CONF['site_admin_url']}/plugins/captcha/install_doc.html\">Manuel d`Installation</a>.",
    'captcha_help' => 'Entrez les caractères',
    'bypass_error' => 'Vous avez tenté de contourner le traitement de CAPTCHA sur ce site, s\'il vous plaît utilisez le lien Nouvel utilisateur pour vous inscrire.',
    'bypass_error_blank' => 'You have attempted to bypass the CAPTCHA processing at this site, please enter a valid CAPTCHA phrase.',
    'entry_error' => 'La chaîne de CAPTCHA saisi ne correspond pas les caractères sur l`image, s`il vous plaît essayer à nouveau. <b> Ceci est la casse.</b>',
    'entry_error_pic' => 'Les images CAPTCHA choisis ne correspondent pas à la demande sur le graphique, s`il vous plaît essayez de nouveau.',
    'captcha_info' => 'Le plugin CAPTCHA fournit une couche de protection contre les robots des spammeurs pour votre site glFusion. voir l` <a href="%s">Documentation Wiki en Ligne</a> for more info.',
    'enabled_header' => 'Paramètres CAPTCHA actuelles',
    'on' => 'On',
    'off' => 'Off',
    'captcha_alt' => 'Vous devez entrer le texte graphique - contacter Site Admin si vous ne parvenez pas à lire le graphique',
    'save' => 'Sauver',
    'cancel' => 'Annuler',
    'success' => 'Options de configuration sauvegardé avec succès.',
    'reload' => 'Recharger',
    'reload_failed' => 'Désolé, ne peut pas autoreload CAPTCHA image \ n soumettez le formulaire et une nouvelle CAPTCHA sera chargé',
    'reload_too_many' => 'Vous ne pouvez demander jusqu`à 5 images rafraîchit',
    'session_expired' => 'Votre CAPTCHA session a expiré, s`il vous plaît essayer à nouveau',
    'picture' => 'Image',
    'characters' => 'Personnages',
    'ayah_error' => 'Sorry, but we were not able to verify you as human. Please try again.',
    'captcha_math' => 'Enter the answer',
    'captcha_prompt' => 'Are You Human?'
);

// Localization of the Admin Configuration UI
$LANG_configsections['captcha'] = array(
    'label' => 'CAPTCHA',
    'title' => 'Configuration CAPTCHA'
);

$LANG_confignames['captcha'] = array(
    'gfxDriver' => 'Pilote Graphique',
    'gfxFormat' => 'Format Graphique',
    'imageset' => 'Statique de l`Image',
    'debug' => 'Debug',
    'gfxPath' => 'Chemin complet de l`utilitaire de conversion de ImageMagick',
    'remoteusers' => 'CAPTCHA vigueur pour tous les utilisateurs distants',
    'logging' => 'Connexion tentatives non valide CAPTCHA',
    'anonymous_only' => 'Anonyme Seulement',
    'enable_comment' => 'Activer Commentaire',
    'enable_story' => 'Activer Histoire',
    'enable_registration' => 'Activer Enregistrement',
    'enable_contact' => 'Activer Contacter',
    'enable_emailstory' => 'Activer Email Histoire',
    'enable_forum' => 'Activer Forum',
    'enable_mediagallery' => 'Activer Media Gallery (Cartes postales)',
    'enable_rating' => 'Activer Note Plugin de Soutien',
    'enable_links' => 'Activer Liens Support Plugin',
    'enable_calendar' => 'Activer Calendrier Plugin de Soutien',
    'expire' => 'Combien de secondes d`une session CAPTCHA est valide',
    'publickey' => 'reCAPTCHA Public Key - <a href="https://www.google.com/recaptcha/admin/create" target=_blank>reCAPTCHA Signup</a>',
    'privatekey' => 'reCAPTCHA Private Key',
    'recaptcha_theme' => 'reCAPTCHA Theme'
);

$LANG_configsubgroups['captcha'] = array(
    'sg_main' => 'Paramètres de Configuration'
);

$LANG_fs['captcha'] = array(
    'cp_public' => 'Paramètres Généraux',
    'cp_integration' => 'Intégration CAPTCHA'
);

// Note: entries 0, 1, and 12 are the same as in $LANG_configselects['Core']
$LANG_configselects['captcha'] = array(
    0 => array('True' => 1, 'False' => 0),
    1 => array('True' => true, 'False' => false),
    2 => array('GD Libs' => 0, 'ImageMagick' => 1, 'Static Images' => 2, 'reCAPTCHA' => 3, 'Math Equation' => 6),
    4 => array('Default' => 'default', 'Simple' => 'simple'),
    5 => array('JPG' => 'jpg', 'PNG' => 'png'),
    6 => array('clean' => 'clean', 'red' => 'red', 'white' => 'white', 'blackglass' => 'blackglass')
);
$PLG_captcha_MESSAGE1 = 'Plugin Captcha mise à niveau: Mise à jour effectuée avec succès.';
$PLG_captcha_MESSAGE2 = 'CAPTCHA Plugin Successfully Installed';
$PLG_captcha_MESSAGE3 = 'CAPTCHA Plugin Successfully Installed';

?>