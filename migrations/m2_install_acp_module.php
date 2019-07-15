<?php
/**
 *
 * Top Poster Of The Month. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2005, 2019, 3Di <https://www.phpbbstudio.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace threedi\tpotm\migrations;

class m2_install_acp_module extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return ['\phpbb\db\migration\data\v31x\v3111'];
	}

	public function update_data()
	{
		return [
			['module.add', [
				'acp',
				'ACP_CAT_DOT_MODS',
				'ACP_TPOTM_TITLE'
			]],
			['module.add', [
				'acp',
				'ACP_TPOTM_TITLE',
				[
					'module_basename'	=> '\threedi\tpotm\acp\tpotm_module',
					'modes'				=> ['settings'],
				],
			]],
		];
	}
}
