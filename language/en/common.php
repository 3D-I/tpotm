<?php
/**
 *
 * Top Poster Of The Month. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2005, 2019, 3Di <https://www.phpbbstudio.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
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
	$lang = [];
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

$lang = array_merge($lang, [
	'TPOTM_CAT'						=> 'Top Poster of the Month',
	'TPOTM_EVER_CAT'				=> 'Top Posters ever',
	'TPOTM_NOW'						=> 'At the present time is ',
	'TPOTM_NOBODY'					=> 'not yet available ',
	'TPOTM_BADGE'					=> 'Badge ?',
	'TPOTM_BADGE_MINIPROFILE'		=> 'Top poster of the Month',
	'TOTAL_MONTH'					=> ' out of <strong>%1s</strong> (%2s%%)',
	'TPOTM_EXPLAIN'					=> 'From %1s to %2s',
	'TPOTM_NO_EXPLAIN'				=> 'During the current month',
	'TPOTM_TOT_POST'				=> 'Total posts',
	'TPOTM_DATE'					=> 'Year and Month',
	'TPOTM_LAST_POST_IN_MONTH'		=> 'Last on',

	'TPOTM_CACHE'	=> [
		0	=> ' <i>[updates at every page load]</i>',
		1	=> ' <i>[updates every <strong>%d</strong> minute]</i>',
		2	=> ' <i>[updates every <strong>%d</strong> minutes]</i>',
	],

	'TPOTM_POST'	=> [
		1	=> ' with a total of <strong>%d</strong> post',
		2	=> ' with a total of <strong>%d</strong> posts',
	],

	// Translators please do not change the following line, no need to translate it!
	'TPOTM_CREDIT_LINE'		=>	' | <a href="https://github.com/3D-I/tpotm">Top Poster Of The Month</a> &copy; 2005, 2018 - 3Di',

	// Hall of fame
	'VIEWING_TPOTM_HALL'			=> 'Viewing TPOTM Hall of fame',
	'TPOTM_PAGE'					=> 'Hall of fame',
	'TPOTM_HELLO'					=> 'Top Poster Of The Month - Hall Of Fame',
	'TPOTM_EXPLAIN_HALL'			=> 'From %1s to %2s',
	'TPOTM_HALL_NO_EXPLAIN'			=> 'Since Epoch till the very end of the previous month',
	'TPOTM_HALL_NO_TOP_POSTERS'		=> 'There are no past Top Posters yet to display.',

	'HALL_OF_FAME'	=> [
		0	=> 'Hall of fame',
		1	=> 'Hall of fame &bull; page %d',
		2	=> 'Hall of fame &bull; page %d',
	],

	'TPOTM_HALL_COUNT'	=> [
		1	=> ' Found a total of <strong>%d</strong> top poster',
		2	=> ' Found a total of <strong>%d</strong> top posters',
	],

	'NOT_AUTHORISED_TPOTM_HALL'		=> 'You are not authorized to see the Hall of fame of Top Poster Of The Month extension.',
	'TPOTM_HALL_DISABLED'			=> 'The Hall of fame of Top Poster Of The Month extension is momentanely disabled.',
]);
