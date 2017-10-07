<?php
/**
 *
 * Top Poster Of The Month. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2005,2017, 3Di
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace threedi\tpotm\controller;

/**
 * Top Poster Of The Month main controller.
 */
class main
{
	/* @var \phpbb\auth\auth */
	protected $auth;

	/* @var \phpbb\db\driver\driver_interface */
	protected $db;

	/* @var \phpbb\config\config */
	protected $config;

	/* @var \phpbb\controller\helper */
	protected $helper;

	/** @var \phpbb\pagination */
	protected $pagination;

	/* @var \phpbb\request\request */
	protected $request;

	/* @var \phpbb\template\template */
	protected $template;

	/* @var \phpbb\user */
	protected $user;

	/* @var \threedi\tpotm\core\tpotm */
	protected $tpotm;

	/* @var string phpEx */
	protected $php_ext;

	/* @var string phpBB root path */
	protected $root_path;

	/**
	 * Constructor
	 */
	public function __construct(\phpbb\auth\auth $auth, \phpbb\db\driver\driver_interface $db, \phpbb\config\config $config, \phpbb\controller\helper $helper, \phpbb\pagination $pagination, \phpbb\request\request $request, \phpbb\template\template $template, \phpbb\user $user, \threedi\tpotm\core\tpotm $tpotm, $phpExt, $root_path)
	{
		$this->auth			= $auth;
		$this->db			= $db;
		$this->config		= $config;
		$this->helper		= $helper;
		$this->pagination 	= $pagination;
		$this->request 		= $request;
		$this->template		= $template;
		$this->user			= $user;
		$this->tpotm		= $tpotm;
		$this->php_ext		= $phpExt;
		$this->root_path	= $root_path;
	}

	/**
	 * Controller for route /tpotm/{name}
	 *
	 * @param string $name
	 *
	 * @return \Symfony\Component\HttpFoundation\Response A Symfony Response object
	 */
	public function handle($name)
	{
		if (!$this->tpotm->is_authed())
		{
			throw new \phpbb\exception\http_exception(403, 'NOT_AUTHORISED_TPOTM__HALL');
		}

		if (!$this->tpotm->is_hall())
		{
			throw new \phpbb\exception\http_exception(404, 'TPOTM__HALL_DISABLED');
		}

		/**
		 * Check permissions prior to run the code
		 */
		if ($this->tpotm->is_authed() && $this->tpotm->is_hall())
		{
			$message = $this->user->lang('TPOTM_HELLO');
			$this->template->assign_var('TPOTM_MESSAGE', $this->user->lang($message, $name));

			/* Starting point in time */
			//$board_start = (int) $this->config['board_startdate'];
			$board_start = (int) '0'; // Epoch time 1970-01-01 00:00

			/**
			 * if the current month is 01 (January) date() will decrement the year by one
			 * and wrap the month back round to 12
			 */
			$now = time();
			$date_today = gmdate("Y-m", $now);
			list($year_cur, $month_cur) = explode('-', $date_today);
			$month = (int) $month_cur -1;
			$year = (int) $year_cur;

			/* Top posters_ever (minus the present month - Thx Steve) */
			$max_days =  date('t', gmmktime(23, 59, 59, $month, 1, $year));
			$end_last_month = gmmktime(23, 59, 59, $month, $max_days, $year);

			/* These are for pagination */
			$total_users	= $this->request->variable('user_id', 0);
			$start			= $this->request->variable('start', 0);
			$limit			= (int) $this->config['threedi_tpotm_users_page'];

			/* If the Admin so wishes */
			$and_founder = $this->tpotm->wishes_founder();

			/*
			 * Top posters ever
			 * Show the top posters ever sorted by total posts DESC
			 * If same tot posts and same exact post time then the post ID rules
			 * SQL errors for empty arrays skipped by setting the fourth parm as true within "sql_in_set"
			*/
			$sql = 'SELECT u.username, u.user_id, u.user_colour, u.user_avatar, u.user_avatar_type, u.user_avatar_width, u.user_avatar_height, MAX(u.user_type), p.poster_id, MAX(p.post_time), COUNT(p.post_id) AS total_posts
				FROM ' . USERS_TABLE . ' u, ' . POSTS_TABLE . ' p
				WHERE u.user_id <> ' . ANONYMOUS . '
					AND u.user_id = p.poster_id
					AND ' . $this->db->sql_in_set('u.user_id', $this->tpotm->auth_admin_mody_ary(), true, true) . '
					AND ' . $this->db->sql_in_set('u.user_id', $this->tpotm->banned_users_ids(), true, true) . '
					' . $and_founder . '
					AND p.post_visibility = ' . ITEM_APPROVED . '
					AND p.post_time BETWEEN ' . (int) $board_start . ' AND ' . (int) $end_last_month . '
				GROUP BY u.user_id
				ORDER BY total_posts DESC, MAX(p.post_time) DESC';
			$result = $this->db->sql_query_limit($sql, $limit , $start, (int) $this->tpotm->config_time_cache());
			$rows = $this->db->sql_fetchrowset($result);
			$this->db->sql_freeresult($result);

			/* Total users count for pagination */
			$sql2 = 'SELECT u.user_id, MAX(u.user_type), p.poster_id, MAX(p.post_time), COUNT(p.post_id) AS total_posts
				FROM ' . USERS_TABLE . ' u, ' . POSTS_TABLE . ' p
				WHERE u.user_id <> ' . ANONYMOUS . '
					AND u.user_id = p.poster_id
					AND ' . $this->db->sql_in_set('u.user_id', $this->tpotm->auth_admin_mody_ary(), true, true) . '
					AND ' . $this->db->sql_in_set('u.user_id', $this->tpotm->banned_users_ids(), true, true) . '
					' . $and_founder . '
					AND p.post_visibility = ' . ITEM_APPROVED . '
					AND p.post_time BETWEEN ' . (int) $board_start . ' AND ' . (int) $end_last_month . '
				GROUP BY u.user_id
				ORDER BY total_posts DESC';
			$result2 = $this->db->sql_query($sql2, (int) $this->tpotm->config_time_cache());
			$row2 = $this->db->sql_fetchrowset($result2);
			$total_users = (int) count($row2);
			$this->db->sql_freeresult($result2);

			/* No need of this any more */
			unset($row2);

			/**
			 * Gives the user an avatar as default if missing, for the sake of the layout.
			 * If the TPOTM img has been manipulated returns no avatar at all and notice.
			 */
			$no_avatar =  (empty($row['user_avatar'])) ? $this->tpotm->style_mini_badge() : $this->user->lang('TPOTM_BADGE');

			foreach ($rows as $row)
			{
				/* Map arguments for phpbb_get_avatar() */
				$row_avatar_hall = array(
					'avatar'		 => $row['user_avatar'],
					'avatar_type'	 => $row['user_avatar_type'],
					'avatar_height'	 => $row['user_avatar_height'],
					'avatar_width'	 => $row['user_avatar_width'],
				);

				/* Giv'em an username, if any */
				$username = ($this->auth->acl_get('u_viewprofile')) ? get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']) : get_username_string('no_profile', $row['user_id'], $row['username'], $row['user_colour']);

				/* DAE (Default Avatar Extended) extension compatibility */
				if ($this->tpotm->is_dae())
				{
					/* We don't care here about the UCP prefs -> view avatars */
					$user_avatar = phpbb_get_avatar($row_avatar_hall, '');
				}
				else
				{
					/* We don't care here about the UCP prefs -> view avatars */
					$user_avatar = (!empty($row['user_avatar'])) ? phpbb_get_avatar($row_avatar_hall, '') : $no_avatar;
				}

				$this->template->assign_block_vars('tpotm_ever', array(
					'USER_AVATAR'	=> $user_avatar,
					'USERNAME'		=> $username,
					'TOTAL_POSTS'	=> (int) $row['total_posts'],
					'POST_TIME'		=> $this->user->format_date((int) $row['MAX(p.post_time)'])
				));
			}

			/* Date range (tooltip) */
			if ($this->user->data['user_tt_tpotm'] && $this->user->data['user_tt_sel_tpotm'])
			{
				/* User prefs hard-coded since it is a fake any way */
				$data_begin = $this->user->format_date((int) $board_start, $this->config['threedi_tpotm_utc'] . ' H:i');
				$data_end = $this->user->format_date((int) $end_last_month, $this->config['threedi_tpotm_utc']) . ' 00:00';
			}
			else
			{
				/* Classic data range based on UCP prefs native */
				$data_begin = $this->user->format_date((int) $board_start);
				$data_end = $this->user->format_date((int) $end_last_month);
			}

			$template_vars = array(
				'L_TPOTM_EXPLAIN_HALL'	=> $this->user->lang('TPOTM_EXPLAIN', $data_begin, $data_end),
				'COUNT'					=> $this->user->lang('TPOTM_HALL_COUNT', (int) $total_users),
			);

			$this->template->assign_vars($template_vars);

			$url = $this->helper->route('threedi_tpotm_controller', array('name' => $name));

			$this->pagination->generate_template_pagination($url, 'pagination', 'start', $total_users, $limit, $start);

			$name = $this->user->lang('HALL_OF_FAME', $this->pagination->get_on_page($limit, $start));

			make_jumpbox(append_sid("{$this->root_path}viewforum.{$this->php_ext}"));

			return $this->helper->render('tpotm_hall.html', $name);
		}
	}
}
