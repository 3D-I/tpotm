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
	'ERROR_MSG_3111_321_MISTMATCH'	=>	'Minimum 3.1.11 but less than 3.2.0@dev OR greater than 3.2.1 but less than 3.3.0@dev',
	'ERROR_MSG_PHP_VERSION'			=>	'PHP version must be greater than 5.4.0',
]);
