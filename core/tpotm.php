<?php
/**
 *
 * Top Poster Of The Month. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2005,2017, 3Di
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace threedi\tpotm\core;

class tpotm
{
	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\db\driver\driver */
	protected $db;

	/** @var \phpbb\log */
	protected $log;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\extension\manager "Extension Manager" */
	protected $ext_manager;

	/** @var \phpbb\path_helper */
	protected $path_helper;

	/** @var string phpBB root path */
	protected $root_path;

	/** @var string phpEx */
	protected $php_ext;

	/**
		* Constructor
		*
		* @param \phpbb\auth\auth			$auth			Authentication object
		* @param \phpbb\config\config		$config			Config Object
		* @param \phpbb\db\driver\driver	$db				Database object
		* @param \phpbb\log\log				$log			phpBB log
		* @param \phpbb\user				$user			User object
		* @var string phpBB root path		$root_path
		* @var string phpEx					$phpExt
		* @param \phpbb\extension\manager	$ext_manager	Extension manager object
		* @param \phpbb\path_helper			$path_helper	Path helper object
		* @access public
	*/

	public function __construct(\phpbb\auth\auth $auth, \phpbb\config\config $config, \phpbb\db\driver\driver_interface $db, \phpbb\log\log $log, \phpbb\user $user, \phpbb\extension\manager $ext_manager, \phpbb\path_helper $path_helper, $root_path, $phpExt)
	{
		$this->auth				=	$auth;
		$this->config			=	$config;
		$this->db				=	$db;
		$this->log				=	$log;
		$this->user				=	$user;
		$this->ext_manager		=	$ext_manager;
		$this->path_helper		=	$path_helper;
		$this->root_path		=	$root_path;
		$this->php_ext			=	$phpExt;

		$this->ext_path			=	$this->ext_manager->get_extension_path('threedi/tpotm', true);
		$this->ext_path_web		=	$this->path_helper->update_web_root_path($this->ext_path);
	}

	/**
	 * Returns whether the phpBB is equal or greater than v3.2.1
	 *
	 * @return bool
	 */
	public function is_rhea()
	{
		return phpbb_version_compare(PHPBB_VERSION, '3.2.1', '>=');
	}

	/**
	 * Returns the style related URL and HTML to the image file
	 *
	 * @return string
	 */
	public function style_mini_badge()
	{
		return '<img src="' . ($this->ext_path_web . 'styles/' . rawurlencode($this->user->style['style_path']) . '/theme/images/tpotm_badge.png'). '" alt="' . $this->user->lang('TPOTM_BADGE') . '" />';
	}

	/**
	 * Returns the style related URL and HTML to the miniprofile image file
	 *
	 * @return string
	 */
	public function style_miniprofile_badge()
	{
		return '<img src="' . ($this->ext_path_web . 'styles/' . rawurlencode($this->user->style['style_path']) . '/theme/images/tpotm_badge.png'). '" class="tpotm-miniprofile-badge" alt="' . $this->user->lang('TPOTM_BADGE') . '" />';
	}


	/**
	 * Returns an array of users with admin/mod auths (thx Steve for the idea)
	 *
	 * @return array	empty array otherwise
	 */
	public function admin_mody_ary()
	{
		/**
		 * Inspiration taken from Top Five ext
		 * Grabs all admins and mods, it is a catch all.
		*/
		$admin_ary = $this->auth->acl_get_list(false, 'a_', false);
		$admin_ary = (!empty($admin_ary[0]['a_'])) ? $admin_ary[0]['a_'] : array();
		$mod_ary = $this->auth->acl_get_list(false,'m_', false);
		$mod_ary = (!empty($mod_ary[0]['m_'])) ? $mod_ary[0]['m_'] : array();

		/* Groups the above results */
		return array_unique(array_merge($admin_ary, $mod_ary));
	}

	/**
	 * Gets the complete list of banned users' ids.
	 *
	 * @return array	Array of banned users' ids if any, empty array otherwise
	 */
	public function banned_users_ids()
	{
		if (!function_exists('phpbb_get_banned_user_ids'))
		{
			include($this->root_path . 'includes/functions_user.' . $this->php_ext);
		}

		return phpbb_get_banned_user_ids(array());
	}

	/**
	 * Update the user_tpotm to be false for everyone
	 *
	 * @return void
	 */
	public function perform_user_db_clean()
	{
		$tpotm_sql1 = array(
			'user_tpotm'		=> ''
		);
		$sql1 = 'UPDATE ' . USERS_TABLE . '
			SET ' . $this->db->sql_build_array('UPDATE', $tpotm_sql1) . '
			WHERE user_id <> ' . ANONYMOUS;
		$this->db->sql_query($sql1);
	}

	/**
	 * Update the user_tpotm to be true for the present winner
	 *
	 * @param int $tpotm_user_id the current TPOTM user_id
	 * @param string $tpotm_miniprofile_badge	the style related URL and HTML to the miniprofile image file
	 * @return void
	 */
	public function perform_user_db_update($tpotm_user_id, $tpotm_miniprofile_badge)
	{
		$tpotm_sql2 = array(
			'user_tpotm'		=> (string) $tpotm_miniprofile_badge
		);
		$sql2 = 'UPDATE ' . USERS_TABLE . '
			SET ' . $this->db->sql_build_array('UPDATE', $tpotm_sql2) . '
			WHERE user_id = ' . (int) $tpotm_user_id;
		$this->db->sql_query($sql2);
	}

	/**
	 * Resets the user_tpotm information in the database
	 *
	 * @param int $tpotm_user_id the current TPOTM user_id
	 * @param string $tpotm_miniprofile_badge	the style related URL and HTML to the miniprofile image file
	 * @return void
	 */
	public function perform_user_reset($tpotm_user_id, $tpotm_miniprofile_badge)
	{
		$this->perform_user_db_clean();
		$this->perform_user_db_update($tpotm_user_id, $tpotm_miniprofile_badge);
	}
}
