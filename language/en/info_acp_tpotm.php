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
	'ACL_CAT_3DI'					=>	'3Di',

	'ACP_TPOTM_TITLE'				=> 'Top Poster Of The Month',
	'ACP_TPOTM_SETTINGS'			=> 'Settings',

	'ACP_TPOTM_SETTING_SAVED'		=> 'Top Poster Of The Month Settings saved.',

	// Error log
	'TPOTM_LOG_CONFIG_SAVED'		=> '<strong>TPOTM general configuration saved.</strong>',
	'TPOTM_LOG_BADGE_IMG_INVALID'	=> '<strong>TPOTM - <em>Badge IMG filename(s)</em> is wrong or totally missing.</strong>',
]);
