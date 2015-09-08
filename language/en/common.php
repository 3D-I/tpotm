<?php
/**
*
* tpotm [English]
*
* @copyright (c) 2005 - 2008 - 2015 3Di (Marco T.)
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
	'TOP_CAT'				=> 'Top Poster',
	'TOP_USERNAME'			=> 'The Top Poster of the Month at the present time is ',
	'TOP_USER_MONTH_POSTS'	=> ' with a total of <b>%d</b>',
	'TOP_POSTS'				=> ' posts',
	'TOP_POST'				=> ' post',
	'TOP_USERNAME_NONE'		=> '<b>N/A</b>',
));
