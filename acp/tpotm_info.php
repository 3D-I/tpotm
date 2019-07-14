<?php
/**
 *
 * Top Poster Of The Month. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2005,2019, 3Di
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace threedi\tpotm\acp;

/**
 * Top Poster Of The Month ACP module info.
 */
class tpotm_info
{
	public function module()
	{
		return [
			'filename'	=> '\threedi\tpotm\acp\tpotm_module',
			'title'		=> 'ACP_TPOTM_TITLE',
			'modes'		=> [
				'settings'	=> [
					'title'	=> 'ACP_TPOTM_SETTINGS',
					'auth'	=> 'ext_threedi/tpotm && acl_a_tpotm_admin',
					'cat'	=> ['ACP_TPOTM_TITLE']
				],
			],
		];
	}
}
