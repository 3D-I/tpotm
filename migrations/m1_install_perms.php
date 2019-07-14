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

class m1_install_perms extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return ['\phpbb\db\migration\data\v31x\v3111'];
	}

	public function update_data()
	{
		return [
			['permission.add', ['u_allow_tpotm_view']],
			['permission.permission_set', ['REGISTERED', 'u_allow_tpotm_view', 'group']],
			['permission.add', ['a_tpotm_admin']],
			['permission.permission_set', ['ADMINISTRATORS', 'a_tpotm_admin', 'group']],
		];
	}
}
