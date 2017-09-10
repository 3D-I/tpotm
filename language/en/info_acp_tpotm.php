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
	'ACP_TPOTM_TITLE'				=> 'Top Poster Of The Month',
	'ACP_TPOTM_SETTINGS'			=> 'Settings',
	'ACP_TPOTM_SETTING_SAVED'		=> 'Top Poster Of The Month Settings saved.',
));