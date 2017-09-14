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

		$this->enable_admin_mod_array	= (bool) $this->config['threedi_tpotm_adm_mods'];
		$this->enable_miniavatar		= (bool) $this->config['threedi_tpotm_miniavatar'];
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
		$this->template->assign_vars(array(
			'S_TPOTM'				=> ($this->auth->acl_get('u_allow_tpotm_view') || $this->auth->acl_get('a_tpotm_admin')) ? true : false,
			'S_TPOTM_INDEX_BOTTOM'	=> ($this->config['threedi_tpotm_index']) ? true : false,
			'S_TPOTM_INDEX_TOP'		=> ($this->config['threedi_tpotm_index']) ? false : true,
			'S_TPOTM_INDEX_FORUMS'	=> ($this->config['threedi_tpotm_forums']) ? true : false,
			'S_TPOTM_AVATAR'		=> ($this->config['threedi_tpotm_miniavatar']) ? true : false,
			'S_TPOTM_MINIPROFILE'	=> ($this->config['threedi_tpotm_miniprofile']) ? true : false,
		));
	}

	public function display_tpotm($event)
	{
		/**
		 * Check perms first
		 */
		if ($this->auth->acl_get('u_allow_tpotm_view') || $this->auth->acl_get('a_tpotm_admin'))
		{
			$now = time();
			$date_today = gmdate("Y-m-d", $now);
			list($year_cur, $month_cur, $day1) = explode('-', $date_today);

			/* Start time for current month */
			$month_start_cur	= gmmktime (0,0,0, $month_cur, 1, $year_cur);
			$month_start		= $month_start_cur;
			$month_end			= $now;

			/* Config time for cache adjustable in ACP */
			$config_time_cache = (int) ($this->config['threedi_tpotm_ttl'] * 60);

			/* Grabs the number of minutes to show for templating purposes */
			$config_time_cache_min = (int) ($this->config['threedi_tpotm_ttl']);

			/**
			 * If we are disabling the cache, the existing information
			 * in the cache file is not valid. Let's clear it.
			 */
			if (($config_time_cache_min) === 0)
			{
				$this->cache->destroy('_tpotm');
			}

			/**
			 * Check cached data
			 * Run the whole stuff only when needed or cache is disabled in ACP
			 */
			if (($row = $this->cache->get('_tpotm')) === false)
			{
				/**
				 * Don't run the code if the admin so wishes
				 */
				if ($this->enable_admin_mod_array)
				{
					$admin_mod_array = array();
				}
				else
				{
					$admin_mod_array = $this->tpotm->admin_mody_ary();
				}

				/**
				 * Gets the complete list of banned users' ids.
				 */
				$ban_ids = $this->tpotm->banned_users_ids();

				/*
					* There can be only ONE, the TPOTM.
					* If same tot posts and same exact post time then the post ID rules
					* Empty arrays SQL errors eated by setting the fourth parm as true within "sql_in_set"
				*/
				$sql = 'SELECT u.username, u.user_id, u.user_colour, u.user_avatar, u.user_avatar_type, u.user_avatar_width, u.user_avatar_height, user_tpotm, MAX(u.user_type), p.poster_id, MAX(p.post_time), COUNT(p.post_id) AS total_posts
					FROM ' . USERS_TABLE . ' u, ' . POSTS_TABLE . ' p
					WHERE u.user_id <> ' . ANONYMOUS . '
						AND u.user_id = p.poster_id
						AND ' . $this->db->sql_in_set('u.user_id', $admin_mod_array, true, true) . '
						AND ' . $this->db->sql_in_set('u.user_id', $ban_ids, true, true) . '
						AND (u.user_type <> ' . USER_FOUNDER . ')
						AND p.post_visibility = ' . ITEM_APPROVED . '
						AND p.post_time BETWEEN ' . $month_start . ' AND ' . $month_end . '
					GROUP BY u.user_id
					ORDER BY total_posts DESC';
				$result = $this->db->sql_query_limit($sql, 1);
				$row = $this->db->sql_fetchrow($result);
				$this->db->sql_freeresult($result);

				/**
				 * If cache is enabled use it
				 */
				if (($config_time_cache_min) >= 1)
				{
					$this->cache->put('_tpotm', $row, (int) $config_time_cache);
				}
			}

			/* Let's show the TPOTM then.. */
			$tpotm_tot_posts = (int) $row['total_posts'];

			/* If no posts for the current elapsed time there is not a TPOTM */
			if ((int) $tpotm_tot_posts < 1)
			{
				$this->tpotm->perform_user_db_clean();
			}
			/* There is a TPOTM, let's update the DB then */
			if ((int) $tpotm_tot_posts >= 1)
			{
				$this->tpotm->perform_user_reset((int) $row['user_id'], (string) $this->tpotm->style_miniprofile_badge());
			}

			/* Only auth'd users can view the profile */
			$tpotm_un_string = ($this->auth->acl_get('u_viewprofile')) ? get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']) : get_username_string('no_profile', $row['user_id'], $row['username'], $row['user_colour']);

			/* Fresh install or when a new Month starts gives zero posts */
			$tpotm_un_nobody = $this->user->lang['TPOTM_NOBODY'];

			$tpotm_post = $this->user->lang('TPOTM_POST', (int) $tpotm_tot_posts);
			$tpotm_cache = $this->user->lang('TPOTM_CACHE', (int) $config_time_cache_min);
			$tpotm_name = ($tpotm_tot_posts < 1) ? $tpotm_un_nobody : $tpotm_un_string;

			$template_vars = array(
				'TPOTM_NAME'		=> $tpotm_name,
				'L_TPOTM_POST'		=> $tpotm_post,
				'L_TPOTM_CACHE'		=> $tpotm_cache,
			);

			/**
			 * Don't run that code if the admin so wishes
			 */
			if ($this->enable_miniavatar)
			{
				// @ToDO: use phpbb_get_avatar here..
				$template_vars += array(
					'TPOTM_AVATAR'		=> (!empty($row['user_avatar_type'])) ? get_user_avatar($row['user_avatar'], $row['user_avatar_type'], $row['user_avatar_width'], $row['user_avatar_height']) : $this->tpotm->style_mini_badge(),

					'U_TPOTM_AVATAR_URL'	=> ($this->auth->acl_get('u_viewprofile')) ? get_username_string('profile', $row['user_id'], $row['username'], $row['user_colour']) : get_username_string('no_profile', $row['user_id'], $row['username'], $row['user_colour']),
				);
			}
			/* You know.. template stuff */
			$this->template->assign_vars($template_vars);
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
		 * Check permission prior to run the code
		 */
		if ( ($this->auth->acl_get('u_allow_tpotm_view') || $this->auth->acl_get('a_tpotm_admin')) && ($this->config['threedi_tpotm_miniprofile']) )
		{
			$array = $event['user_cache_data'];
			$array['user_tpotm'] = $event['row']['user_tpotm'];
			/**
			 * The migration sat a default user_tpotm for everyone that's empty string
			 * Only one user got the formatted HTML string to the image
			 */
			$user_tpotm = array();
			$user_tpotm[] = ($array['user_tpotm']) ? (string) $this->tpotm->style_miniprofile_badge() : '';
			$array = array_merge($array, $user_tpotm);

			$event['user_cache_data'] = $array;
		}
	}

	/**
	 * Modify the posts template block
	 *
	 * @event core.viewtopic_modify_post_row
	 */
	// @ToDo fix display of the results!
	public function viewtopic_tpotm($event)
	{
		/**
		 * Check permission prior to run the code
		 */
		if ( ($this->auth->acl_get('u_allow_tpotm_view') || $this->auth->acl_get('a_tpotm_admin')) && ($this->config['threedi_tpotm_miniprofile']) )
		{
			$event['post_row'] = array_merge($event['post_row'], array(
				'TPOTM_BADGE'	=>	$event['user_poster_data']['user_tpotm'])
			);
		}
	//var_dump($event['user_poster_data']['user_tpotm']);
	}
}
