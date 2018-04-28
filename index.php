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
 * @param CONST INCLUDES_VALKYRIE VARIABLE GLOBAL DE SECURITE DU CMS
 * @uses SECURITY-WRITE-FILE
 */
defined('INCLUDES_VALKYRIE') ? INCLUDES_VALKYRIE : define('INCLUDES_VALKYRIE', true);

defined('ROOT_PATH') ? ROOT_PATH : define('ROOT_PATH', './');
defined('ROOT_INCLUDES') ? ROOT_INCLUDES : define('ROOT_INCLUDES', '/includes/');
$phpEx = substr(strrchr(__FILE__, '.'), 1);
defined('PAGE_NAME') ? PAGE_NAME : define( 'PAGE_NAME', basename( __FILE__, '.' . $phpEx ) );
require_once(ROOT_PATH . ROOT_INCLUDES . 'additional.' . $phpEx);

if ($hook->hook_exist ( 'CHECK_AUTHENTICATION' ))
    $checklog=$hook->execute_hook ( 'CHECK_AUTHENTICATION' );
else {
    echo ('<p>Module \'AUTHENTICATION\' is disabled</p>');
    $checklog=false;
}
if(!$checklog)
{
    $host  = $_SERVER['HTTP_HOST'];
    //$uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
    $extra = 'login.' . $phpEx;
    redirect("http://$host/$extra");
    exit;
}

$template = new template();
$template->CALL('limitless/head.tpl');
$template->CALL('limitless/bottom.tpl');


//DEBUG HACK KOGIUM

IF(!empty($_array_error))
{
	echo('<br><br><table>'.get_object_vars($_array_error[0][6]['previous'])['xdebug_message'].'</table>'); //HACK PDOEXCEPTION TO ENGINE ERROR
    echo '<pre>';
	var_dump($_array_error);
    echo '</pre>';
}

?>