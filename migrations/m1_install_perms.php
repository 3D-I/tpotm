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

class m1_install_perms extends \phpbb\db\migration\migration
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
		return array('\phpbb\db\migration\data\v31x\v3111');
	}

	public function update_data()
	{
		return array(
			/* First set a milestone */
			array('config.add', array('threedi_tpotm', '2.0.0-rc2')),
			/* Permissions now */
			array('permission.add', array('u_allow_tpotm_view')),
			array('permission.permission_set', array('REGISTERED', 'u_allow_tpotm_view', 'group')),
			array('permission.add', array('a_tpotm_admin')),
			array('permission.permission_set', array('ADMINISTRATORS', 'a_tpotm_admin', 'group')),
		);
	}
}
