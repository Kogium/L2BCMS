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
 * @throws exit if constant not declared
 * @uses SECURITY-READ-FILE
 */
defined('INCLUDES_VALKYRIE') ? INCLUDES_VALKYRIE : exit;

/* @global objet de traduction */
GLOBAL $phpEx;
$TL_TYPE = basename(__FILE__, '.'.$phpEx);

#--[start]SECURITY-SYSTEM--#
IF (!defined('FUNCTION_TEMPLATE_'.$TL_TYPE))
{

	#--[start]LANGUAGE-SYSTEM--#

    /* FUNCTION HERE */

	#--[end]LANGUAGE-SYSTEM--#
	
	define('FUNCTION_TEMPLATE_'.$TL_TYPE, true);
}
#--[end]SECURITY-SYSTEM--#

if (empty($_VAR_SYSTEM) || !is_array($_VAR_SYSTEM))
{
	GLOBAL $_VAR_SYSTEM;
	$_VAR_SYSTEM = array();
}

#--[start]LANGUAGE-SYSTEM--#
$_VAR_SYSTEM = array_merge($_VAR_SYSTEM, array(
	'{CURRENT_TEMPLATE_PATH}'		=> CURRENT_TEMPLATE_PATH,
	'{NAME_SITE}'                   => 'VALKYRIE',
    '{NAME_PAGE}'                   => defined('PAGE_NAME') ? PAGE_NAME : 'Valkyrie',
    '{VERSION}'                     => '1.0'
));
#--[end]LANGUAGE-SYSTEM--#
?>