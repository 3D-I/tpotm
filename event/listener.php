<?php
/**
*
* @package phpBB Extension - tpotm 1.0.2-(Top Poster Of The Month)
* @copyright (c) 2015 3Di (Marco T.)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
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
	//protected $auth; // not yet in use

	/** @var \phpbb\cache\service */
	protected $cache;

	/** @var \phpbb\config\config */
	//protected $config; // not yet in use

	/** @var \phpbb\template\template */
	protected $template;

	/* @var \phpbb\user */
	protected $user;

	/** @var \phpbb\db\driver\driver */
	protected $db;

	/**
		* Constructor
		*
		* @param \phpbb\auth\auth			$auth			Authentication object // not yet in use
		* @param \phpbb\cache\service		$cache
		* @param \phpbb\config\config		$config			Config Object // not yet in use
		* @param \phpbb\template\template	$template		Template object
		* @param \phpbb\user				$user			User Object
		* @param \phpbb\db\driver\driver	$db				Database object
		* @access public
		*/
	public function __construct(
			\phpbb\cache\service $cache, \phpbb\template\template $template, \phpbb\user $user, \phpbb\db\driver\driver_interface $db)
	{
		//$this->auth = $auth; // not yet in use
		$this->cache = $cache;
		//$this->config = $config; // not yet in use
		$this->template = $template;
		$this->user = $user;
		$this->db = $db;
	}

	static public function getSubscribedEvents()
	{
		return array(
			'core.user_setup'		=> 'load_language_on_setup',
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

	public function display_tpotm($event)
	{
		$now = time();
		$date_today = gmdate("Y-m-d", $now);
		list($year_cur, $month_cur, $day1) = split('-', $date_today);

		/* Start time for current month */
		$month_start_cur	= gmmktime (0,0,0, $month_cur, 1, $year_cur);
		$month_start		= $month_start_cur;
		$month_end			= $now;

		/*
			* group_id 5 = administrators
			* group_id 4 = global moderators
			* per default into a Vanilla 3.1.x board
		*/
		$group_ids = array(5, 4);

		/*
			* config time for cache, still to be fully implemented thus hardcoded
			* 900 = 15 minutes
		*/
		$config_time_cache = 900;

		/* Check cached data */
		if (($row = $this->cache->get('_tpotm')) === false)
		{
			$sql = 'SELECT u.username, u.user_id, u.user_colour, u.user_type, u.group_id, p.poster_id, p.post_time, COUNT(p.post_id) AS total_posts
				FROM ' . USERS_TABLE . ' u, ' . POSTS_TABLE . ' p
				WHERE u.user_id > ' . ANONYMOUS . '
					AND u.user_id = p.poster_id
						AND (u.user_type <> ' . USER_FOUNDER . ')
							AND ' . $this->db->sql_in_set('u.group_id', $group_ids, true) . '
								AND p.post_time BETWEEN ' . $month_start . ' AND ' . $month_end . '
				GROUP BY u.user_id
				ORDER BY total_posts DESC';

			$result = $this->db->sql_query_limit($sql, 1);
			$row = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);

			/* caching this data improves performance */
			$this->cache->put('_tpotm', $row, (int) $config_time_cache);
		}

		/* Let's show the Top Poster then */
		$tpotm_tot_posts = (int) $row['total_posts'];

		$tpotm_un_string = get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']);
		/* Fresh installs or new Month starts give zero posts */
		$tpotm_un_nobody = $this->user->lang['TPOTM_NOBODY'];

		$tpotm_post = $this->user->lang('TPOTM_POST', (int) $tpotm_tot_posts);

		$tpotm_name = ($tpotm_tot_posts < 1) ? $tpotm_un_nobody : $tpotm_un_string;

		/* you know.. template stuffs */
		$this->template->assign_vars(array(
			'TPOTM_NAME'		=> $tpotm_name,
			'L_TPOTM_CAT'		=> $this->user->lang['TPOTM_CAT'],
			'L_TPOTM_NOW'		=> $this->user->lang['TPOTM_NOW'],
			'L_TPOTM_POST'		=> $tpotm_post,
		));
	}
}
