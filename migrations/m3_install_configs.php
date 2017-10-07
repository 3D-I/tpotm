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
		return !phpbb_version_compare($this->config['threedi_tpotm'], '1.2.0-rc1', '>=');
	}

	static public function depends_on()
	{
		return array(
			'\threedi\tpotm\migrations\m2_install_acp_module',
		);
	}

	public function update_data()
	{
		return array(
			array('config.add', array('threedi_tpotm_miniavatar', 0)),
			array('config.add', array('threedi_tpotm_miniprofile', 0)),
			array('config.add', array('threedi_tpotm_hall', 0)),
			array('config.add', array('threedi_tpotm_adm_mods', 1)),
			array('config.add', array('threedi_tpotm_founders', 0)),
			array('config.add', array('threedi_tpotm_forums', 0)),
			array('config.add', array('threedi_tpotm_index', 1)),
			array('config.add', array('threedi_tpotm_ttl', 30)),
			array('config.add', array('threedi_tpotm_badge_exists', 1)),
			array('config.add', array('threedi_tpotm_users_page', 12)),
			array('config.add', array('threedi_tpotm_utc', 'd m Y')),
		);
	}
}
