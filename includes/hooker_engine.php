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
 * @package additional/hooker
 * @internal Autorisation des droits
 * @param CONST INCLUDES_VALKYRIE VARIABLE GLOBAL DE SECURITE DU CMS
 * @throws exit if constant not declared
 * @uses SECURITY-READ-FILE
 */
defined('INCLUDES_VALKYRIE') ? INCLUDES_VALKYRIE : exit;

GLOBAL $phpEx;

/** INSERT INTO $plugins [] => 'plugin1.plugin.php' ALL PLUGIN WITH WHITE ACCESS */
$plugins = array();
$plugin_extension = '.plugin.' . $phpEx ;
//$plugins [] = 'nom_du_plugin' . $plugin_extension ;

/** INSERT INTO $modules [] => 'abc.module.php' ALL MODULE WITH WHITE ACCESS */
$modules = array();
$module_extension = '.module.' . $phpEx ;
$modules [] = 'sns_authenticator' . $module_extension ;
//$modules [] = 'nom_du_module' . $module_extension ;

/** WhiteList hook inside Hooker */
$hooker = array(
    '__INSTALL',
    '__UNINSTALL',
    //AUTHENTICATOR
    'SET_PASSWORD_ACCOUNT',
    'GET_ACTIVATE_ACCOUNT',
    'SET_ACTIVATE_ACCOUNT',
    'AUTHENTICATOR_PASSWORD',
    'OPEN_AUTHENTICATION',
    'CHECK_AUTHENTICATION',
    'CLOSE_AUTHENTICATION',
    'SET_NAVIGATION',
    'GET_NAVIGATION',
    'GET_EXPIRE_NAVIGATION',
    'GET_LASTACTIVITY_NAVIGATION',
);

/*========================================================================*/
/* Example */
/*========================================================================*/

/*
echo '<pre>';
echo 'Contenu Page index';
echo '</pre>';

//place this where you want to execute hooks for "test"
if ($hook->hook_exist ( 'test' )) {
    $hook->execute_hook ( 'test' );
} else {
    echo ('<p><p>no any plugin hooks into TEST!!!</p></p>');
}

//execute hooks for "test1" only if there are hooks to execute
if ($hook->hook_exist ( 'test1' )) {
    $hook->execute_hook ( 'test1' );
} else {
    echo ('<p><p>no any plugin hooks into TEST1!!!</p></p>');
}

//execute hooks for "test2" only if there are hooks to execute
if ($hook->hook_exist ( 'test2' )) {
    $hook->execute_hook ( 'test2' );
} else {
    echo ('<p>no any plugin hooks into TEST2!!!</p>');
}

//execute hooks for "with_args" only if there are hooks to execute
if ($hook->hook_exist ( 'with_args' )) {
    $hook->execute_hook ( 'with_args', time() );
} else {
    echo ('<p>no any plugin hooks on with_args!!!</p>');
}

$urls[] = "ericbess";
$urls[] = "google";

if ($hook->hook_exist ( 'filter' )) {
    echo 'Before filter:</br>' . $urls [0] . '</br>' . $urls [1] . '</br></br>';
    $result = $hook->filter_hook ( 'filter', $urls );
    echo 'After filter:</br>' . $result [0] . '</br>' . $result [1] . '</br>';
} else {
    echo ('<p>no any plugin hooks on filter!!!</p>');
}

//print the the plugins header
echo "<p>Print all plugins hearder</p>";
echo "<pre>";
print_r ( $hook->get_plugins_header () );
echo "</pre>";

//print the the modules header
echo "<p>Print all modules hearder</p>";
echo "<pre>";
print_r ( $hook->get_modules_header () );
echo "</pre>";
*/

/*========================================================================*/
/*========================================================================*/
?>