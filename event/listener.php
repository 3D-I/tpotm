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
	protected $helper;
	protected $template;
	protected $user;

	protected $php_ext;
	protected $tpotm;

	/**
		* Constructor
		*
		* @param \phpbb\controller\helper	$helper			Controller helper object
		* @param \phpbb\template\template	$template		Template object
		* @param \phpbb\user				$user			User Object
		*
		* @var string phpEx
		* @param threedi\tpotm\core\tpotm	$tpotm			Methods to be used by Class
		* @access public
		*/
	public function __construct(\phpbb\controller\helper $helper, \phpbb\template\template $template, \phpbb\user $user, $phpExt, \threedi\tpotm\core\tpotm $tpotm)
	{
		$this->helper		= $helper;
		$this->template		= $template;
		$this->user			= $user;

		$this->php_ext		= $phpExt;
		$this->tpotm		= $tpotm;
	}

	static public function getSubscribedEvents()
	{
		return array(
			'core.user_setup'						=>	'load_language_on_setup',
			'core.permissions'						=>	'permissions',
			'core.page_header'						=> 'add_page_header_link',
			'core.viewonline_overwrite_location'	=> 'viewonline_page',
			'core.page_header_after'				=>	'tpotm_template_switch',
			'core.page_footer'						=>	'display_tpotm',
			'core.viewtopic_cache_user_data'		=>	'viewtopic_cache_user_data',
			'core.viewtopic_modify_post_row'		=>	'viewtopic_tpotm',
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
	 * Add a link to the controller in the forum navbar
	 */
	public function add_page_header_link()
	{
		/**
		 * Check permissions prior to run the code
		 */
		if (($this->tpotm->is_authed()) && ($this->tpotm->is_hall()))
		{
			$this->template->assign_vars(array(
				'U_TPOTM_HALL'	=> $this->helper->route('threedi_tpotm_controller', array('name' => $this->user->lang('TPOTM_ROUTE_NAME'))),
			));
		}
	}

	/**
	 * Show users viewing hall of fame on the Who Is Online page
	 *
	 * @param \phpbb\event\data	$event	Event object
	 */
	public function viewonline_page($event)
	{
		/**
		 * Check permissions prior to run the code
		 */
		if (($this->tpotm->is_authed()) && ($this->tpotm->is_hall()))
		{
			if ($event['on_page'][1] === 'app' && strrpos($event['row']['session_page'], 'app.' . $this->php_ext . '/tpotm') === 0)
			{
				$event['location'] = $this->user->lang('VIEWING_TPOTM_HALL');
				$event['location_url'] = $this->helper->route('threedi_tpotm_controller', array('name' => $this->user->lang('TPOTM_ROUTE_NAME')));
			}
		}
	}

	/**
	 * Template switches over all
	 *
	 * @event core.page_header_after
	 */
	public function tpotm_template_switch()
	{
		$this->tpotm->template_switches_over_all();
	}

	public function display_tpotm()
	{
		/**
		 * Check perms first
		 */
		if ($this->tpotm->is_authed())
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
		if (($this->tpotm->is_authed()) && ($this->tpotm->enable_miniprofile()))
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
		if (($this->tpotm->is_authed()) && ($this->tpotm->enable_miniprofile()))
		{
			$user_tpotm = (!empty($event['user_poster_data']['user_tpotm'])) ? $this->tpotm->style_miniprofile_badge($event['user_poster_data']['user_tpotm']) : '';

			$event['post_row'] = array_merge($event['post_row'], array('TPOTM_BADGE' => $user_tpotm));
		}
	}
}
