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

class m5_install_configs extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return phpbb_version_compare($this->config['threedi_tpotm'], '2.1.0', '>=');
	}

	static public function depends_on()
	{
		return ['\threedi\tpotm\migrations\m3_install_configs'];
	}

	public function update_data()
	{
		return [
			['config.update', ['threedi_tpotm', '2.1.0']],
		];
	}
}
