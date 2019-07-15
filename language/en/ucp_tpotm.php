<?php
/**
 *
 * Top Poster Of The Month. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2005, 2019, 3Di <https://www.phpbbstudio.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = [];
}

$lang = array_merge($lang, [
	'USER_TOOLTIP_HOVER'		=> '<em><< tooltip</em>',
	'USER_TOOLTIP'				=> 'Date format UTC+00:00 in Tooltips',
	'USER_TOOLTIP_EXPLAIN'		=> '<strong>Yes</strong> = Ex. <em>01 10 2017 00:01</em> to <em>01 11 2027 00:00</em><br><strong>No</strong> = Your above date format',
	'USER_TOOLTIP_SEL'			=> 'Display date format in tooltips',
	'USER_TOOLTIP_SEL_EXPLAIN'	=> '<strong>No</strong> = Tooltips will be shown with a standard message ignoring the above selection',
]);
