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
		* @param \phpbb\log\log				$log			phpBB log
		* @param \phpbb\user				$user			User object
		* @param \phpbb\extension\manager	$ext_manager	Extension manager object
		* @param \phpbb\path_helper			$path_helper	Path helper object
		* @var string phpBB root path
		* @var string phpEx
		* @access public
	*/

	public function __construct(\phpbb\auth\auth $auth, \phpbb\config\config $config, \phpbb\log\log $log, \phpbb\user $user, \phpbb\extension\manager $ext_manager, \phpbb\path_helper $path_helper, $root_path, $phpExt)
	{
		$this->auth				=	$auth;
		$this->config			=	$config;
		$this->log				=	$log;
		$this->user				=	$user;
		$this->ext_manager		=	$ext_manager;
		$this->path_helper		=	$path_helper;
		$this->ext_path			=	$this->ext_manager->get_extension_path('threedi/tpotm', true);
		$this->ext_path_web		=	$this->path_helper->update_web_root_path($this->ext_path);
		$this->root_path		=	$root_path;
		$this->php_ext			=	$phpExt;
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
	 * Returns an array of users with admin/mod auths
	 *
	 * @return array	empty array otherwise
	 */
	public function admin_mody_ary()
	{
		/**
		 * Inspiration taken from Top Five ext - Thanks to Steve.
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
}
