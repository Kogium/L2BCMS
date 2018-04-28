<?php
/**
 * @copyright (c) 2014-2018 Kogium.
 * @author Kogium <kogium@valkyrie-group.info>
 *
 * @licence CC BY-SA 4.0
 * @licence http://creativecommons.org/licenses/by-sa/4.0/deed.fr
 *
 */

#Protection Lecture du script
#--[start]SECURITY-READ-FILE--#
defined('INCLUDES_VALKYRIE') ? INCLUDES_VALKYRIE : exit;
#--[end]SECURITY-READ-FILE--#

/* TEMPLATE LANGUAGE */
GLOBAL $phpEx,$lang;
$TL_TYPE = basename(__FILE__, '.'.$phpEx);

if (empty($_LANG) || !is_array($_LANG))
{
	GLOBAL $_LANG;
	$_LANG = array();
}

#--[start]LANGUAGE-SYSTEM-BEFORE-FUNCTION--#
$_LANG = array_merge($_LANG, array(

    //TRADUCTION SYSTEME
    'TS_INJECTION_ENGINE'		        => 'L\'injection a été bloquée par le système de sécurité.',
    'TS_BAD_ARGUMENTS'			        => 'Mauvais Arguments',
    'TS_BAD_ENV'				        => 'Erreur d\'éxécution d\'environnement',
    'TS_BAD_FILE_INSTALL'		        => 'Mauvaise Installation des fichiers',
    'TS_BAD_FILE_PERMISSION'	        => 'Mauvaise Permission d\'accès aux fichiers ou Dossiers',
    'TS_ERROR_INCLUDE'			        => 'Erreur d\'inclusion d\'un fichier Système',
    'TS_ERROR_INCLUDE_ONCE'		        => 'Erreur d\'inclusion unique d\'un Système',
    'TS_ERROR_INCLUDE_HOOK'		        => 'Erreur d\'assignement du Hook',

    //TRADUCTION THEME
    'login_title'		                => 'Authentifiez vous sur votre compte',
    'login_title_credential'            => 'Vos identifiants',
    'form_input_username'               => 'Utilisateur',
    'form_input_password'               => 'Mot de Passe',
    'form_input_username_valid'         => 'Entrez votre nom d\'utilisateur',
    'form_input_password_valid'         => 'Entrez votre mot de passe',
    'form_input_remember'               => 'Se souvenir',
    'form_input_forgot_pwd'             => 'Mot de passe oublié?',
    'form_submit'                       => 'Valider',
    'login_form_submit'                 => 'Se connecter',
    'login_question_account'            => 'Vous n\'avez pas de compte?',
    'login_question_calladmin'          => 'Contactez l\'administrateur',
    'client_title'                      => 'Liste des Sociétés',
    'site_title'                        => 'Liste des Sites',

    //TRADUCTION TIMEOUT
    'session_timeout_title'		        => 'Session inactive',
    'session_timeout_message'	        => 'Votre session va expirée dans 5 minutes. Voulez vous rester connecté ?',
    'session_timeout_keepbtn'           => 'Rester connecté',
    'session_timeout_logoutbtn'         => 'Se déconnecter',

    //SYSTEM TRANSLATE
    '{PLUGIN_DISABLE}'                  => 'Plugin désactivé',

    //TRADUCTION MENU
    '{MENU_HEADER_MAIN}'                => 'Navigation',
    '{MENU_LINK_INDEX}'                 => 'Accueil',
    '{MENU_LINK_SOCIETY}'               => 'Société',
    '{UNDER_MENU_LINK_SOCIETY_LIST}'    => 'Liste des Sociétés',
    '{MENU_LINK_SITE}'                  => 'Site',
    '{UNDER_MENU_LINK_SITE_LIST}'       => 'Liste des Sites'
));
#--[end]LANGUAGE-SYSTEM--#

#--[start]SECURITY-SYSTEM--#
IF (!defined('FUNCTION_TEMPLATE_'.$TL_TYPE))
{

    #--[start]LANGUAGE-SYSTEM--#

    /* FUNCTION HERE */

    #--[end]LANGUAGE-SYSTEM--#

    define('FUNCTION_TEMPLATE_'.$TL_TYPE, true);
}
#--[end]SECURITY-SYSTEM--#
?>