<?php
/**
 *
 * Top Poster Of The Month. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2005,2017, 3Di
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace threedi\tpotm\event;

/**
* @ignore
*/
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
* Event listener
*/
class listener implements EventSubscriberInterface
{
	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\cache\service */
	protected $cache;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\db\driver\driver */
	protected $db;

	/** @var \phpbb\template\template */
	protected $template;

	/* @var \phpbb\user */
	protected $user;

	/** @var string phpBB root path */
	protected $root_path;

	/** @var string phpEx */
	protected $php_ext;

	/* @var \threedi\tpotm\core\tpotm */
	protected $tpotm;

	/**
		* Constructor
		*
		* @param \phpbb\auth\auth			$auth			Authentication object
		* @param \phpbb\cache\service		$cache
		* @param \phpbb\config\config		$config			Config Object
		* @param \phpbb\db\driver\driver	$db				Database object
		* @param \phpbb\template\template	$template		Template object
		* @param \phpbb\user				$user			User Object
		* @var string phpBB root path
		* @var string phpEx
		* @param threedi\tpotm\core\tpotm		$tpotm			Methods to be used by Class
		* @access public
		*/
	public function __construct(\phpbb\auth\auth $auth, \phpbb\cache\service $cache, \phpbb\config\config $config, \phpbb\db\driver\driver_interface $db, \phpbb\template\template $template, \phpbb\user $user, $root_path, $phpExt, \threedi\tpotm\core\tpotm $tpotm)
	{
		$this->auth			= $auth;
		$this->cache		= $cache;
		$this->config		= $config;
		$this->db			= $db;
		$this->template		= $template;
		$this->user			= $user;
		$this->root_path	= $root_path;
		$this->php_ext		= $phpExt;
		$this->tpotm		= $tpotm;

		$this->enable_miniprofile		= (bool) ($this->config['threedi_tpotm_miniprofile']);
	}

	static public function getSubscribedEvents()
	{
		return array(
			'core.user_setup'					=>	'load_language_on_setup',
			'core.permissions'					=>	'permissions',
			'core.page_header_after'			=>	'tpotm_template_switch',
			'core.page_footer'					=>	'display_tpotm',
			'core.viewtopic_cache_user_data'	=>	'viewtopic_cache_user_data',
			'core.viewtopic_modify_post_row'	=>	'viewtopic_tpotm',
		);
	}

	public function load_language_on_setup($event)
	{
		$lang_set_ext = $event['lang_set_ext'];
		$lang_set_ext[] = array(
			'ext_name' => 'threedi/tpotm',
			'lang_set' => 'common',
		);
		$event['lang_set_ext'] = $lang_set_ext;
	}

	/**
	 * Permission's language file is automatically loaded
	 *
	 * @event core.permissions
	 */
	public function permissions($event)
	{
		$permissions = $event['permissions'];
		$permissions += array(
			'u_allow_tpotm_view' => array(
				'lang'	=> 'ACL_U_ALLOW_TPOTM_VIEW',
				'cat'	=> 'misc',
			),
			'a_tpotm_admin' => array(
				'lang'	=> 'ACL_A_TPOTM_ADMIN',
				'cat'	=> 'misc',
			),
		);
		$event['permissions'] = $permissions;
	}

	/**
	 * Template switches over all
	 *
	 * @event core.page_header_after
	 */
	public function tpotm_template_switch($event)
	{
		$this->tpotm->template_switches_over_all();
	}

	public function display_tpotm($event)
	{
		/**
		 * Check perms first
		 */
		if ($this->auth->acl_get('u_allow_tpotm_view') || $this->auth->acl_get('a_tpotm_admin'))
		{
			/*
			 * There can be only ONE, the TPOTM.
			*/
			$this->tpotm->show_the_winner();
		}
	}

	/**
	 * Modify the users' data displayed within their posts
	 *
	 * @event core.viewtopic_cache_user_data
	 */
	public function viewtopic_cache_user_data($event)
	{
		/**
		 * Check permissions prior to run the code
		 */
		if ( ($this->auth->acl_get('u_allow_tpotm_view') || $this->auth->acl_get('a_tpotm_admin')) && ($this->enable_miniprofile) )
		{
			$array = $event['user_cache_data'];
			$array['user_tpotm'] = $event['row']['user_tpotm'];
			/**
			 * The migration created a field in the users table: user_tpotm
			 * Sat as default to be empty string for everyone
			 * Only the TPOTM gets the badge's filename in it.
			 */
			$user_tpotm = array();

			$user_tpotm[] = ($array['user_tpotm']) ? (string) $this->tpotm->style_miniprofile_badge($array['user_tpotm']) : '';

			$array = array_merge($array, $user_tpotm);
			$event['user_cache_data'] = $array;
		}
	}

	/**
	 * Modify the posts template block
	 *
	 * @event core.viewtopic_modify_post_row
	 */
	public function viewtopic_tpotm($event)
	{
		/**
		 * Check permissions prior to run the code
		 */
		if ( ($this->auth->acl_get('u_allow_tpotm_view') || $this->auth->acl_get('a_tpotm_admin')) && ($this->enable_miniprofile) )
		{
			$user_tpotm = (!empty($event['user_poster_data']['user_tpotm'])) ? $this->tpotm->style_miniprofile_badge($event['user_poster_data']['user_tpotm']) : '';

			$event['post_row'] = array_merge($event['post_row'], array('TPOTM_BADGE' => $user_tpotm));
		}
	}
}
