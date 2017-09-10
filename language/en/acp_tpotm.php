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
	'TPOTM_GENERAL'							=>	'General Settings',
	// Tpl locations
	'ACP_TPOTM_TEMPLATE_LOCATIONS'			=>	'Template locations',
	'ACP_TPOTM_TEMPLATE_LOCATIONS_EXPLAIN'	=>	'You may choose the location on index to be at the bottom or top of the page but both. In addition you might want to display the result also in viewforum.',
	'TPOTM_INDEX'							=>	'Index page',
	'TPOTM_BOTTOM'							=>	'Bottom',
	'TPOTM_TOP'								=>	'Top',
	'TPOTM_FORUMS'							=>	'Forum pages',
	// Variouses
	'ACP_TPOTM_VARIOUSES'					=>	'Variouses',
	'TPOTM_TTL'								=>	'Time to live',
	'TPOTM_TTL_EXPLAIN'						=>	'Cache time in minutes',
	'TPOTM_AVATAR'							=>	'Mini Avatar',
	'TPOTM_AVATAR_EXPLAIN'					=>	'Prepends the TPOTM avatar to the result',
	'TPOTM_MINIPROFILE'						=>	'Mini profile next to posts',
	'TPOTM_MINIPROFILE_EXPLAIN'				=>	'Display the TPOTM mini-badge',
	'TPOTM_HALL'							=>	'Hall of fame',
	'TPOTM_HALL_EXPLAIN'					=>	'Display the HOF link on nav bar',
	'TPOTM_ADM_MODS'						=>	'Admin and Moderators',
	'TPOTM_ADM_MODS_EXPLAIN'				=>	'Include also those users in the TPOTM',
	// Errors
	'ACP_TPOTM_ERRORS'						=>	'Errors explaination',
));
