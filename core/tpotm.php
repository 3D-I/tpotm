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

/**
 * Top Poster Of The Month service.
 */
class tpotm
{
	protected $auth;
	protected $cache;
	protected $config;
	protected $db;
	protected $user;
	protected $path_helper;
	protected $root_path;
	protected $php_ext;
	protected $template;

	/**
		* Constructor
		*
		* @param \phpbb\auth\auth					$auth			Authentication object
		* @param \phpbb\cache\service				$cache
		* @param \phpbb\config\config				$config			Config Object
		* @param \phpbb\db\driver\driver_interface	$db				Database object
		* @param \phpbb\user						$user			User object
		* @param \phpbb\path_helper					$path_helper	Path helper object
		* @var string phpBB root path				$root_path
		* @var string phpEx							$phpExt
		* @param \phpbb\template\template			$template		Template object
		* @access public
	*/

	public function __construct(\phpbb\auth\auth $auth, \phpbb\cache\service $cache, \phpbb\config\config $config, \phpbb\db\driver\driver_interface $db, \phpbb\user $user, \phpbb\path_helper $path_helper, $root_path, $phpExt, \phpbb\template\template $template)
	{
		$this->auth				=	$auth;
		$this->cache			=	$cache;
		$this->config			=	$config;
		$this->db				=	$db;
		$this->user				=	$user;
		$this->path_helper		=	$path_helper;
		$this->root_path		=	$root_path;
		$this->php_ext			=	$phpExt;
		$this->template			=	$template;
	}

	/**
	 * Returns the absolute URL to the ext_path_web
	 *
	 * @return string
	 */
	public function ext_path_web()
	{
		return $this->path_helper->get_web_root_path() . 'ext/threedi/tpotm/';
	}

	/**
	 * Returns the time for cache adjustable in ACP
	 *
	 * @return int
	 */
	public function config_time_cache()
	{
		return (int) ($this->config['threedi_tpotm_ttl'] * 60);
	}

	/**
	 * Returns the number of minutes to show for templating purposes
	 *
	 * @return int
	 */
	public function config_time_cache_min()
	{
		return (int) ($this->config['threedi_tpotm_ttl']);
	}

	/**
	 * Returns whether the user is authed
	 *
	 * @return bool
	 */
	public function is_authed()
	{
		return (bool) (($this->auth->acl_get('u_allow_tpotm_view') || $this->auth->acl_get('a_tpotm_admin')));
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
	 * Returns whether the basic badge img exists
	 *
	 * @return	bool
	 */
	public function style_badge_is_true()
	{
		return file_exists($this->ext_path_web() . 'styles/' . rawurlencode($this->user->style['style_path']) . '/theme/images/tpotm_badge.png');
	}

	/**
	 * Badge IMG check-point
	 *
	 * @return void
	 */
	public function check_point_badge_img()
	{
		/* If Img badge filename mistmach error, state is false and return */
		if (!$this->style_badge_is_true())
		{
			$this->config->set('threedi_tpotm_badge_exists', 0);
			return;
		}
		else
		{
			/* Check passed, let's set it back to true. */
			$this->config->set('threedi_tpotm_badge_exists', 1);
		}
	}

	/**
	 * Returns the style related URL and HTML to the miniprofile badge image file
	 *
	 * @param string	$user_tpotm		the miniprofile image filename with extension
	 * @return string					URL
	 */
	public function style_miniprofile_badge($user_tpotm)
	{
		return $this->ext_path_web() . 'styles/' . rawurlencode($this->user->style['style_path']) . '/theme/images/' . $user_tpotm;
	}

	/**
	 * Returns the style related URL and HTML to the miniavatar image file
	 *
	 * @return string	Formatted URL
	 */
	public function style_mini_badge()
	{
		return '<img src="' . $this->ext_path_web() . 'styles/' . rawurlencode($this->user->style['style_path']) . '/theme/images/tpotm_badge.png" />';
	}

	/**
	 * Don't run the code if the admin so wishes.
	 * Returns an array of users with admin/mod auths (thx Steve for the idea)
	 *
	 * @return array	empty array otherwise
	 */
	public function auth_admin_mody_ary()
	{
		if ((bool) $this->config['threedi_tpotm_adm_mods'])
		{
			return array();
		}
		else
		{
			/**
			 * Inspiration taken from Top Five ext
			 * Grabs all admins and mods, it is a catch all.
			 */
			$admin_ary = $this->auth->acl_get_list(false, 'a_', false);
			$admin_ary = (!empty($admin_ary[0]['a_'])) ? $admin_ary[0]['a_'] : array();
			$mod_ary = $this->auth->acl_get_list(false, 'm_', false);
			$mod_ary = (!empty($mod_ary[0]['m_'])) ? $mod_ary[0]['m_'] : array();

			/* Groups the above results */
			return array_unique(array_merge($admin_ary, $mod_ary));
		}
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
	 * Update the user_tpotm to be empty for everyone
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
	 * Update the user_tpotm with the badge filename for the present winner
	 *
	 * @param int	$tpotm_user_id	the current TPOTM user_id
	 * @return void
	 */
	public function perform_user_db_update($tpotm_user_id)
	{
		$tpotm_sql2 = array(
			'user_tpotm'		=> 'tpotm_badge.png'
		);
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
	public function perform_user_reset($tpotm_user_id)
	{
		$this->perform_user_db_clean();
		$this->perform_user_db_update($tpotm_user_id);
	}

	/**
	 * Template switches over all
	 *
	 * @return void
	 */
	public function template_switches_over_all()
	{
		$this->template->assign_vars(array(
			'S_TPOTM'				=> ($this->auth->acl_get('u_allow_tpotm_view') || $this->auth->acl_get('a_tpotm_admin')) ? true : false,
			'S_IS_RHEA'				=> $this->is_rhea(),
			'S_TPOTM_INDEX_BOTTOM'	=> ($this->config['threedi_tpotm_index']) ? true : false,
			'S_TPOTM_INDEX_TOP'		=> ($this->config['threedi_tpotm_index']) ? false : true,
			'S_TPOTM_INDEX_FORUMS'	=> ($this->config['threedi_tpotm_forums']) ? true : false,
			'S_TPOTM_AVATAR'		=> ($this->config['threedi_tpotm_miniavatar']) ? true : false,
			'S_TPOTM_MINIPROFILE'	=> ($this->config['threedi_tpotm_miniprofile']) ? true : false,
			'S_TPOTM_HALL'			=> ($this->config['threedi_tpotm_hall']) ? true : false,
			'S_IS_BADGE_IMG'		=> $this->style_badge_is_true(),
			'S_IS_DAE'				=> $this->is_dae(),
		));
	}

	/**
	 * Returns whether DAE (Default Avatar Extended) extension it's installed and TRUE
	 *
	 * @return bool
	 */
	public function is_dae()
	{
		return (isset($this->config['threedi_default_avatar_version']) && phpbb_version_compare($this->config['threedi_default_avatar_version'], '1.0.0-rc2', '>=') && $this->config['threedi_default_avatar_extended'] && $this->config['threedi_default_avatar_exists']);
	}

	/**
	 * Gets the Unix Timestamp values for the current month.
	 *
	 * @return array	($month_start, $month_end) Unix Timestamp
	 */
	public function month_timegap()
	{
			$now = time();
			$date_today = gmdate("Y-m-d", $now);
			list($year_cur, $month_cur, $day1) = explode('-', $date_today);

			$month_start_cur = gmmktime (0,0,0, $month_cur, 1, $year_cur);
			/* Start timestamp for current month */
			$month_start = $month_start_cur;
			/* End timestamp for current month */
			$month_end = $now;

			return array((int) $month_start, (int) $month_end);
	}

	/**
	 * Gets/store the total posts count for the current month till now
	 *
	 * @return void|int	$total_month
	 */
	public function perform_cache_on_this_month_total_posts()
	{
		/* Prevents a potential Division by Zero */
		if ($this->config['threedi_tpotm_month_total_posts'] === 0)
		{
			$this->config->set('threedi_tpotm_month_total_posts', 1);
		}

		/**
		 * If we are disabling the cache the existing data
		 * in the cache file are not of use. Let's delete.
		 */
		if ($this->config_time_cache_min() === 0)
		{
			$this->cache->destroy('_tpotm_total');
		}

		/**
		 * Check cached data (cache it is used to keep things in syncro)
		 * Run the whole stuff only when needed or cache is disabled in ACP
		 */
		if ($total_month = $this->cache->get('_tpotm_total') === false)
		{
			list($month_start, $month_end) = $this->month_timegap();

			$sql = 'SELECT COUNT(post_id) AS post_count
				FROM ' . POSTS_TABLE . '
				WHERE post_time BETWEEN ' . (int) $month_start . ' AND ' . (int) $month_end . '
					AND post_visibility = ' . ITEM_APPROVED;
			$result = $this->db->sql_query($sql);
			$total_month = (int) $this->db->sql_fetchfield('post_count');
			$this->db->sql_freeresult($result);

			/* Using a config for multiple uses and to avoid to use list() as well */
			$this->config->set('threedi_tpotm_month_total_posts', (int) $total_month);
		}

		/* If cache is enabled use it */
		if ((int) $this->config_time_cache() >= 1)
		{
			$this->cache->put('_tpotm_total', (int) $total_month, (int) $this->config_time_cache());
		}
	}

	/*
	* There can be only ONE, the TPOTM.
	* If same tot posts and same exact post time then the post ID rules
	* Empty arrays SQL errors eated by setting the fourth parm as true within "sql_in_set"
	* Performs a chache check-in prior to delivery the final results
	*
	 * @return array $row		cached or not results
	*/
	public function perform_cache_on_main_db_query()
	{
		/**
		 * If we are disabling the cache, the existing information
		 * in the cache file is not valid. Let's clear it.
		 */
		if (($this->config_time_cache_min()) === 0)
		{
			$this->cache->destroy('_tpotm');
		}
		/**
		 * Run the whole stuff only when needed or cache is disabled in ACP
		 */
		if ($row = $this->cache->get('_tpotm') === false)
		{
			list($month_start, $month_end) = $this->month_timegap();

			$sql = 'SELECT u.username, u.user_id, u.user_colour, u.user_avatar, u.user_avatar_type, u.user_avatar_width, u.user_avatar_height, user_tpotm, MAX(u.user_type), p.poster_id, MAX(p.post_time)
				FROM ' . USERS_TABLE . ' u, ' . POSTS_TABLE . ' p
				WHERE u.user_id <> ' . ANONYMOUS . '
					AND u.user_id = p.poster_id
					AND ' . $this->db->sql_in_set('u.user_id', $this->auth_admin_mody_ary(), true, true) . '
					AND ' . $this->db->sql_in_set('u.user_id', $this->banned_users_ids(), true, true) . '
					AND (u.user_type <> ' . USER_FOUNDER . ')
					AND p.post_visibility = ' . ITEM_APPROVED . '
					AND p.post_time BETWEEN ' . (int) $month_start . ' AND ' . (int) $month_end . '
				GROUP BY u.user_id, p.post_time, p.post_id
				ORDER BY p.post_time DESC, p.post_id DESC';
			$result = $this->db->sql_query_limit($sql, 1);
			$row = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);
		}
		/* If cache is enabled use it */
		if ((int) $this->config_time_cache() >= 1)
		{
			$this->cache->put('_tpotm', $row, (int) $this->config_time_cache());
		}
		return $row;
	}

	/*
	* tpotm_tot_posts
	*
	 * @param int	$user_id	the current TPOTM user_id
	 * @return int $tpotm_tot_posts		cached or not tpotm_tot_posts results
	*/
	public function perform_cache_on_tpotm_tot_posts($user_id)
	{
		/**
		 * If we are disabling the cache, the existing information
		 * in the cache file is not valid. Let's clear it.
		 */
		if ($this->config_time_cache_min() === 0)
		{
			$this->cache->destroy('_tpotm_tot_posts');
		}
		/**
		 * Check cached data
		 * Run the whole stuff only when needed or cache is disabled in ACP
		 */
		if ($tpotm_tot_posts = $this->cache->get('_tpotm_tot_posts') === false)
		{
			list($month_start, $month_end) = $this->month_timegap();

			$sql = 'SELECT COUNT(post_id) AS total_posts
				FROM ' . POSTS_TABLE . '
				WHERE post_time BETWEEN ' . (int) $month_start . ' AND ' . (int) $month_end . '
					AND poster_id = ' . (int) $user_id;
			$result = $this->db->sql_query($sql);
			$tpotm_tot_posts = (int) $this->db->sql_fetchfield('total_posts');
			$this->db->sql_freeresult($result);

			/* If no posts for the current elapsed time there is not a TPOTM */
			if ((int) $tpotm_tot_posts < 1)
			{
				$this->perform_user_db_clean();
			}

			/* There is a TPOTM, let's update the DB then */
			if ((int) $tpotm_tot_posts >= 1)
			{
				$this->perform_user_reset((int) $user_id);
			}
		}
		/* If cache is enabled use it */
		if ((int) $this->config_time_cache() >= 1)
		{
			$this->cache->put('_tpotm_tot_posts', (int) $tpotm_tot_posts, (int) $this->config_time_cache());
		}
		return (int) $tpotm_tot_posts;
	}

	/**
	  Performs a date range costruction of the current month
	 *
	 * @return string		user formatted data range (Thx Steve)
	 */
	public function get_month_data($hr, $min, $sec, $start = true, $format = false)
	{
		list($year, $month, $day) = explode('-', gmdate("y-m-d", time()));

		$data = gmmktime($hr, $min, $sec, $month, $start ? 1 : date("t"), $year);

		return $format ? $this->user->format_date((int) $data) : (int) $data;
	}

	/*
	* There can be only ONE, the TPOTM.
	* If same tot posts and same exact post time then the post ID rules
	* Empty arrays SQL errors eated by setting the fourth parm as true within "sql_in_set"
	*
	 * @return void
	*/
	public function show_the_winner()
	{
		/* Syncro */
		$this->perform_cache_on_this_month_total_posts();
		$row = $this->perform_cache_on_main_db_query();
		$tpotm_tot_posts = $this->perform_cache_on_tpotm_tot_posts((int) $row['user_id']);

		/* Only auth'd users can view the profile */
		$tpotm_un_string = ($this->auth->acl_get('u_viewprofile')) ? get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']) : get_username_string('no_profile', $row['user_id'], $row['username'], $row['user_colour']);

		/* Fresh install or if a new Month has began results to zero posts */
		$tpotm_un_nobody = $this->user->lang['TPOTM_NOBODY'];
		$tpotm_post = ((int) $tpotm_tot_posts >= 1) ? $this->user->lang('TPOTM_POST', (int) $tpotm_tot_posts) : false;
		$tpotm_cache = $this->user->lang('TPOTM_CACHE', (int) $this->config_time_cache_min());
		$tpotm_name = ((int) $tpotm_tot_posts) < 1 ? $tpotm_un_nobody : $tpotm_un_string;
		$total_month = (int) $this->config['threedi_tpotm_month_total_posts'];

		$template_vars = array(
			'TPOTM_NAME'		=> $tpotm_name,
			'L_TPOTM_POST'		=> $tpotm_post,
			'L_TPOTM_CACHE'		=> $tpotm_cache,
			'L_TOTAL_MONTH'		=> ((int) $total_month >= 1) ? $this->user->lang('TOTAL_MONTH', (int) $total_month, round(((int) $tpotm_tot_posts / (int) $total_month) * 100)) : false,
			'L_TPOTM_EXPLAIN'	=> $this->user->lang('TPOTM_EXPLAIN', $this->get_month_data(00, 00, 00, true, true), $this->get_month_data(23, 59, 59, false, true)),
		);

		/* Prevents a potential Division by Zero below */
		$tpotm_tot_posts = ($tpotm_tot_posts === 0) ? true : (int) $tpotm_tot_posts;
		/**
		 * Percentages for Hall of Fame's styling etc..
		 * It could happen an user posted more than the total posts in the month.
		 * Ask Quick-Install, LoL o_0
		 */
		$percent = ((int) $tpotm_tot_posts > (int) $total_month) ? 0 : min(100, ((int) $tpotm_tot_posts) / (int) $total_month) * 100;
		$degrees = (360 * $percent) / 100;
		$start = 90;

		$template_vars += array(
			'PERCENT'			=> number_format((float) $percent, 2, '.', ','),
			'DEGREE'			=> $percent > 50 ? $degrees - $start : $degrees + $start,
		);

		/**
		 * Don't run that code if the admin so wishes or there is not a TPOTM yet
		 */
		if ((int) $tpotm_tot_posts >= 1)
		{
			/* Map arguments for  phpbb_get_avatar() */
			$row_avatar = array(
				'avatar'		=> $row['user_avatar'],
				'avatar_width'	=> (int) $row['user_avatar_width'],
				'avatar_height'	=> (int) $row['user_avatar_height'],
				'avatar_type'	=> $row['user_avatar_type'],
			);

			/* DAE (Default Avatar Extended) extension compatibility */
			if ($this->is_dae())
			{
				$tpotm_av_3132_hall = ($this->user->optionget('viewavatars')) ? phpbb_get_avatar($row_avatar, '') : '';
			}
			else
			{
				/**
				 * Hall's avatar must be TPOTM's IMG for both versions
				 * The Hall of fame doesn't care about the UCP prefs view avatars
				 */
				$tpotm_av_3132_hall = (!empty($row['user_avatar'])) ? phpbb_get_avatar($row_avatar, '') : (($this->style_badge_is_true()) ? $this->style_mini_badge() : $this->user->lang('TPOTM_BADGE'));
			}

			$template_vars += array(
				'TPOTM_AVATAR_HALL'		=> $tpotm_av_3132_hall,
			);

			/**
			 * Avatar as IMG or FA-icon depends on the phpBB version
			 * Here we do care about the UCP prefs -> view avatars
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
					$tpotm_av_3132 = (!empty($row['user_avatar'])) ? (($this->user->optionget('viewavatars')) ? phpbb_get_avatar($row_avatar, '') : '') : (($this->style_badge_is_true()) ? $this->style_mini_badge() : $this->user->lang('TPOTM_BADGE'));
				}

				$template_vars += array(
					'U_TPOTM_AVATAR_URL'	=> $tpotm_av_url,
					'TPOTM_AVATAR'			=> $tpotm_av_3132,
				);
			}
		}
		/* You know.. template stuff */
		$this->template->assign_vars($template_vars);
	}
}
