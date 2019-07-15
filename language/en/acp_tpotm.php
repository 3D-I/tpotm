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
	'TPOTM_GENERAL'							=>	'General Settings',
	// Legend Tpl locations
	'ACP_TPOTM_TEMPLATE_LOCATIONS'			=>	'Template locations',
	'ACP_TPOTM_TEMPLATE_LOCATIONS_EXPLAIN'	=>	'You may choose the location on index to be at the bottom or top of the page but both. In addition you might want to display the result also in viewforum.',
	// Legend Hall of fame
	'ACP_TPOTM_HALL'						=>	'Hall of fame settings',
	// Legend Board Management
	'ACP_TPOTM_MANAGEMENT'					=>	'Board Administration',
	'ACP_TPOTM_MANAGEMENT_EXPLAIN'			=>	'<strong>These setting apply to both results.</strong><br><em>Please note:</em> the Founders option works together with the Admin/mods one. Means if you exclude Admin/mods keeping the Founders ON, the Founders will be not included anyway.',
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
	'ACP_TPOTM_TTL_MODE'					=>	'<em>The dynamic automatic cache</em> takes care of the Leap years and automatically renews at the very begin of each month.<br>When this option is ON the selection in the drop down box is not being considered any more.<br>Selecting NO disables both caches. To enable one of the cache modes select an option (again).',
	'TPOTM_TTL_MODE'						=>	'TPE Dynamic cache',
	'TPOTM_TTL_TPE'							=>	'TPE Time to live',
	'TPOTM_ACP_NO_CACHE'					=>	'no cache',
	'TPOTM_ACP_ONE_DAY'						=>	'one day',
	'TPOTM_ACP_ONE_WEEK'					=>	'one week',
	'TPOTM_ACP_TWO_WEEKS'					=>	'two weeks',
	'TPOTM_ACP_ONE_MONTH'					=>	'one month',
	'ACP_TPOTM_HALL_EPOCH_EXPLAIN'			=>	'Using <em>Epoch</em> the fetch starts since <em>Epoch time 1970-01-01 00:00</em> instead of the start date of the Board. To be used if you did reset at some point that date and some users are being excluded from the <em>Top posters ever</em> list, which is based also on the post time.',
	'TPOTM_HALL_EPOCH'						=>	'Fetch users since',
	'TPOTM_EPOCH'							=>	'Epoch',
	'TPOTM_BOARD_S_DATE'					=>	'Date of when the Board started',
	// Variouses
	'ACP_TPOTM_VARIOUSES'					=>	'Various settings',
	'TPOTM_TTL'								=>	'Time to live',
	'TPOTM_TTL_EXPLAIN'						=>	'Cache time in minutes. 0 = Disabled<br><em>Use the cache to improve performance</em>',
	'TPOTM_AVATAR'							=>	'Mini Avatar',
	'TPOTM_AVATAR_EXPLAIN'					=>	'Display the user avatar in result',
	'TPOTM_MINIPROFILE'						=>	'Mini profile next to posts',
	'TPOTM_MINIPROFILE_EXPLAIN'				=>	'Display the mini-badge',
	'TPOTM_ADM_MODS'						=>	'Include Admin and Moderators',
	'TPOTM_ADM_MODS_EXPLAIN'				=>	'Yes = improves performance<br><em>Excluding them increases DB load, the use of it is preferred in combination with the cache.</em>',
	'TPOTM_FOUNDERS'						=>	'Include Founders',
	'TPOTM_FOUNDERS_EXPLAIN'				=>	'Yes = improves performance<br><em>Use this in combo with the above option.</em>',
	'TPOTM_BANNEDS'							=>	'Include banned users',
	'TPOTM_BANNEDS_EXPLAIN'					=>	'Yes = improves performance<br>',
	// Errors
	'ACP_TPOTM_ERRORS'						=>	'Errors',
	'TPOTM_BADGE_IMG_INVALID'				=>	'The filename of the IMG Badge for the installed style <strong><em>"%1s"</em></strong> is wrong or the relative style folder for the extension is totally missing. Check the relative style and images folders for this extension.<br>The badge filename must be: <em><strong>tpotm_badge.png</strong></em>.<br><em>The ACP of the extension is now in a dormant status and the current settings cannot be modified till the issue has been fixed.</em>',
]);
