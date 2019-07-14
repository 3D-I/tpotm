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

class m3_install_configs extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		/**
		 * If does NOT exists go ahead
		 */
		return isset($this->config['threedi_tpotm']);
	}

	static public function depends_on()
	{
		return ['\phpbb\db\migration\data\v31x\v3111'];
	}

	public function update_data()
	{
		return [
			['config.add', ['threedi_tpotm', '2.0.9']],
			['config.add', ['threedi_tpotm_miniavatar', 1]],
			['config.add', ['threedi_tpotm_miniprofile', 1]],
			['config.add', ['threedi_tpotm_hall', 1]],
			['config.add', ['threedi_tpotm_adm_mods', 1]],
			['config.add', ['threedi_tpotm_founders', 1]],
			['config.add', ['threedi_tpotm_banneds', 1]],
			['config.add', ['threedi_tpotm_forums', 1]],
			['config.add', ['threedi_tpotm_index', 0]],
			['config.add', ['threedi_tpotm_ttl', 30]],
			['config.add', ['threedi_tpotm_users_page', 12]],
			['config.add', ['threedi_tpotm_utc', 'd m Y']],
			['config.add', ['threedi_tpotm_since_epoch', 0]],
			['config.add', ['threedi_tpotm_ttl_tpe', 0]],
			['config.add', ['threedi_tpotm_ttl_mode', 1]],
		];
	}
}
