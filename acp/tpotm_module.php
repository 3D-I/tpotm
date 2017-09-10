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

/**
 * Default Avatar Extended ACP module.
 */
class tpotm_module
{
	public $page_title;
	public $tpl_name;
	public $u_action;

	public function main($id, $mode)
	{
		global $config, $request, $template, $user;

		$user->add_lang_ext('threedi/tpotm', 'acp_tpotm');
		$this->tpl_name = 'tpotm_body';
		$this->page_title = $user->lang('ACP_TPOTM_TITLE');
		add_form_key('threedi/tpotm');

		/* Do this now and forget */
		$errors = array();

		if ($request->is_set_post('submit'))
		{
			if (!check_form_key('threedi/tpotm'))
			{
				trigger_error('FORM_INVALID', E_USER_WARNING);
			}

			/* No errors? Great, let's go. */
			if ( !count($errors) )
			{
				$config->set('threedi_tpotm_index', $request->variable('threedi_tpotm_index', (int) $config['threedi_tpotm_index']));

				$config->set('threedi_tpotm_forums', $request->variable('threedi_tpotm_forums', (int) $config['threedi_tpotm_forums']));

				$config->set('threedi_tpotm_ttl', $request->variable('threedi_tpotm_ttl', (int) $config['threedi_tpotm_ttl']));

				$config->set('threedi_tpotm_miniavatar', $request->variable('threedi_tpotm_miniavatar', (int) $config['threedi_tpotm_miniavatar']));

				$config->set('threedi_tpotm_miniprofile', $request->variable('threedi_tpotm_miniprofile', (int) $config['threedi_tpotm_miniprofile']));

				$config->set('threedi_tpotm_hall', $request->variable('threedi_tpotm_hall', (int) $config['threedi_tpotm_hall']));

				$config->set('threedi_tpotm_adm_mods', $request->variable('threedi_tpotm_adm_mods', (int) $config['threedi_tpotm_adm_mods']));

				trigger_error($user->lang('ACP_TPOTM_SETTING_SAVED') . adm_back_link($this->u_action));
			}
		}

		$template->assign_vars(array(
			'S_ERRORS'				=> ($errors) ? true : false,
			'ERRORS_MSG'			=> ($errors) ? implode('<br /><br />', $errors) : '',
			'U_ACTION'				=> $this->u_action,
			// Template locations
			'TPOTM_INDEX'			=> ($config['threedi_tpotm_index']) ? true : false,
			'TPOTM_FORUMS'			=> ($config['threedi_tpotm_forums']) ? true : false,
			// General Settings
			'TPOTM_TTL'				=> (int) $config['threedi_tpotm_ttl'],
			'TPOTM_MINIAVATAR'		=> ($config['threedi_tpotm_miniavatar']) ? true : false,
			'TPOTM_MINIPROFILE'		=> ($config['threedi_tpotm_miniprofile']) ? true : false,
			'TPOTM_HALL'			=> ($config['threedi_tpotm_hall']) ? true : false,
			'TPOTM_ADM_MODS'		=> ($config['threedi_tpotm_adm_mods']) ? true : false,
		));
	}
}
