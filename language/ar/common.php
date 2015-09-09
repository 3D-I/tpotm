<?php
/**
*
* tpotm [Arabic]
*
* @copyright (c) 2005 - 2008 - 2015 3Di (Marco T.)
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
* Translated By : Bassel Taha Alhitary - www.alhitary.net
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
	'TOP_CAT'				=> 'أكثر عضو مُشارك',
	'TOP_USERNAME'			=> 'أكثر الأعضاء مُشاركة لهذا الشهر هو ',
	'TOP_USER_MONTH_POSTS'	=> ' بإجمالي مشاركات <b>%d</b>',
	'TOP_POSTS'				=> ' مشاركات',
	'TOP_POST'				=> ' مشاركة',
	'TOP_USERNAME_NONE'		=> '<b>لا يوجد</b>',
));
