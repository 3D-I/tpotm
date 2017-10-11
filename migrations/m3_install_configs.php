<?php
/**
 *
 * Top Poster Of The Month. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2005,2017, 3Di
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace threedi\tpotm\migrations;

class m3_install_configs extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		/**
		 * If does exists go ahead
		 */
		return !phpbb_version_compare($this->config['threedi_tpotm'], '2.0.3-rc', '>=');
	}

	static public function depends_on()
	{
		return ['\threedi\tpotm\migrations\m2_install_acp_module'];
	}

	public function update_data()
	{
		return [
			['config.add', ['threedi_tpotm_miniavatar', 1]],
			['config.add', ['threedi_tpotm_miniprofile', 1]],
			['config.add', ['threedi_tpotm_hall', 0]],
			['config.add', ['threedi_tpotm_adm_mods', 1]],
			['config.add', ['threedi_tpotm_founders', 1]],
			['config.add', ['threedi_tpotm_banneds', 1]],
			['config.add', ['threedi_tpotm_forums', 0]],
			['config.add', ['threedi_tpotm_index', 1]],
			['config.add', ['threedi_tpotm_ttl', 5]],
			['config.add', ['threedi_tpotm_badge_exists', 1]],
			['config.add', ['threedi_tpotm_users_page', 12]],
			['config.add', ['threedi_tpotm_utc', 'd m Y']],
			['config.add', ['threedi_tpotm_since_epoch', 0]],
		];
	}
}
