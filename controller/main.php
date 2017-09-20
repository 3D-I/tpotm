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
	 * @param \phpbb\config\config		$config
	 * @param \phpbb\extension\manager	$ext_manager
	 * @param \phpbb\controller\helper	$helper
	 * @param \phpbb\path_helper		$path_helper
	 * @param \phpbb\template\template	$template
	 * @param \phpbb\user				$user
	 * @param threedi\tpotm\core\tpotm	$tpotm			Methods to be used by Class
	 * @var string phpBB root path		$root_path
	 */
	public function __construct(\phpbb\config\config $config, \phpbb\extension\manager $ext_manager, \phpbb\controller\helper $helper, \phpbb\path_helper $path_helper, \phpbb\template\template $template, \phpbb\user $user, \threedi\tpotm\core\tpotm $tpotm, $root_path)
	{
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
	 * Returns the absolute URL to the ext_path_web
	 *
	 * @return string
	 */
	public function controller_style_badge_is_true()
	{
		return  file_exists($this->root_path . 'ext/threedi/tpotm/styles/' . rawurlencode($this->user->style['style_path']) . '/theme/images/tpotm_badge.png');
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
		$this->template->assign_var('DEMO_MESSAGE', $this->user->lang($l_message, $name));

		return $this->helper->render('tpotm_hall.html', $name);
	}
}
