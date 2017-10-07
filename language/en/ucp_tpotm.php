<?php
/**
 *
 * Top Poster Of The Month. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2005,2017, 3Di
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

$lang = array_merge($lang, array(
	'USER_TOOLTIP_HOVER'	=> '<em><< Mouse hover the icon</em>',
	'USER_TOOLTIP'			=> 'Date format in Tooltips',
	'USER_TOOLTIP_EXPLAIN'	=> 'Yes = <em>01 10 2017 00:01</em> to <em>01 11 2027 00:00</em><br>No = Your above date format',
));
