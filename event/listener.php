<?php
/**
 *
 * Top Poster Of The Month. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2005, 2019, 3Di <https://www.phpbbstudio.com>
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
	/* @var \phpbb\request\request */
	protected $request;

	/* @var \phpbb\controller\helper */
	protected $helper;

	/* @var \phpbb\template\template */
	protected $template;

	/* @var \phpbb\user */
	protected $user;

	/* @var \threedi\tpotm\core\tpotm */
	protected $tpotm;

	/* @var string phpEx */
	protected $php_ext;

	/**
	 * Constructor
	 * @param \phpbb\request\request		$request
	 * @param \phpbb\controller\helper		$helper
	 * @param \phpbb\template\template		$template
	 * @param \phpbb\user					$user
	 * @param \threedi\tpotm\core\tpotm		$tpotm
	 * @param								$php_ext
	 * @return void
	 * @access public
	 */
	public function __construct(
		\phpbb\request\request $request,
		\phpbb\controller\helper $helper,
		\phpbb\template\template $template,
		\phpbb\user $user,
		\threedi\tpotm\core\tpotm $tpotm,
		$php_ext
	)
	{
		$this->request		= $request;
		$this->helper		= $helper;
		$this->template		= $template;
		$this->user			= $user;
		$this->tpotm		= $tpotm;

		$this->php_ext		= $php_ext;
	}

	static public function getSubscribedEvents()
	{
		return [
			'core.user_setup'						=>	'load_language_on_setup',
			'core.permissions'						=>	'permissions',
			'core.ucp_prefs_personal_data'			=>	'tpotm_ucp_prefs_data',
			'core.ucp_prefs_personal_update_data'	=>	'tpotm_ucp_prefs_update_data',
			'core.page_header'						=>	'add_page_header_link',
			'core.viewonline_overwrite_location'	=>	'viewonline_page',
			'core.page_header_after'				=>	'tpotm_template_switch',
			'core.user_setup_after'					=>	'display_tpotm',
			'core.viewtopic_cache_user_data'		=>	'viewtopic_tpotm_cache_user_data',
			'core.viewtopic_modify_post_row'		=>	'viewtopic_tpotm',
		];
	}

	public function load_language_on_setup($event)
	{
		$lang_set_ext = $event['lang_set_ext'];
		$lang_set_ext[] = [
			'ext_name' => 'threedi/tpotm',
			'lang_set' => 'common',
		];
		$event['lang_set_ext'] = $lang_set_ext;
	}

	/**
	 * Permission's language file is automatically loaded
	 *
	 * @event core.permissions
	 * @param $event
	 */
	public function permissions($event)
	{
		$categories = $event['categories'];
		$permissions = $event['permissions'];

		if (empty($categories['3Di']))
		{
			/* Setting up a custom CAT */
			$categories['3Di'] = 'ACL_CAT_3DI';

			$event['categories'] = $categories;
		}

		$perms = [
			'u_allow_tpotm_view',
			'a_tpotm_admin',
		];

		foreach ($perms as $permission)
		{
			$permissions[$permission] = ['lang' => 'ACL_' . utf8_strtoupper($permission), 'cat' => '3Di'];
		}

		$event['permissions'] = $permissions;
	}

	/**
	 * Add configuration to Board preferences in UCP
	 * @param $event
	 */
	public function tpotm_ucp_prefs_data($event)
	{
		/**
		 * Check permissions prior to run the code
		 */
		if ($this->tpotm->is_authed())
		{
			/* Includes specified language only in UCP */
			$this->user->add_lang_ext('threedi/tpotm', 'ucp_tpotm');

			/* Collects the user decision */
			$user_tooltip = $this->request->variable('user_tt_tpotm', (bool) $this->user->data['user_tt_tpotm']);
			$user_tooltip_sel = $this->request->variable('user_tt_sel_tpotm', (bool) $this->user->data['user_tt_sel_tpotm']);

			/* Merges the above decision into the already existing array */
			$event['data'] = array_merge($event['data'], [
				'user_tt_tpotm'		=> $user_tooltip,
				'user_tt_sel_tpotm'	=> $user_tooltip_sel,
			]);

			/* Send to template */
			$this->template->assign_vars([
				'TPOTM_UCP_BADGE'		=> $this->tpotm->style_miniprofile_badge('tpotm_badge.png'),
				'S_USER_TOOLTIP'		=> $user_tooltip,
				'S_USER_TOOLTIP_SEL'	=> $user_tooltip_sel,
			]);
		}
	}

	/**
	 * Updates configuration to Board preferences in UCP
	 * @param $event
	 */
	public function tpotm_ucp_prefs_update_data($event)
	{
		/**
		 * Check permissions prior to run the code
		 */
		if ($this->tpotm->is_authed())
		{
			$event['sql_ary'] = array_merge($event['sql_ary'], [
				'user_tt_tpotm'		=> $event['data']['user_tt_tpotm'],
				'user_tt_sel_tpotm'	=> $event['data']['user_tt_sel_tpotm'],
			]);
		}
	}

	/**
	 * Add a link to the controller in the forum navbar
	 */
	public function add_page_header_link()
	{
		/**
		 * Check permissions prior to run the code
		 */
		if ($this->tpotm->is_authed() && $this->tpotm->is_hall())
		{
			$this->template->assign_vars([
				'U_TPOTM_HALL'	=> $this->helper->route('threedi_tpotm_controller'),
			]);
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
		if ($this->tpotm->is_authed() && $this->tpotm->is_hall())
		{
			if ($event['on_page'][1] === 'app' && strrpos($event['row']['session_page'], 'app.' . $this->php_ext . '/tpotm') === 0)
			{
				$event['location'] = $this->user->lang('VIEWING_TPOTM_HALL');

				$event['location_url'] = $this->helper->route('threedi_tpotm_controller');
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
		/**
		 * Check perms first
		 */
		if ($this->tpotm->is_authed())
		{
			$this->tpotm->template_switches_over_all();
		}
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
	 * @param $event
	 */
	public function viewtopic_tpotm_cache_user_data($event)
	{
		/**
		 * Check permissions prior to run the code
		 */
		if ($this->tpotm->is_authed() && $this->tpotm->enable_miniprofile())
		{
			$array = $event['user_cache_data'];
			$array['user_tpotm'] = $event['row']['user_tpotm'];

			/**
			 * The migration created a field in the users table: user_tpotm
			 * Sat as default to be empty string for everyone
			 * Only the TPOTM gets the badge's filename in it.
			 */
			$user_tpotm = [];

			$user_tpotm[] = ($array['user_tpotm']) ? (string) $this->tpotm->style_miniprofile_badge($array['user_tpotm']) : '';

			$array = array_merge($array, $user_tpotm);
			$event['user_cache_data'] = $array;
		}
	}

	/**
	 * Modify the posts template block
	 *
	 * @event core.viewtopic_modify_post_row
	 * @param $event
	 */
	public function viewtopic_tpotm($event)
	{
		/**
		 * Check permissions prior to run the code
		 */
		if ($this->tpotm->is_authed() && $this->tpotm->enable_miniprofile())
		{
			$user_tpotm = (!empty($event['user_poster_data']['user_tpotm'])) ? $this->tpotm->style_miniprofile_badge($event['user_poster_data']['user_tpotm']) : '';

			$event['post_row'] = array_merge($event['post_row'], ['TPOTM_BADGE' => $user_tpotm]);
		}
	}
}
