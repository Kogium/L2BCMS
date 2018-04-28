<?php
/**
 * @copyright (c) 2014-2018 Kogium.
 * @author Kogium <kogium@valkyrie-group.info>
 *
 * @licence CC BY-SA 4.0
 * @licence http://creativecommons.org/licenses/by-sa/4.0/deed.fr
 *
 */

/**
 * @filesource
 * @category System
 * @package config
 * @internal Le fichier config.php est coder avec la vérification ternaire pour sécuriser l'appel des CONSTANTES
 * @param CONST INCLUDES_VALKYRIE VARIABLE GLOBAL DE SECURITE DU CMS
 * @throws exit if constant not declared
 * @uses SECURITY-READ-FILE
 */
defined('INCLUDES_VALKYRIE') ? INCLUDES_VALKYRIE : exit;

/** Annonce aux Dévellopeurs
 *
 * @version 1.0.0
 * @version DEV-PHP 7.0.6
 *
 * @internal PHP Extension
 * -PDO
 * -MBString
 * -Driver SQL version 3.2
 *
 * @internal Compatibilité interpréteur PHP
 * -php 5.6
 * -php 7.x
 * -HTTP HEADER AGENT
 *
 */

/** @package config\INTERNAL */

/** @ignore DEBUG-DEV */
error_reporting(E_ALL & ~E_NOTICE); //DEV
#error_reporting(0); //PROD

/** @internal encodage UTF-8 PHP */
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');

/** @package config\GLOBAL_PATH */

/** Chemin racine du systéme */
defined('ROOT_PATH') ? ROOT_PATH : define('ROOT_PATH', './');

/** Chemin du répertoire de contenu */
defined('ROOT_CONTENT') ? ROOT_CONTENT : define('ROOT_CONTENT', 'content/');

/** Chemin du répertoire des modules */
defined('ROOT_MOD') ? ROOT_MOD : define('ROOT_MOD', 'modules/');

/** Chemin du répertoire des plugins */
defined('ROOT_PLUG') ? ROOT_PLUG : define('ROOT_PLUG', 'plugins/');

/** Chemin du répertoire des langues */
defined('ROOT_LANG') ? ROOT_LANG : define('ROOT_LANG', 'language/');

/** CHEMIN COMPLET DES PLUGINS */
defined('PATH_PLUG') ? PATH_PLUG : define('PATH_PLUG',  ROOT_CONTENT . ROOT_PLUG);

/** CHEMIN COMPLET DES MODULES */
defined('PATH_MOD') ? PATH_MOD : define('PATH_MOD',  ROOT_CONTENT . ROOT_MOD);

/** CHEMIN COMPLET DES FICHIERS DE TRADUCTION */
defined('PATH_LANG') ? PATH_LANG : define('PATH_LANG',  ROOT_CONTENT . ROOT_LANG);

/** Chemin du fichier template par rapport aux includes MAW */
defined('ROOT_TEMPLATE') ? ROOT_TEMPLATE : define('ROOT_TEMPLATE', '/template/');

/** Chemin du fichier theme par rapport a la racine template */
defined('ROOT_THEME') ? ROOT_THEME : define('ROOT_THEME', 'theme/');

/** CHEMIN COMPLET DES FICHIERS DE TRADUCTION */
defined('PATH_LANG_THEME') ? PATH_LANG_THEME : define('PATH_LANG_THEME',  ROOT_CONTENT . ROOT_LANG . ROOT_THEME);

/** Chemin du theme courant */
defined('CURRENT_TEMPLATE_PATH') ? CURRENT_TEMPLATE_PATH : define('CURRENT_TEMPLATE_PATH', ROOT_TEMPLATE . ROOT_THEME . 'limitless/');

/** Chemin du fichier includes par rapport a la racine template */
defined('ROOT_INCLUDES') ? ROOT_INCLUDES : define('ROOT_INCLUDES', '/includes/');

/** Chemin du répertoire de la Librairie */
defined('ROOT_LIBRARY') ? ROOT_LIBRARY : define('ROOT_LIBRARY', 'library/');

/** Chemin du répertoire des moteurs Engine SGBDR */
defined('ROOT_SGBDR_DIRECTORY') ? ROOT_SGBDR_DIRECTORY : define('ROOT_SGBDR_DIRECTORY', ROOT_PATH . ROOT_INCLUDES . ROOT_LIBRARY . 'sgbdr/');

/** @package config\TYPE_EXEC_FILE */

/**
 * @internal Type des fichiers Systèmes [permet de remplacer le Multi extension par règles Htaccess]
 * @param extension __FILE__
 * l'extension du fichier config.* permet gérer l'extension de tous les fichiers systèmes
 * @uses GLOBAL $phpEx
 */
GLOBAL $phpEx;
/** @var string extension file system */
$phpEx = !empty($phpEx) ? $phpEx : substr(strrchr(__FILE__, '.'), 1);


/** @package config\PDO */

/** Host du moteur PDO pour la communication avec la SGBDR */
defined('PDO_HOST') ? PDO_HOST : define('PDO_HOST', '127.0.0.1');

/** Utilisateur du moteur PDO pour la communication avec la SGBDR */
defined('PDO_USER') ? PDO_USER : define('PDO_USER', 'utilisateur');

/** Mot de passe du moteur PDO pour la communication avec la SGBDR */
defined('PDO_PASSWORD') ? PDO_PASSWORD : define('PDO_PASSWORD', 'mot_de_passe');

/** Nom de la Base de donnée du moteur PDO pour la communication avec la SGBDR */
defined('PDO_DATABASE') ? PDO_DATABASE : define('PDO_DATABASE', 'BDD_VALKYRIE');

/** Port de communication du moteur PDO pour la communication avec la SGBDR */
defined('PDO_PORT') ? PDO_PORT : define('PDO_PORT', '1433');

/**
 * Transaction automatique ou manuelle de la SGBDR
 * @uses false = Commit automatique
 * @uses true = commit manuelle
 */
defined('PDO_TRANSACTION') ? PDO_TRANSACTION : define('PDO_TRANSACTION', false);


/** @package config\CACHE */
/** ACTIVE OR NOT CACHE SYSTEM */
defined('CACHE_TEMPLATE') ? CACHE_TEMPLATE : define('CACHE_TEMPLATE', FALSE); //CACHE FALSE IN DEV

/** TIME CACHE */
defined('CACHE_TEMPLATE_TIME') ? CACHE_TEMPLATE_TIME : define('CACHE_TEMPLATE_TIME', 3600); //1h


/** @package config\AUTOLOADER */
/** @uses CONSTANTE GLOBAL AUTOLOADER */
GLOBAL $__AUTOLOADER_PATH_CACHE;
GLOBAL $__AUTOLOADER_ARGS;
GLOBAL $__AUTOLOADER_FILE_EXTENSION;
GLOBAL $__AUTOLOADER_INCLUDE;
GLOBAL $__AUTOLOADER_EXCLUDE;
defined('AUTOLOADER_SCAN_NEVER') ? AUTOLOADER_SCAN_NEVER : define(AUTOLOADER_SCAN_NEVER,0); // 0b000
defined('AUTOLOADER_SCAN_ONCE') ? AUTOLOADER_SCAN_ONCE : define(AUTOLOADER_SCAN_ONCE,1); // 0b001
defined('AUTOLOADER_SCAN_ALWAYS') ? AUTOLOADER_SCAN_ALWAYS : define(AUTOLOADER_SCAN_ALWAYS,3); // 0b011
defined('AUTOLOADER_SCAN_CACHE') ? AUTOLOADER_SCAN_CACHE : define(AUTOLOADER_SCAN_CACHE,4); // 0b100

/** @var string Chemin du cache */
$__AUTOLOADER_PATH_CACHE = ROOT_PATH . ROOT_TEMPLATE . 'cache/autoloader.' . $phpEx;

/** AUTOLOADER SCAN ARGUMENT
 * @var string selection de la constante de scan pour le manageur de librairie automatique
 * @uses DEV => "AutoloadManager::SCAN_ALWAYS" // 0b011
 * @uses PROD => "AutoloadManager::SCAN_CACHE" // 0b100
 * @example "./includes/autoloader.php" 85 4 LES TYPES D'OPTIONS DU SCANNEUR DE CLASSE
 */
$__AUTOLOADER_ARGS = AUTOLOADER_SCAN_ALWAYS; # DEV

/** @var string[] chemin de la librairie à inclure */
$__AUTOLOADER_INCLUDE = array(
    ROOT_PATH . ROOT_INCLUDES . ROOT_LIBRARY
);

/** @var string[] chemin de la librairie à exclure */
$__AUTOLOADER_EXCLUDE = array(
    ROOT_PATH . ROOT_CONTENT . ROOT_PLUG,
    ROOT_PATH . ROOT_CONTENT . ROOT_MOD
);

/** @var string [REGEX]+GLOBAL $phpEx ; type des fichiers systèmes à scanner par le manageur automatique de librairie */
$__AUTOLOADER_FILE_EXTENSION = $phpEx;

/** @package config\COOKIE */
/** @uses CONSTANTE GLOBAL COOKIE */
GLOBAL $__COOKIE_CONFIG;
?>