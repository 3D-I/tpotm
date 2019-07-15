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

/*
 * Adds the column "user_tpotm" to the fields list of the USERS_TABLE
 * Index is being populated with empty string  as default
 */
class m4_install_user_schema extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		/* If doesn't exists go ahead */
		return $this->db_tools->sql_index_exists($this->table_prefix . 'users', 'user_tpotm');
	}

	static public function depends_on()
	{
		return ['\phpbb\db\migration\data\v31x\v3111'];
	}

	public function update_schema()
	{
		return [
			'add_columns'	=> [
				$this->table_prefix . 'users'	=>	[
					'user_tpotm'			=> ['VCHAR:255', ''],
					'user_tt_tpotm'			=> ['BOOL', 0],
					'user_tt_sel_tpotm'		=> ['BOOL', 1],
				],
			],
		];
	}

	public function revert_schema()
	{
		return [
			'drop_columns'	=>[
				$this->table_prefix . 'users'	=>	[
					'user_tpotm',
					'user_tt_tpotm',
					'user_tt_sel_tpotm',
				],
			],
		];
	}
}
