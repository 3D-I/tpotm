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

	/* @var \phpbb\extension\manager */
	protected $ext_manager;

	/* @var \phpbb\controller\helper */
	protected $helper;

	/* @var \phpbb\path_helper */
	protected $path_helper;

	/* @var \phpbb\template\template */
	protected $template;

	/* @var \phpbb\user */
	protected $user;

	/* @var \threedi\tpotm\core\tpotm */
	protected $tpotm;

	/* @var string phpBB root path */
	protected $root_path;

	/**
	 * Constructor
	 *
	 * @param \phpbb\auth\auth					$auth			Authentication object
	 * @param \phpbb\db\driver\driver_interface	$db				Database object
	 * @param \phpbb\config\config				$config
	 * @param \phpbb\extension\manager			$ext_manager
	 * @param \phpbb\controller\helper			$helper
	 * @param \phpbb\path_helper				$path_helper
	 * @param \phpbb\template\template			$template
	 * @param \phpbb\user						$user
	 * @param threedi\tpotm\core\tpotm			$tpotm			Methods to be used by Class
	 * @var string phpBB root path				$root_path
	 */
	public function __construct(\phpbb\auth\auth $auth, \phpbb\db\driver\driver_interface $db, \phpbb\config\config $config, \phpbb\extension\manager $ext_manager, \phpbb\controller\helper $helper, \phpbb\path_helper $path_helper, \phpbb\template\template $template, \phpbb\user $user, \threedi\tpotm\core\tpotm $tpotm, $root_path)
	{
		$this->auth			= $auth;
		$this->db			= $db;
		$this->config		= $config;
		$this->ext_manager	= $ext_manager;
		$this->helper		= $helper;
		$this->path_helper	= $path_helper;
		$this->template		= $template;
		$this->user			= $user;
		$this->tpotm		= $tpotm;
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

		$l_message = $this->user->lang('DEMO_HELLO');
		$this->template->assign_var(
			'DEMO_MESSAGE', $this->user->lang($l_message, $name)
			);

		/**
		 * Gives an avatar as default if missing.
		 * For the sake of the layout
		 */
		$no_avatar = '<img src="' . ($this->path_helper->get_web_root_path() . 'ext/threedi/tpotm/styles/' . rawurlencode($this->user->style['style_path']) . '/theme/images/tpotm_badge.png') . '" />';

		/*
		 * top_posters_ever
		 * If same tot posts and same exact post time then the post ID rules
		 * Empty arrays SQL errors eated by setting the fourth parm as true within "sql_in_set"
		 *
		 * @return void %m-%Y
		*/
		$year_start = (int) ($this->config['board_start_date']);
		$year_end = time();

		//	, DATE_FORMAT(FROM_UNIXTIME(p.post_time), "%Y") AS year, DATE_FORMAT(FROM_UNIXTIME(p.post_time), "%m") AS month

		$sql = 'SELECT u.username, u.user_id, u.user_colour, u.user_avatar, u.user_avatar_type, u.user_avatar_width, u.user_avatar_height, user_tpotm, MAX(u.user_type), p.poster_id, MAX(p.post_time), COUNT(p.post_id) AS total_posts
		FROM ' . USERS_TABLE . ' u, ' . POSTS_TABLE . ' p
		WHERE u.user_id <> ' . ANONYMOUS . '
			AND u.user_id = p.poster_id
			AND ' . $this->db->sql_in_set('u.user_id', $this->tpotm->auth_admin_mody_ary(), true, true) . '
			AND ' . $this->db->sql_in_set('u.user_id', $this->tpotm->banned_users_ids(), true, true) . '
			AND (u.user_type <> ' . USER_FOUNDER . ')
			AND p.post_visibility = ' . ITEM_APPROVED . '
			AND p.post_time BETWEEN ' . $year_start . ' AND ' . $year_end . '
		GROUP BY u.user_id
		ORDER BY total_posts DESC';
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			/* Map arguments for  phpbb_get_avatar() */
			$row_avatar = array(
				'avatar'		=> $row['user_avatar'],
				'avatar_type'	=> $row['user_avatar_type'],
				'avatar_width'	=> (int) $row['user_avatar_width'],
				'avatar_height'	=> (int) $row['user_avatar_height'],
			);

			//'USER_ID'		=> (int) $row['user_id'],
			//	'YEAR'			=> (int) $row['year'],
			//	'MONTH'			=> (int) $row['month'],
			$this->template->assign_block_vars('tpotm_ever', array(
				'USER_AVATAR'	=> (!empty($row['user_avatar'])) ? phpbb_get_avatar($row_avatar, $alt = $this->user->lang('USER_AVATAR')) : $no_avatar,
				'USERNAME'		=> ($this->auth->acl_get('u_viewprofile')) ? get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']) : get_username_string('no_profile', $row['user_id'], $row['username'], $row['user_colour']),
				'TOTAL_POSTS'	=> (int) $row['total_posts'],
				));
			}

		$this->db->sql_freeresult($result);

		return $this->helper->render('tpotm_hall.html', $name);
	}
}
