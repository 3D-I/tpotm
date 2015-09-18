<?php
/**
*
* @package phpBB Extension - tpotm 1.0.1-RC2 (Top Poster Of The Month)
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
	protected $auth;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\template\template */
	protected $template;

	/* @var \phpbb\user */
	protected $user;

	/** @var \phpbb\db\driver\driver */
	protected $db;

	/**
		* Constructor
		*
		* @param \phpbb\auth\auth			$auth			Authentication object
		* @param \phpbb\config\config		$config			Config Object
		* @param \phpbb\template\template	$template		Template object
		* @param \phpbb\user				$user			User Object
		* @param \phpbb\db\driver\driver	$db				Database object
		* @access public
		*/
	public function __construct(
			\phpbb\auth\auth $auth,
			\phpbb\config\config $config,
			\phpbb\template\template $template,
			\phpbb\user $user,
			\phpbb\db\driver\driver_interface $db)
	{
		$this->auth = $auth;
		$this->config = $config;
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

		// Start time for current month
		$month_start_cur	= gmmktime (0,0,0, $month_cur, 1, $year_cur);
		$month_start		= $month_start_cur;
		$month_end			= $now;

		/*
		* group_id 5 = administrators
		* group_id 4 = global moderators
		* this groups belong to a Vanilla 3.1.x board
		*/
		$group_ids = array(5, 4);

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

		/*
		* Let's go then..
		* Posts made into the selected elapsed time
		*/
		$topm_tp = $row['total_posts'];
		$topm_un = get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']);

		/*
		* There is not a Top Poster yet, usually happens with fresh installations.
		* Where only the FOUNDER made the first post/topic.
		* No normal users already did it or at least not into the current month.
		* Here TOPM_UN reflects this state.
		*/
		$this->template->assign_vars(array(
			'TOPM_UN'			=> ($topm_tp < 1) ? $topm_un = $this->user->lang['TOP_USERNAME_NONE'] : $topm_un,
			'L_TPOTM'			=> $this->user->lang['TOP_CAT'],
			'L_TOPM_UNA_L'		=> $this->user->lang['TOP_USERNAME'],
			'L_TOPM_UPO_L'		=> sprintf($this->user->lang['TOP_USER_MONTH_POSTS'], $topm_tp),
			'L_TOPM_POSTS_L'	=> ($topm_tp > 1 || $topm_tp == 0 ) ? $this->user->lang['TOP_POSTS'] : $this->user->lang['TOP_POST'],
		));
	}
}
