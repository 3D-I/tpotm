<?php
/**
 *
 * Top Poster Of The Month. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2005,2017, 3Di
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
		global $config, $request, $template, $user, $phpbb_log, $phpbb_container;

		$tpotm = $phpbb_container->get('threedi.tpotm.tpotm');

		$user->add_lang_ext('threedi/tpotm', 'acp_tpotm');

		$this->tpl_name = 'tpotm_body';
		$this->page_title = $user->lang('ACP_TPOTM_TITLE');

		add_form_key('threedi/tpotm');

		/**
		 * If Img Badge filename error..
		 * state is false and return, else go on..
		 */
		$tpotm->check_point_badge_img();

		/**
		 * If the Img Badge's filename is wrong then stop the party and go dormant.
		 */
		if (!$tpotm->style_badge_is_true())
		{
			/* Log the error. */
			$phpbb_log->add('critical', $user->data['user_id'], $user->ip, 'TPOTM_LOG_BADGE_IMG_INVALID');

			trigger_error($user->lang('TPOTM_BADGE_IMG_INVALID') . adm_back_link($this->u_action), E_USER_WARNING);
		}

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

		$selected = $time_row_options = '';

		foreach ( $time_modes as $val => $time_mode )
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

			/**
			 * If Img Badge filename error..
			 * state is false and return, else go on..
			 */
			$tpotm->check_point_badge_img();

			/* You changed filenames? No party! */
			if (!$config['threedi_tpotm_badge_exists'])
			{
				$errors[] = $user->lang('TPOTM_BADGE_IMG_INVALID');
				/* Log the error. */
				$phpbb_log->add('critical', $user->data['user_id'], $user->ip, 'TPOTM_LOG_BADGE_IMG_INVALID');
			}

			/* No errors? Great, let's go. */
			if (!count($errors))
			{
				$config->set('threedi_tpotm_index', $request->variable('threedi_tpotm_index', (int) $config['threedi_tpotm_index']));
				$config->set('threedi_tpotm_forums', $request->variable('threedi_tpotm_forums', (int) $config['threedi_tpotm_forums']));
				$config->set('threedi_tpotm_hall', $request->variable('threedi_tpotm_hall', (int) $config['threedi_tpotm_hall']));
				$config->set('threedi_tpotm_users_page', $request->variable('threedi_tpotm_users_page', (int) $config['threedi_tpotm_users_page']));
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

		$template->assign_vars([
			'S_ERRORS'				=> ($errors) ? true : false,
			'ERRORS_MSG'			=> ($errors) ? implode('<br /><br />', $errors) : '',
			'U_ACTION'				=> $this->u_action,
			// Template locations
			'TPOTM_INDEX'			=> ($config['threedi_tpotm_index']) ? true : false,
			'TPOTM_FORUMS'			=> ($config['threedi_tpotm_forums']) ? true : false,
			// Hall of fame
			'TPOTM_HALL'			=> ($config['threedi_tpotm_hall']) ? true : false,
			'TPOTM_USERS_PAGE'		=> (int) $config['threedi_tpotm_users_page'],
			'S_TPOTM_TPE_TTL'		=> $time_row_options,
			'TPOTM_TTL_TPE'			=>	(int) $config['threedi_tpotm_ttl_tpe'],
			'TPOTM_HALL_EPOCH'		=> ($config['threedi_tpotm_since_epoch']) ? true : false,
			// General Settings
			'TPOTM_TTL'				=> (int) $config['threedi_tpotm_ttl'],
			'TPOTM_MINIAVATAR'		=> ($config['threedi_tpotm_miniavatar']) ? true : false,
			'TPOTM_MINIPROFILE'		=> ($config['threedi_tpotm_miniprofile']) ? true : false,
			// Founders, admin and mods
			'TPOTM_ADM_MODS'		=> ($config['threedi_tpotm_adm_mods']) ? true : false,
			'TPOTM_FOUNDERS'		=> ($config['threedi_tpotm_founders']) ? true : false,
			'TPOTM_BANNEDS'			=> ($config['threedi_tpotm_banneds']) ? true : false,
		]);
	}
}
