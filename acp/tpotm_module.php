<?php
/**
 *
 * Top Poster Of The Month. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2005, 2019, 3Di <https://www.phpbbstudio.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace threedi\tpotm\acp;

use \threedi\tpotm\ext;

/**
 * Top Poster Of The Month ACP module.
 */
class tpotm_module
{
	public $page_title;
	public $tpl_name;
	public $u_action;

	public function main($id, $mode)
	{
		global $config, $request, $template, $user, $phpbb_log, $db, $phpbb_root_path;

		$rootpath = (defined('PHPBB_USE_BOARD_URL_PATH') && PHPBB_USE_BOARD_URL_PATH) ? generate_board_url() . '/' : $phpbb_root_path;

		$user->add_lang_ext('threedi/tpotm', 'acp_tpotm');

		$this->tpl_name = 'tpotm_body';

		$this->page_title = $user->lang('ACP_TPOTM_TITLE');

		add_form_key('threedi/tpotm');

		/**
		 * Drop down construct inspired by MChat.
		 */
		$time_modes = [
			ext::NO_CACHE	=> 'no_cache',
			ext::ONE_DAY	=> 'one_day',
			ext::ONE_WEEK	=> 'one_week',
			ext::TWO_WEEKS	=> 'two_weeks',
			ext::ONE_MONTH	=> 'one_month',
		];

		$time_row_options = '';

		foreach ($time_modes as $val => $time_mode)
		{
			$time_row_options .= '<option value="' . $val . '"' . (($val == $time_mode) ? ' selected="selected"' : '') . '>';
			$time_row_options .= $user->lang('TPOTM_ACP_' . strtoupper($time_mode));
			$time_row_options .= '</option>';
		}

		/* Do this now and forget */
		$errors = [];

		if ($request->is_set_post('submit'))
		{
			if (!check_form_key('threedi/tpotm'))
			{
				trigger_error('FORM_INVALID', E_USER_WARNING);
			}

			/* Check for all installed styles first */
			$sql = 'SELECT style_path
				FROM ' . STYLES_TABLE;
			$result = $db->sql_query($sql);

			while ($rows = $db->sql_fetchrow($result))
			{
				$styles_installed[] = $rows['style_path'];
			}

			$db->sql_freeresult($result);

			/**
			 * Now check for the correct existance of the TPOTM's image badge into
			 * each of the effectively installed styles and report a detailed list on failure.
			 */
			foreach ($styles_installed as $style_installed)
			{
				if (!file_exists($rootpath . 'ext/threedi/tpotm/styles/' . $style_installed . '/theme/images/tpotm_badge.png'))
				{
					$errors[] = $user->lang('TPOTM_BADGE_IMG_INVALID', $style_installed);

					$phpbb_log->add('critical', $user->data['user_id'], $user->ip, 'TPOTM_LOG_BADGE_IMG_INVALID');
				}
			}

			/* No errors? Great, let's go. */
			if (!count($errors))
			{
				$config->set('threedi_tpotm_index', $request->variable('threedi_tpotm_index', (int) $config['threedi_tpotm_index']));
				$config->set('threedi_tpotm_forums', $request->variable('threedi_tpotm_forums', (int) $config['threedi_tpotm_forums']));
				$config->set('threedi_tpotm_hall', $request->variable('threedi_tpotm_hall', (int) $config['threedi_tpotm_hall']));
				$config->set('threedi_tpotm_users_page', $request->variable('threedi_tpotm_users_page', (int) $config['threedi_tpotm_users_page']));
				$config->set('threedi_tpotm_ttl_mode', $request->variable('threedi_tpotm_ttl_mode', (int) $config['threedi_tpotm_ttl_mode']));
				$config->set('threedi_tpotm_ttl_tpe', $request->variable('threedi_tpotm_ttl_tpe', (int) $config['threedi_tpotm_ttl_tpe']));
				$config->set('threedi_tpotm_since_epoch', $request->variable('threedi_tpotm_since_epoch', (int) $config['threedi_tpotm_since_epoch']));
				$config->set('threedi_tpotm_ttl', $request->variable('threedi_tpotm_ttl', (int) $config['threedi_tpotm_ttl']));
				$config->set('threedi_tpotm_miniavatar', $request->variable('threedi_tpotm_miniavatar', (int) $config['threedi_tpotm_miniavatar']));
				$config->set('threedi_tpotm_miniprofile', $request->variable('threedi_tpotm_miniprofile', (int) $config['threedi_tpotm_miniprofile']));
				$config->set('threedi_tpotm_adm_mods', $request->variable('threedi_tpotm_adm_mods', (int) $config['threedi_tpotm_adm_mods']));
				$config->set('threedi_tpotm_founders', $request->variable('threedi_tpotm_founders', (int) $config['threedi_tpotm_founders']));
				$config->set('threedi_tpotm_banneds', $request->variable('threedi_tpotm_banneds', (int) $config['threedi_tpotm_banneds']));

				/* Log the action and return */
				$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'TPOTM_LOG_CONFIG_SAVED');

				trigger_error($user->lang('ACP_TPOTM_SETTING_SAVED') . adm_back_link($this->u_action));
			}
		}

		$s_errors = !empty($errors);

		$template->assign_vars([
			'S_ERRORS'				=> $s_errors,

			'ERRORS_MSG'			=> $s_errors ? implode('<br>', $errors) : '',

			'TPOTM_INDEX'			=> (bool) $config['threedi_tpotm_index'],
			'TPOTM_FORUMS'			=> (bool) $config['threedi_tpotm_forums'],
			'TPOTM_HALL'			=> (bool) $config['threedi_tpotm_hall'],
			'TPOTM_USERS_PAGE'		=> (int) $config['threedi_tpotm_users_page'],
			'TPOTM_TTL_MODE'		=> (bool) $config['threedi_tpotm_ttl_mode'],
			'S_TPOTM_TTL_TPE'		=> $time_row_options,
			'TPOTM_TTL_TPE'			=> (int) $config['threedi_tpotm_ttl_tpe'],
			'TPOTM_HALL_EPOCH'		=> (bool) $config['threedi_tpotm_since_epoch'],
			'TPOTM_TTL'				=> (int) $config['threedi_tpotm_ttl'],
			'TPOTM_MINIAVATAR'		=> (bool) $config['threedi_tpotm_miniavatar'],
			'TPOTM_MINIPROFILE'		=> (bool) $config['threedi_tpotm_miniprofile'],
			'TPOTM_ADM_MODS'		=> (bool) $config['threedi_tpotm_adm_mods'],
			'TPOTM_FOUNDERS'		=> (bool) $config['threedi_tpotm_founders'],
			'TPOTM_BANNEDS'			=> (bool) $config['threedi_tpotm_banneds'],

			'U_ACTION'				=> $this->u_action,
		]);
	}
}
