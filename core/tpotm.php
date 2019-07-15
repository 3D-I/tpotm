<?php
/**
 *
 * Top Poster Of The Month. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2005, 2019, 3Di <https://www.phpbbstudio.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace threedi\tpotm\core;

/**
 * Top Poster Of The Month service.
 */
class tpotm
{
	/* @var \phpbb\auth\auth */
	protected $auth;

	/* @var \phpbb\cache\service */
	protected $cache;

	/* @var \phpbb\config\config */
	protected $config;

	/* @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\extension\manager */
	protected $ext_manager;

	/* @var \phpbb\user */
	protected $user;

	/* @var \phpbb\controller\helper */
	protected $path_helper;

	/* @var \phpbb\template\template */
	protected $template;

	/* @var string phpBB root path */
	protected $root_path;

	/* @var string phpEx */
	protected $php_ext;

	/**
	 * Constructor
	 * @param \phpbb\auth\auth                  $auth
	 * @param \phpbb\cache\service              $cache
	 * @param \phpbb\config\config              $config
	 * @param \phpbb\db\driver\driver_interface $db
	 * @param \phpbb\extension\manager          $ext_manager
	 * @param \phpbb\user                       $user
	 * @param \phpbb\path_helper                $path_helper
	 * @param \phpbb\template\template          $template
	 * @param                                   $root_path
	 * @param                                   $phpExt
	 */
	public function __construct(
		\phpbb\auth\auth $auth,
		\phpbb\cache\service $cache,
		\phpbb\config\config $config,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\extension\manager $ext_manager,
		\phpbb\user $user,
		\phpbb\path_helper $path_helper,
		\phpbb\template\template $template,
		$root_path,
		$phpExt
	)
	{
		$this->auth				=	$auth;
		$this->cache			=	$cache;
		$this->config			=	$config;
		$this->db				=	$db;
		$this->ext_manager		=	$ext_manager;
		$this->user				=	$user;
		$this->path_helper		=	$path_helper;
		$this->template			=	$template;

		$this->root_path		=	$root_path;
		$this->php_ext			=	$phpExt;

		$is_dae_enabled			=	$this->ext_manager->is_enabled('threedi/dae');
		$this->is_dae_enabled	=	$is_dae_enabled;

	}

	/**
	 * Returns whether the DAE is enabled and follows some conditions
	 *
	 * @return bool
	 */
	public function is_dae()
	{
		return $this->is_dae_enabled && $this->config['threedi_default_avatar_extended'] && ($this->auth->acl_get('u_dae_user') || $this->auth->acl_get('a_dae_admin'));
	}

	/**
	 * Returns the time for cache adjustable in ACP
	 *
	 * @return int
	 */
	public function config_time_cache()
	{
		return (int) $this->config['threedi_tpotm_ttl'] * 60;
	}

	/**
	 * Returns the number of minutes to show for templating purposes
	 *
	 * @return int
	 */
	public function config_time_cache_min()
	{
		return (int) $this->config['threedi_tpotm_ttl'];
	}

	/**
	 * Returns whether the user is authed
	 *
	 * @return bool
	 */
	public function is_authed()
	{
		return ($this->auth->acl_get('u_allow_tpotm_view') || $this->auth->acl_get('a_tpotm_admin'));
	}

	/**
	 * Returns whether the Hall of fame has been enabled or not
	 *
	 * @return bool
	 */
	public function is_hall()
	{
		return (bool) $this->config['threedi_tpotm_hall'];
	}

	/**
	 * Returns whether the miniavatar has been enabled or not
	 *
	 * @return bool
	 */
	public function enable_miniavatar()
	{
		return (bool) $this->config['threedi_tpotm_miniavatar'];
	}

	/**
	 * Returns whether the miniprofile has been enabled or not
	 *
	 * @return bool
	 */
	public function enable_miniprofile()
	{
		return (bool) $this->config['threedi_tpotm_miniprofile'];
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
	 * Returns the style related URL to the icon mini stats image file for 3.1.x
	 *
	 * @return string	URL
	 */
	public function icon_tpotm_stats_url()
	{
		return generate_board_url() . '/ext/threedi/tpotm/styles/' . rawurlencode($this->user->style['style_path']) . '/theme/images/icon-tpotm-hall.png';
	}

	/**
	 * Returns whether the basic badge img exists
	 *
	 * @return	bool
	 */
	public function style_badge_exists()
	{
		/**
		 * Right or wrong we need to find the correct
		 * path to use on a per location basis
		 */
		$rootpath = (defined('PHPBB_USE_BOARD_URL_PATH') && PHPBB_USE_BOARD_URL_PATH) ? generate_board_url() . '/' : $this->root_path;

		return file_exists($rootpath . 'ext/threedi/tpotm/styles/' . rawurlencode($this->user->style['style_path']) . '/theme/images/tpotm_badge.png');
	}

	/**
	 * Returns the style related URL to the miniprofile badge image file
	 *
	 * @param string	$user_tpotm		the miniprofile image filename with extension
	 * @return string					URL
	 */
	public function style_miniprofile_badge($user_tpotm)
	{
		return generate_board_url() . '/ext/threedi/tpotm/styles/' . rawurlencode($this->user->style['style_path']) . '/theme/images/' . $user_tpotm;
	}

	/**
	 * Returns the style related URL and HTML markup to the miniavatar image file
	 *
	 * @return string	Formatted URL
	 */
	public function style_mini_badge()
	{
		return '<img src="' . generate_board_url() . '/ext/threedi/tpotm/styles/' . rawurlencode($this->user->style['style_path']) . '/theme/images/tpotm_badge.png" />';
	}

	/**
	 * Returns the style related URL and HTML markup to the miniavatar image file for prosilver
	 *
	 * @return string	Formatted URL
	 */
	public function style_mini_badge_prosilver()
	{
		return '<img src="' . generate_board_url() . '/ext/threedi/tpotm/styles/prosilver/theme/images/tpotm_badge.png" />';
	}

	/**
	 * Badge IMG check-point
	 *
	 * @return string	Formatted URL, language string otherwise.
	 */
	public function check_point_badge_img()
	{
		if ( ($this->user->style['style_path'] !== 'prosilver') && $this->style_badge_exists() )
		{
			return $this->style_mini_badge();
		}
		else
		{
			return $this->style_mini_badge_prosilver();
		}
	}

	/**
	 * Updates the user_tpotm to be empty for everyone
	 *
	 * @return void
	 */
	protected function perform_user_db_clean()
	{
		$tpotm_sql1 = [
			'user_tpotm'	=> ''
		];

		$sql1 = 'UPDATE ' . USERS_TABLE . '
			SET ' . $this->db->sql_build_array('UPDATE', $tpotm_sql1) . '
			WHERE user_id <> ' . ANONYMOUS;
		$this->db->sql_query($sql1);
	}

	/**
	 * Updates the user_tpotm with the badge filename for the present winner
	 *
	 * @param int	$tpotm_user_id	the current TPOTM user_id
	 * @return void
	 */
	protected function perform_user_db_update($tpotm_user_id)
	{
		$tpotm_sql2 = [
			'user_tpotm'	=> 'tpotm_badge.png'
		];

		$sql2 = 'UPDATE ' . USERS_TABLE . '
			SET ' . $this->db->sql_build_array('UPDATE', $tpotm_sql2) . '
			WHERE user_id = ' . (int) $tpotm_user_id;
		$this->db->sql_query($sql2);
	}

	/**
	 * Resets the user_tpotm information in the database
	 *
	 * @param int	$tpotm_user_id	the current TPOTM user_id
	 * @return void
	 */
	protected function perform_user_reset($tpotm_user_id)
	{
		$this->db->sql_transaction('begin');

		$this->perform_user_db_clean();
		$this->perform_user_db_update((int) $tpotm_user_id);

		$this->db->sql_transaction('commit');
	}

	/**
	 * Template switches over all
	 *
	 * @return void
	 */
	public function template_switches_over_all()
	{
		$this->template->assign_vars([
			'S_TPOTM'				=> $this->is_authed(),
			'S_IS_RHEA'				=> $this->is_rhea(),
			'S_TPOTM_INDEX_BOTTOM'	=> ($this->config['threedi_tpotm_index']) ? true : false,
			'S_TPOTM_INDEX_TOP'		=> ($this->config['threedi_tpotm_index']) ? false : true,
			'S_TPOTM_INDEX_FORUMS'	=> ($this->config['threedi_tpotm_forums']) ? true : false,
			'S_TPOTM_AVATAR'		=> ($this->config['threedi_tpotm_miniavatar']) ? true : false,
			'S_TPOTM_MINIPROFILE'	=> ($this->config['threedi_tpotm_miniprofile']) ? true : false,
			'S_TPOTM_HALL'			=> ($this->config['threedi_tpotm_hall']) ? true : false,
			'S_U_TOOLTIP_SEL'		=> (bool) $this->user->data['user_tt_sel_tpotm'],
			'TPOTM_ICON_STATS'		=> (string) $this->icon_tpotm_stats_url(),
		]);
	}

	/**
	 * Performs a date range costruction of the current month
	 *
	 * @param int		$hr			24 hrs format like 14
	 * @param int		$min		minutes like 01
	 * @param int		$sec		seconds like 01
	 * @param bool		$start		from the start of the mont yes or not
	 * @param bool		$format		if the data should be user prefs' formatted
	 * @return string	user formatted data range (Thx Steve)
	 */
	protected function get_month_data($hr, $min, $sec, $start = true, $format = false)
	{
		list($year, $month, $day) = explode('-', gmdate("y-m-d", time()));
		$data = gmmktime($hr, $min, $sec, $month, $start ? 1 : date("t"), $year);

		return $format ? $this->user->format_date((int) $data) : (int) $data;
	}

	/**
	 * Gets the Unix Timestamp values for the current month.
	 *
	 * @return array	($month_start, $month_end) Unix Timestamps
	 */
	protected function month_timegap()
	{
		$now = time();
		$date_today = gmdate("Y-m-d", $now);
		list($year_cur, $month_cur, $day1) = explode('-', $date_today);
		$month_start_cur = gmmktime (0,0,0, $month_cur, 1, $year_cur);

		/* Start timestamp for current month */
		$month_start = $month_start_cur;

		/* End timestamp for current month */
		$month_end = $now;

		return [(int) $month_start, (int) $month_end];
	}

	/**
	 * Returns whether to include Founders in the query
	 *
	 * @return string	SQL statement, empty string otherwise
	 */
	public function wishes_founder()
	{
		$tpotm_founder = (bool) $this->config['threedi_tpotm_founders'];

		return ($tpotm_founder) ? '' : 'AND (u.user_type <> ' . USER_FOUNDER . ') ';
	}

	/**
	 * Don't run the code if the admin so wishes.
	 * Returns an array of users with admin/mod auths (thx Steve for the idea)
	 *
	 * @return array	empty array otherwise
	 */
	public function auth_admin_mody_ary()
	{
		if ($this->config['threedi_tpotm_adm_mods'])
		{
			return [];
		}
		else
		{
			/**
			 * Inspiration taken from Top Five ext
			 * Grabs all admins and mods, it is a catch all.
			 */
			$admin_ary = $this->auth->acl_get_list(false, 'a_', false);
			$admin_ary = (!empty($admin_ary[0]['a_'])) ? $admin_ary[0]['a_'] : [];

			$mod_ary = $this->auth->acl_get_list(false, 'm_', false);
			$mod_ary = (!empty($mod_ary[0]['m_'])) ? $mod_ary[0]['m_'] : [];

			/* Groups the above results */
			return array_unique(array_merge($admin_ary, $mod_ary));
		}
	}

	/**
	 * Returns whether to include Admin and mods in the query
	 *
	 * @return string	SQL statement, empty string otherwise
	 */
	public function wishes_admin_mods()
	{
		$tpotm_admin_mods = (bool) $this->config['threedi_tpotm_adm_mods'];

		return ($tpotm_admin_mods) ? '' : 'AND ' . $this->db->sql_in_set('u.user_id', $this->auth_admin_mody_ary(), true, true) . ' ';
	}

	/**
	 * Gets the complete list of banned users.
	 *
	 * @return array	Array of banned users' ids if any, empty array otherwise
	 */
	public function banned_users_ids()
	{
		$ban_ids = [];

		/* No Email bans or IP ones */
		$sql = 'SELECT ban_userid
			FROM ' . BANLIST_TABLE . '
			WHERE ban_userid IS NOT NULL';
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$ban_ids[] = (int) $row['ban_userid'];
		}
		$this->db->sql_freeresult($result);

		/* SQL errors for empty arrays will be skipped
		 * by the fourth parm as true within "sql_in_set"
		 */
		return $ban_ids;
	}

	/**
	 * Returns whether to include also banned users in the query
	 *
	 * @return string	SQL statement, empty string otherwise
	 */
	public function wishes_banneds()
	{
		$tpotm_bans = (bool) $this->config['threedi_tpotm_banneds'];

		return ($tpotm_bans) ? '' : 'AND ' . $this->db->sql_in_set('u.user_id', $this->banned_users_ids(), true, true) . ' ';
	}

	/**
	 * Returns the SQL main SELECT statement used in various places.
	 *
	 * @param var	$and_admmods	the DBal AND statement to use
	 * @param var	$and_bans		the DBal AND statement to use
	 * @param var	$and_founder	the DBal AND statement to use
	 * @param int	$tpotm_start	UNIX timestamp of a starting point
	 * @param int	$tpotm__end		UNIX timestamp of an ending point
	 * @return	string	DBal SELECT statement
	 */
	public function tpotm_sql($and_admmods, $and_bans, $and_founder, $tpotm_start, $tpotm__end)
	{
		$sql = 'SELECT u.username, u.user_id, u.user_colour, u.user_avatar, u.user_avatar_type, u.user_avatar_width, u.user_avatar_height, user_tpotm, MAX(u.user_type), p.poster_id, MAX(p.post_time), COUNT(p.post_id) AS total_posts
				FROM ' . USERS_TABLE . ' u, ' . POSTS_TABLE . ' p
				WHERE u.user_id <> ' . ANONYMOUS . '
					AND u.user_id = p.poster_id
					' . $and_admmods . '
					' . $and_bans . '
					' . $and_founder . '
					AND p.post_visibility = ' . ITEM_APPROVED . '
					AND p.post_time BETWEEN ' . (int) $tpotm_start . ' AND ' . (int) $tpotm__end . '
				GROUP BY u.user_id
				ORDER BY total_posts DESC, MAX(p.post_time) DESC';

		return $sql;
	}

	/**
	 * Gets the total posts count for the current month till now
	 *
	 * @return int	$total_month
	 */
	protected function perform_cache_on_this_month_total_posts()
	{
		list($month_start, $month_end) = $this->month_timegap();

		/**
		 * Admin wants the cache to be cleared asap
		 * Show changes immediately after.
		 */
		if ((int) $this->config_time_cache_min() < 1)
		{
			$this->cache->destroy('_tpotm_total');
		}

		/**
		 * Check cached data
		 * Run the whole stuff only when needed
		 */
		if (($total_month = $this->cache->get('_tpotm_total')) === false)
		{
			$sql = 'SELECT COUNT(post_id) AS post_count
				FROM ' . POSTS_TABLE . '
				WHERE post_time BETWEEN ' . (int) $month_start . ' AND ' . (int) $month_end . '
					AND post_visibility = ' . ITEM_APPROVED;
			$result = $this->db->sql_query($sql);
			$total_month = (int) $this->db->sql_fetchfield('post_count');
			$this->db->sql_freeresult($result);

			$this->cache->put('_tpotm_total', (int) $total_month, (int) $this->config_time_cache());
		}
		return (int) $total_month;
	}

	/**
	 * There can be only ONE, the TPOTM.
	 * If same tot posts and same exact post time then the post ID rules
	 * Empty arrays SQL errors eated by setting the fourth parm as true within "sql_in_set"
	 * Performs a cache's check-in prior to delivery the final results
	 *
	 * @return array $row		cached or not results
	*/
	protected function perform_cache_on_main_db_query()
	{
		list($month_start, $month_end) = $this->month_timegap();

		/**
		 * Admin wants the cache to be cleared asap
		 */
		if ((int) $this->config_time_cache_min() < 1)
		{
			$this->cache->destroy('_tpotm');
		}

		/**
		 * Run the whole stuff only when needed
		 */
		if (($row = $this->cache->get('_tpotm')) === false)
		{
			/* If the Admin so wishes */
			$and_admmods = $this->wishes_admin_mods();
			$and_bans = $this->wishes_banneds();
			$and_founder = $this->wishes_founder();

			/* The main thang */
			$sql = $this->tpotm_sql($and_admmods, $and_bans, $and_founder, (int) $month_start, (int) $month_end);
			$result = $this->db->sql_query_limit($sql, 1);
			$row = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);

			/* There is a TPOTM, let's update the DB then */
			if (((int) $row['total_posts'] >= 1) && empty($row['user_tpotm']))
			{
				$this->perform_user_reset((int) $row['user_id']);
			}

			$this->cache->put('_tpotm', $row, (int) $this->config_time_cache());
		}

		return $row;
	}

	/*
	 * Gets the total TPOTM posts count for the current month till now
	 *
	 * @param int		$user_id			the current TPOTM user_id
	 * @return int		$tpotm_tot_posts	cached or not tpotm_tot_posts results
	*/
	protected function perform_cache_on_tpotm_tot_posts($user_id)
	{
		list($month_start, $month_end) = $this->month_timegap();

		/**
		 * Admin wants the cache to be cleared asap
		 */
		if ((int) $this->config_time_cache_min() < 1)
		{
			$this->cache->destroy('_tpotm_tot_posts');
		}

		/**
		 * Check cached data
		 * Run the whole stuff only when needed
		 */
		if (($tpotm_tot_posts = $this->cache->get('_tpotm_tot_posts')) === false)
		{
			$sql = 'SELECT COUNT(post_id) AS total_posts
				FROM ' . POSTS_TABLE . '
				WHERE post_time BETWEEN ' . (int) $month_start . ' AND ' . (int) $month_end . '
					AND poster_id = ' . (int) $user_id;
			$result = $this->db->sql_query($sql);
			$tpotm_tot_posts = (int) $this->db->sql_fetchfield('total_posts');
			$this->db->sql_freeresult($result);

			$this->cache->put('_tpotm_tot_posts', (int) $tpotm_tot_posts, (int) $this->config_time_cache());
		}

		return (int) $tpotm_tot_posts;
	}

	/*
	 * There can be only ONE... show the TPOTM.
	 *
	 * @return void
	 */
	public function show_the_winner()
	{
		/**
		 * Data Syncronization
		 */
		$row = $this->perform_cache_on_main_db_query();
		$tpotm_tot_posts = $this->perform_cache_on_tpotm_tot_posts((int) $row['user_id']);
		$total_month = $this->perform_cache_on_this_month_total_posts();

		/* Only authed can view the profile */
		$tpotm_un_string = ($this->auth->acl_get('u_viewprofile')) ? get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']) : get_username_string('no_profile', $row['user_id'], $row['username'], $row['user_colour']);

		/**
		 * Fresh install (one starting post by founder)
		 * or if a new Month has began results in zero posts
		 */
		$tpotm_un_nobody = $this->user->lang['TPOTM_NOBODY'];
		$tpotm_post = ((int) $tpotm_tot_posts >= 1) ? $this->user->lang('TPOTM_POST', (int) $tpotm_tot_posts) : false;
		$tpotm_cache = $this->user->lang('TPOTM_CACHE', (int) $this->config_time_cache_min());
		$tpotm_name = ((int) $tpotm_tot_posts < 1) ? $tpotm_un_nobody : $tpotm_un_string;

		/* Date range (tooltip) UCP */
		if ($this->user->data['user_tt_tpotm'] && $this->user->data['user_tt_sel_tpotm'])
		{
			/* User prefs hard-coded since it is a fake any way */
			$time = $this->user->lang('TPOTM_EXPLAIN', $this->user->format_date($this->get_month_data(00, 00, 00, true, false), $this->config['threedi_tpotm_utc']) . ' 00:01', $this->user->format_date($this->get_month_data(23, 59, 59, false, false), $this->config['threedi_tpotm_utc'])) . ' 00:00';
		}
		else
		{
			/* Classic data range based on UCP prefs native */
			$time = $this->user->lang('TPOTM_EXPLAIN', $this->get_month_data(00, 00, 00, true, true), $this->get_month_data(23, 59, 59, false, true));
		}

		$template_vars = [
			'TPOTM_NAME'			=> $tpotm_name,
			'L_TPOTM_POST'			=> $tpotm_post,
			'L_TPOTM_CACHE'			=> $tpotm_cache,
			'L_TOTAL_MONTH'			=> ((int) $total_month >= 1) ? $this->user->lang('TOTAL_MONTH', (int) $total_month, round(((int) $tpotm_tot_posts / (int) $total_month) * 100)) : false,
			'L_TPOTM_EXPLAIN'		=> $time,
			'S_TPOTM_AVAILABLE'		=> ((int) $tpotm_tot_posts < 1) ? false : true,
		];

		/* Prevents a potential Division by Zero below */
		$tpotm_tot_posts = ($tpotm_tot_posts === 0) ? true : (int) $tpotm_tot_posts;
		/**
		 * Percentages for Hall of Fame's styling etc..
		 * It could happen an user posted more than the total posts in the month.
		 * Ask Quick-Install o_0
		 */
		$percent = ((int) $tpotm_tot_posts > (int) $total_month) ? 0 : min(100, ((int) $tpotm_tot_posts) / (int) $total_month) * 100;
		$degrees = (360 * $percent) / 100;
		$start = 90;

		$template_vars += [
			'PERCENT'	=> number_format((float) $percent, 2, '.', ','),
			'DEGREE'	=> $percent > 50 ? $degrees - $start : $degrees + $start,
		];

		/**
		 * Don't run this code if there is not a TPOTM yet
		 */
		if ((int) $tpotm_tot_posts >= 1)
		{
			/* Map arguments for  phpbb_get_avatar() */
			$row_avatar = [
				'avatar'		 => $row['user_avatar'],
				'avatar_type'	 => $row['user_avatar_type'],
				'avatar_height'	 => $row['user_avatar_height'],
				'avatar_width'	 => $row['user_avatar_width'],
			];

			/**
			 * DAE (Default Avatar Extended) extension compatibility
			 * Here we do not care about the UCP prefs -> view avatars
			 */
			if ($this->is_dae())
			{
				$tpotm_av_3132_hall = phpbb_get_avatar($row_avatar, '');
			}
			else
			{
				/**
				 * Hall of fame's "default avatar" must be TPOTM's badge IMG for both versions
				 */
				$tpotm_av_3132_hall = (!empty($row['user_avatar'])) ? phpbb_get_avatar($row_avatar, '') : $this->check_point_badge_img();
			}

			$template_vars += ['TPOTM_AVATAR_HALL'	=> $tpotm_av_3132_hall,];

			/**
			 * Here we do care about the UCP prefs -> view avatars
			 * Code runs if the admin so wishes.
			 */
			if ($this->enable_miniavatar())
			{
				$tpotm_av_url = ($this->auth->acl_get('u_viewprofile')) ? get_username_string('profile', $row['user_id'], $row['username'], $row['user_colour']) : '';

				/* DAE (Default Avatar Extended) extension compatibility */
				if ($this->is_dae())
				{
					$tpotm_av_3132 = ($this->user->optionget('viewavatars')) ? phpbb_get_avatar($row_avatar, '') : '';
				}
				else
				{
					$tpotm_av_3132 = (!empty($row['user_avatar'])) ? (($this->user->optionget('viewavatars') ? phpbb_get_avatar($row_avatar, '') : '')) : $this->check_point_badge_img();
				}

				$template_vars += [
					'U_TPOTM_AVATAR_URL'	=> $tpotm_av_url,
					'TPOTM_AVATAR'			=> $tpotm_av_3132,
				];
			}
		}
		/* You know.. template stuff */
		$this->template->assign_vars($template_vars);
	}
}
