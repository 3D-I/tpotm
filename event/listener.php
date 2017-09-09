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
		* @access public
		*/
	public function __construct(\phpbb\auth\auth $auth, \phpbb\cache\service $cache, \phpbb\config\config $config, \phpbb\db\driver\driver_interface $db, \phpbb\template\template $template, \phpbb\user $user, $root_path, $phpExt)
	{
		$this->auth			= $auth;
		$this->cache		= $cache;
		$this->config		= $config;
		$this->db			= $db;
		$this->template		= $template;
		$this->user			= $user;
		$this->root_path	= $root_path;
		$this->php_ext		= $phpExt;
	}

	static public function getSubscribedEvents()
	{
		return array(
			'core.user_setup'		=> 'load_language_on_setup',
			'core.permissions'		=>	'permissions',
			'core.page_footer'		=> 'display_tpotm',
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

	public function display_tpotm($event)
	{
		/**
		 * Check perms first
		 */
		if ($this->auth->acl_get('u_allow_tpotm_view'))
		{
			$now = time();
			$date_today = gmdate("Y-m-d", $now);
			list($year_cur, $month_cur, $day1) = explode('-', $date_today);

			/* Start time for current month */
			$month_start_cur	= gmmktime (0,0,0, $month_cur, 1, $year_cur);
			$month_start		= $month_start_cur;
			$month_end			= $now;

			/* Config time for cache, hinerits from View online time span */
			$config_time_cache = (int) ($this->config['load_online_time'] * 60);
			/* Grabs the number of minutes to show for templating purposes */
			$config_time_cache_min = (int) ($this->config['load_online_time']);

			/**
			 * Check cached data
			 * Run the whole stuff only when needed
			 */
			if (($row = $this->cache->get('_tpotm')) === false)
			{
				/*
					* Borrowed from Top Five ext
					* grabs all admins and mods, it is a catch all
				*/
				$admin_ary = $this->auth->acl_get_list(false, 'a_', false);
				$admin_ary = (!empty($admin_ary[0]['a_'])) ? $admin_ary[0]['a_'] : array();
				$mod_ary = $this->auth->acl_get_list(false,'m_', false);
				$mod_ary = (!empty($mod_ary[0]['m_'])) ? $mod_ary[0]['m_'] : array();
				/* Groups the above results */
				$admin_mod_array = array_unique(array_merge($admin_ary, $mod_ary));

				/*
					* Borrowed from Ban Hammer ext
					* Check if this user already is banned.
				*/
				if (!function_exists('phpbb_get_banned_user_ids'))
				{
					include($this->root_path . 'includes/functions_user.' . $this->php_ext);
				}
				$ban_ids = phpbb_get_banned_user_ids(array($this->user->data['user_id']));

				/*
					* There can be only ONE, the TPOTM.
					* If same tot posts and same exact post time then the post ID rules
					* Empty arrays SQL errors eated by setting the fourth parm as true within "sql_in_set"
				*/
				$sql = 'SELECT u.username, u.user_id, u.user_colour, MAX(u.user_type), p.poster_id, MAX(p.post_time), COUNT(p.post_id) AS total_posts
					FROM ' . USERS_TABLE . ' u, ' . POSTS_TABLE . ' p
					WHERE u.user_id <> ' . ANONYMOUS . '
						AND u.user_id = p.poster_id
						AND (u.user_type <> ' . USER_FOUNDER . ')
						AND ' . $this->db->sql_in_set('u.user_id', $admin_mod_array, true, true) . '
						AND ' . $this->db->sql_in_set('u.user_id', $ban_ids, true, true) . '
						AND p.post_visibility = ' . ITEM_APPROVED . '
						AND p.post_time BETWEEN ' . $month_start . ' AND ' . $month_end . '
					GROUP BY u.user_id
					ORDER BY total_posts DESC';
				$result = $this->db->sql_query_limit($sql, 1);
				$row = $this->db->sql_fetchrow($result);
				$this->db->sql_freeresult($result);

				/* Caching this data improves performance */
				$this->cache->put('_tpotm', $row, (int) $config_time_cache);
			}

			/* Let's show the TPOTM then.. */
			$tpotm_tot_posts = (int) $row['total_posts'];

			/* only auth'd users can view the profile */
			$tpotm_un_string = ($this->auth->acl_get('u_viewprofile')) ? get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']) : get_username_string('no_profile', $row['user_id'], $row['username'], $row['user_colour']);

			/* Fresh install or when a new Month starts gives zero posts */
			$tpotm_un_nobody = $this->user->lang['TPOTM_NOBODY'];

			$tpotm_post = $this->user->lang('TPOTM_POST', (int) $tpotm_tot_posts);
			$tpotm_cache = $this->user->lang('TPOTM_CACHE', (int) $config_time_cache_min);
			$tpotm_name = ($tpotm_tot_posts < 1) ? $tpotm_un_nobody : $tpotm_un_string;

			/* You know.. template stuff */
			$this->template->assign_vars(array(
				'TPOTM_NAME'		=> $tpotm_name,
				'L_TPOTM_POST'		=> $tpotm_post,
				'L_TPOTM_CACHE'		=> $tpotm_cache,

				'S_U_TPOTM'			=> ($this->auth->acl_get('u_allow_tpotm_view')) ? true : false,
			));
		}
	}
}
