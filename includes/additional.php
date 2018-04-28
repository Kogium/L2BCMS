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
 * @category System
 * @package additional
 * @internal fichier chargement du système
 * @param CONST INCLUDES_VALKYRIE VARIABLE GLOBAL DE SECURITE DU CMS
 * @throws exit if constant not declared
 * @uses SECURITY-READ-FILE
 */
defined('INCLUDES_VALKYRIE') ? INCLUDES_VALKYRIE : exit;

/**
 * @internal Type des fichiers Systèmes [permet de remplacer le Multi extension par règles Htaccess]
 * @param extension __FILE__
 * l'extension du fichier config.* permet gérer l'extension de tous les fichiers systèmes
 * @uses GLOBAL $phpEx
 */
GLOBAL $phpEx;
/** @var string extension file system */
$phpEx = !empty($phpEx) ? $phpEx : substr(strrchr(__FILE__, '.'), 1);

/**
 * @internal objet représentant toutes les exceptions du système
 * @uses GLOBAL $_array_error
 */
GLOBAL $_array_error;
/**
 * @var object[] ERROR SYSTEM
 * Initialisation du retour d'exception
 */
$_array_error=array();

/**
 * @package additional\include
 * @internet Appel des fichiers systèmes statics
 */

require_once( 'config.' . $phpEx ); // @TODO: require_once includes/config.php
require_once( 'engine.' . $phpEx ); // @TODO: require_once includes/engine.php
require_once( ROOT_LIBRARY . 'L_ERROR.' . $phpEx ); // @TODO: require_once includes/library/L_ERROR.php
require_once( 'autoloader.' . $phpEx ); // @TODO: require_once includes/autoloader.php
require_once( 'phphooks.' . $phpEx ); // @TODO: require_once includes/phphooks.php
require_once( 'hooker_engine.' . $phpEx ); // @TODO: require_once includes/hooker_engine.php

try
{
	$autoloadManager = new AutoloadManager( $__AUTOLOADER_PATH_CACHE , AutoloadManager::SCAN_ALWAYS );

	$autoloadManager->setAllowedFileExtensions( $__AUTOLOADER_FILE_EXTENSION );
	
	foreach($__AUTOLOADER_INCLUDE AS $KEY => $VALUE)
	{$autoloadManager->addFolder( $VALUE );}
	
	foreach($__AUTOLOADER_EXCLUDE AS $KEY => $VALUE)
	{$autoloadManager->excludeFolder( $VALUE );}
	
	$autoloadManager->register();
}
CATCH(engine_error $e)
{
	$this->status=FALSE;
	__SHOW_ENGINE_EXCEPTION($e);
}
CATCH(Exception $e) //CATCH ALL ERROR
{
	throw new engine_error($e->getMessage(),10,$e);
}

try
{
    GLOBAL $hook;
	$hook = new phphooks ( );

    /** unset means load all plugins in the plugin fold. set it, just load the plugins in this array. */
	$hook->active_plugins = $plugins;

    /** unset means load all modules in the module fold. set it, just load the modules in this array. */
    $hook->active_modules = $modules;

    /** set multiple hooks to which plugin developers can assign functions */
	$hook->set_hooks ( $hooker );
	
	/**
     * load plugins from folder, if no argument is supplied, a 'plugins/' constant will be used
     * trailing slash at the end is REQUIRED!
     * this method will load all *.plugin.php files from given directory, INCLUDING subdirectories
     */
	$hook->load_plugins ();

    /**
     * load modules from folder, if no argument is supplied, a 'modules/' constant will be used
     * trailing slash at the end is REQUIRED!
     * this method will load all *.module.php files from given directory, INCLUDING subdirectories
     */
    $hook->load_modules ();
	
	/**
     * now, this is a workaround because plugins, when included, can't access $hook variable, so we
     * as developers have to basically redefine functions which can be called from plugin files
	 */
	function add_hook($tag, $function, $priority = 10) {
		global $hook;
		$hook->add_hook ( $tag, $function, $priority );
	}
	
	/** same as above */
	function register_plugin($plugin_id, $data) {
		global $hook;
		$hook->register_plugin ( $plugin_id, $data );
	}
}
CATCH(engine_error $e)
{
	__SHOW_ENGINE_EXCEPTION($e);
}
CATCH(Exception $e) //CATCH ALL ERROR
{
	throw new engine_error($e->getMessage(),10,$e);
}

try
{
    GLOBAL $lang;
    $lang = new language();
    $include = new LS_INCLUDE( ROOT_PATH . ROOT_INCLUDES . ROOT_LIBRARY . 'language/VARIABLE_SYSTEM.' . $phpEx,8);
    $lang->SET_TEMPLATE($_VAR_SYSTEM);
}
CATCH(engine_error $e)
{
    $this->status=FALSE;
    __SHOW_ENGINE_EXCEPTION($e);
}
CATCH(Exception $e) //CATCH ALL ERROR
{
    throw new engine_error($e->getMessage(),10,$e);
}

function redirect($filename)
{
    if (!headers_sent())
        header('Location: '.$filename);
    else {
        echo '<script type="text/javascript">';
        echo 'window.location.href="'.$filename.'";';
        echo '</script>';
        echo '<noscript>';
        echo '<meta http-equiv="refresh" content="0;url='.$filename.'" />';
        echo '</noscript>';
    }
}
?>