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
	// Legend Tpl locations
	'ACP_TPOTM_TEMPLATE_LOCATIONS'			=>	'Template locations',
	'ACP_TPOTM_TEMPLATE_LOCATIONS_EXPLAIN'	=>	'You may choose the location on index to be at the bottom or top of the page but both. In addition you might want to display the result also in viewforum.',
	// Legend Hall of fame
	'ACP_TPOTM_HALL'						=>	'Hall of fame settings',
	// Legend Board Management
	'ACP_TPOTM_MANAGEMENT'					=>	'Board Administration',
	'ACP_TPOTM_MANAGEMENT_EXPLAIN'			=>	'Please note: the Founders option works together with the Admin/mods one. Means if you exclude Admin/mods keeping the Founders ON, the Founders will be not included anyway.',
	// Tpl locations
	'TPOTM_INDEX'							=>	'Index page',
	'TPOTM_BOTTOM'							=>	'Bottom',
	'TPOTM_TOP'								=>	'Top',
	'TPOTM_FORUMS'							=>	'Forum pages',
	// Hall of fame
	'TPOTM_HALL'							=>	'Viewport',
	'TPOTM_HALL_EXPLAIN'					=>	'Enable the page',
	'TPOTM_USERS_PAGE'						=>	'Top posters',
	'TPOTM_USERS_PAGE_EXPLAIN'				=>	'How many users to show per page',
	// Variouses
	'ACP_TPOTM_VARIOUSES'					=>	'Various settings',
	'TPOTM_TTL'								=>	'Time to live',
	'TPOTM_TTL_EXPLAIN'						=>	'Cache time in minutes. 0 = Disabled<br><em>Use the cache to improve performance</em>',
	'TPOTM_AVATAR'							=>	'Mini Avatar',
	'TPOTM_AVATAR_EXPLAIN'					=>	'Display the user avatar in result',
	'TPOTM_MINIPROFILE'						=>	'Mini profile next to posts',
	'TPOTM_MINIPROFILE_EXPLAIN'				=>	'Display the mini-badge',
	'TPOTM_ADM_MODS'						=>	'Admin and Moderators',
	'TPOTM_ADM_MODS_EXPLAIN'				=>	'Yes = improves performance<br><em>Excluding them increases DB load, the use of it is preferred in combination with the cache.</em>',
	'TPOTM_FOUNDERS'						=>	'Founders',
	'TPOTM_FOUNDERS_EXPLAIN'				=>	'Yes = improves performance<br><em>Use this in combo with the above option.</em>',
	// Errors
	'ACP_TPOTM_ERRORS'						=>	'Errors explaination',
	'TPOTM_BADGE_IMG_INVALID'				=>	'The Badge filename is wrong or totally missing. Check your relative extension/style <strong>Images</strong> folder.<br>Badge filename must be: <strong>tpotm_badge.png</strong>.<br>Extension ACP it is now in a dormant status.<br>Fix the issue and it will automatically wake-up at runtime.',
));
