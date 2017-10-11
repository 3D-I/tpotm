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
		return ['\phpbb\db\migration\data\v31x\v3111'];
	}

	public function update_data()
	{
		return [
			/* First set a milestone */
			['config.add', ['threedi_tpotm', '2.0.4-rc']],
			/* Permissions now */
			['permission.add', ['u_allow_tpotm_view']],
			['permission.permission_set', ['REGISTERED', 'u_allow_tpotm_view', 'group']],
			['permission.add', ['a_tpotm_admin']],
			['permission.permission_set', ['ADMINISTRATORS', 'a_tpotm_admin', 'group']],
		];
	}
}
