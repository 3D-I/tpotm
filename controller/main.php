<?php
/**
 *
 * Top Poster Of The Month. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2005, 2019, 3Di <https://www.phpbbstudio.com>
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
	 * @param \phpbb\auth\auth						$auth
	 * @param \phpbb\db\driver\driver_interface		$db
	 * @param \phpbb\config\config					$config
	 * @param \phpbb\controller\helper				$helper
	 * @param \phpbb\pagination						$pagination
	 * @param \phpbb\request\request				$request
	 * @param \phpbb\template\template				$template
	 * @param \phpbb\user							$user
	 * @param \threedi\tpotm\core\tpotm				$tpotm
	 * @param										$php_ext
	 * @param										$root_path
	 * @return void
	 * @access public
	 */
	public function __construct(
		\phpbb\auth\auth $auth,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\config\config $config,
		\phpbb\controller\helper $helper,
		\phpbb\pagination $pagination,
		\phpbb\request\request $request,
		\phpbb\template\template $template,
		\phpbb\user $user,
		\threedi\tpotm\core\tpotm $tpotm,
		$php_ext,
		$root_path
	)
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

		$this->php_ext		= $php_ext;
		$this->root_path	= $root_path;
	}

	/**
	 * Controller for route /tpotm/hall_of_fame
	 *
	 * @return \Symfony\Component\HttpFoundation\Response A Symfony Response object
	 */
	public function handle()
	{
		if (!$this->tpotm->is_authed())
		{
			throw new \phpbb\exception\http_exception(403, 'NOT_AUTHORISED_TPOTM_HALL');
		}

		if (!$this->tpotm->is_hall())
		{
			throw new \phpbb\exception\http_exception(404, 'TPOTM_HALL_DISABLED');
		}

		/* Starting point in time */
		if (!$this->config['threedi_tpotm_since_epoch'])
		{
			$board_start = (int) $this->config['board_startdate'];
		}
		else
		{
			$board_start = (int) '0'; // Epoch time 1970-01-01 00:00
		}

		/**
		 * if the current date is 01 (January) then date() will decrement the year
		 *  by one and wrap the month back round to 12
		 */
		$now = time();
		$date_today = gmdate("Y-m", $now);
		list($year_cur, $month_cur) = explode('-', $date_today);
		$month = (int) $month_cur -1;
		$year = (int) $year_cur;

		/* Top posters_ever (minus the present month) UTC - Thx Steve */
		$max_days =  date( 't', gmmktime(23, 59, 59, $month, 1, $year) );
		$end_last_month = gmmktime(23, 59, 59, $month, $max_days, $year);

		/* Top posters ever's dynamic cache TTL */
		$this_max_days =  date( 't', gmmktime(23, 59, 59, $month_cur, 1, $year) );
		$end_this_month = gmmktime(23, 59, 59, $month_cur, $this_max_days, $year);

		/* Top posters ever's dynamic cache TTL admin choice*/
		$ttl_diff = ($this->config['threedi_tpotm_ttl_mode']) ? $ttl_diff = (int) $end_this_month - $now : (int) $this->config['threedi_tpotm_ttl_tpe'];

		/* These are for pagination */
		$start = $this->request->variable('start', 0);
		$limit = (int) $this->config['threedi_tpotm_users_page'];

		/* Admin choices */
		$and_admmods = $this->tpotm->wishes_admin_mods();
		$and_bans = $this->tpotm->wishes_banneds();
		$and_founder = $this->tpotm->wishes_founder();

		/*
		 * Top posters ever
		 * Show the top posters ever sorted by total posts DESC
		 * If same tot posts and same exact post time then the post ID rules
		 * SQL errors for empty arrays skipped by setting the fourth parm as true within "sql_in_set"
		*/
		$sql = $this->tpotm->tpotm_sql($and_admmods, $and_bans, $and_founder, (int) $board_start, (int) $end_last_month);

		/* Rowset array for the viewport */
		$result = $this->db->sql_query_limit($sql, $limit , $start, (int) $ttl_diff);
		$rows = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		/* Total users count for pagination */
		$result2 = $this->db->sql_query($sql, (int) $ttl_diff);
		$row2 = $this->db->sql_fetchrowset($result2);
		$total_users = (int) count($row2);
		$this->db->sql_freeresult($result2);

		/* No need of this any more */
		unset($row2);

		/**
		 * Gives the user an avatar as default if missing, for the sake of the layout.
		 * If the TPOTM img has been manipulated returns no avatar at all and notice.
		 */
		$no_avatar = (empty($row['user_avatar'])) ? $this->tpotm->check_point_badge_img() : $this->user->lang('TPOTM_BADGE');

		/* Loop into the data */
		foreach ($rows as $row)
		{
			/* Map arguments for phpbb_get_avatar() */
			$row_avatar_hall = [
				'avatar'		=> $row['user_avatar'],
				'avatar_type'	=> $row['user_avatar_type'],
				'avatar_height'	=> $row['user_avatar_height'],
				'avatar_width'	=> $row['user_avatar_width'],
			];

			/* Giv'em an username, if any */
			$username = ($this->auth->acl_get('u_viewprofile'))
				? get_username_string('full', $row['user_id'], $row['username'], $row['user_colour'])
				: get_username_string('no_profile', $row['user_id'], $row['username'], $row['user_colour']);

			/**
			 * DAE (Default Avatar Extended) extension compatibility
			 * Here we do not care about the UCP prefs -> view avatars
			 */
			if ($this->tpotm->is_dae())
			{
				$user_avatar = phpbb_get_avatar($row_avatar_hall, '');
			}
			else
			{
				$user_avatar = (!empty($row['user_avatar'])) ? phpbb_get_avatar($row_avatar_hall, '') : $no_avatar;
			}

			$this->template->assign_block_vars('tpotm_ever', [
				'USER_AVATAR'	=> $user_avatar,
				'USERNAME'		=> $username,
				'TOTAL_POSTS'	=> (int) $row['total_posts'],
				'POST_TIME'		=> $this->user->format_date((int) $row['MAX(p.post_time)'])
			]);
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

		$template_vars = [
			'L_TPOTM_EXPLAIN_HALL'	=> $this->user->lang('TPOTM_EXPLAIN', $data_begin, $data_end),
			'COUNT'					=> $this->user->lang('TPOTM_HALL_COUNT', (int) $total_users),
		];

		$this->template->assign_vars($template_vars);

		$url = $this->helper->route('threedi_tpotm_controller');

		$this->pagination->generate_template_pagination($url, 'pagination', 'start', $total_users, $limit, $start);

		$name = $this->user->lang('HALL_OF_FAME', $this->pagination->get_on_page($limit, $start));

		make_jumpbox(append_sid("{$this->root_path}viewforum.{$this->php_ext}"));

		return $this->helper->render('tpotm_hall.html', $name);
	}
}
