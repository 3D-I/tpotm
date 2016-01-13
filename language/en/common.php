<?php
/**
*
* tpotm 1.0.3 [English]
*
* @package phpBB Extension - tpotm 1.0.3-(Top Poster Of The Month)
* @copyright (c) 2005 - 2008 - 2016 3Di (Marco T.)
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* DO NOT CHANGE
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine

$lang = array_merge($lang, array(
	'TPOTM_CAT'				=> 'Top Poster of the Month',
	'TPOTM_NOW'				=> 'At the present time is ',
	'TPOTM_NOBODY'			=> 'not yet available,',

	'TPOTM_CACHE'	=> array(
		1	=> ' (<i>updates every <strong>%d</strong> minute</i>)',
		2	=> ' (<i>updates every <strong>%d</strong> minutes</i>)',
	),

	'TPOTM_POST'	=> array(
		1	=> ' with a total of <strong>%d</strong> post',
		2	=> ' with a total of <strong>%d</strong> posts',
	),
));
