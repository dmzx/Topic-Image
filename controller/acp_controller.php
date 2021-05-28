<?php
/**
 *
 * Topic Image. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2021, dmzx, https://www.dmzx-web.net
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace dmzx\topicimage\controller;

use phpbb\config\config;
use phpbb\language\language;
use dmzx\topicimage\event\helper;
use phpbb\log\log_interface;
use phpbb\request\request_interface;
use phpbb\template\template;
use phpbb\user;
use phpbb\path_helper;

/**
 * Topic Image ACP controller.
 */
class acp_controller
{
	/** @var config */
	protected $config;

	/** @var language */
	protected $language;

	/** @var helper */
	protected $helper;

	/** @var log_interface */
	protected $log;

	/** @var request_interface */
	protected $request;

	/** @var template */
	protected $template;

	/** @var user */
	protected $user;

	/** @var path_helper */
	protected $path_helper;

	/** @var string */
	protected $root_path;

	/* @var array topicimage_constants */
	protected $topicimage_constants;

	/** @var string Custom form action */
	protected $u_action;

	/**
	 * Constructor.
	 *
	 * @param config					$config
	 * @param language					$language
	 * @param log_interface				$log
	 * @param request_interface			$request
	 * @param template					$template
	 * @param user						$user
	 * @param path_helper 				$path_helper
	 * @param string					$root_path
	 * @param array						$topicimage_constants
	 */
	public function __construct(
		config $config,
		language $language,
		helper $helper,
		log_interface $log,
		request_interface $request,
		template $template,
		user $user,
		path_helper $path_helper,
		string $root_path,
		array $topicimage_constants
	)
	{
		$this->config				= $config;
		$this->language				= $language;
		$this->helper 				= $helper;
		$this->log					= $log;
		$this->request				= $request;
		$this->template				= $template;
		$this->user					= $user;
		$this->path_helper 			= $path_helper;
		$this->root_path 			= $root_path;
		$this->topicimage_constants = $topicimage_constants;
	}

	/**
	 * Display the options a user can configure for this extension.
	 *
	 * @return void
	 */
	public function display_options()
	{
		// Add our acp_topicimage language file
		$this->language->add_lang('acp_topicimage', 'dmzx/topicimage');

		// Create a form key for preventing CSRF attacks
		add_form_key('dmzx_topicimage_acp');

		// Create an array to collect errors that will be output to the user
		$errors = [];

		// Check founder
		$is_founder = $this->user->data['user_type'] == USER_FOUNDER;

		// Get the included forums
		$included_forums = explode(',', $this->config['dmzx_topicimage_included']);

		// Determine board url
		$board_url = generate_board_url() . '/';
		$corrected_path = $this->path_helper->get_web_root_path();
		$image_path = ((defined('PHPBB_USE_BOARD_URL_PATH') && PHPBB_USE_BOARD_URL_PATH) ? $board_url : $corrected_path) . 'images/' . $this->config['dmzx_topicimage_img_folder'] . '/';

		if (!is_dir($image_path))
		{
			$this->recursive_mkdir($image_path, 0775);
		}

		// Is the form being submitted to us?
		if ($this->request->is_set_post('submit'))
		{
			// Test if the submitted form is valid
			if (!check_form_key('dmzx_topicimage_acp'))
			{
				$errors[] = $this->language->lang('FORM_INVALID');
			}

			// If no errors, process the form data
			if (empty($errors))
			{
				// Set the options the user configured
				$this->config->set('dmzx_topicimage_enable', $this->request->variable('dmzx_topicimage_enable', 0));
				$this->config->set('dmzx_topicimage_size', $this->request->variable('dmzx_topicimage_size', 0));
				$this->config->set('dmzx_topicimage_copyright', $this->request->variable('dmzx_topicimage_copyright', '', true));
				$this->config->set('dmzx_topicimage_place', $this->request->variable('dmzx_topicimage_place', 0));
				$this->config->set('dmzx_topicimage_effect', $this->request->variable('dmzx_topicimage_effect', '', true));
				$this->config->set('dmzx_topicimage_direction', $this->request->variable('dmzx_topicimage_direction', '', true));
				$this->config->set('dmzx_topicimage_timer', $this->request->variable('dmzx_topicimage_timer', 0));
				$this->config->set('dmzx_topicimage_items', $this->request->variable('dmzx_topicimage_items', 0));
				$this->config->set('dmzx_topicimage_amount', $this->request->variable('dmzx_topicimage_amount', 0));
				$this->config->set('dmzx_topicimage_time_enable', $this->request->variable('dmzx_topicimage_time_enable', 0));
				$this->config->set('dmzx_topicimage_gc', $this->request->variable('dmzx_topicimage_gc', 0) * 3600);

				$forums = $this->request->variable('selectForms',	['']);
				// Change the array to a string
				$forums	= implode(',', $forums);
				$this->config->set('dmzx_topicimage_included', $forums);

				// Add option settings change action to the admin log
				$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_ACP_TOPICIMAGE_SETTINGS');

				// Option settings have been updated and logged
				// Confirm this to the user and provide link back to previous page
				trigger_error($this->language->lang('ACP_TOPICIMAGE_SETTING_SAVED') . adm_back_link($this->u_action));
			}
		}

		if ($this->request->is_set_post('dmzx_topicimage_clear_all'))
		{
			// Test if the submitted form is valid
			if (!check_form_key('dmzx_topicimage_acp'))
			{
				$errors[] = $this->language->lang('FORM_INVALID');
			}

			$handle = @opendir($this->root_path . 'images/' . $this->config['dmzx_topicimage_img_folder']);
			$files	= [];

			if ($handle)
			{
				while ($file = readdir($handle))
				{
					if ($file != '.' && $file != '..' && $file != '.htaccess' && $file != 'index.htm' && $file != 'index.html')
					{
						$files[] = $file;
					}
				}
				closedir($handle);

				if (!empty($files))
				{
					foreach ($files as $del_file)
					{
						@unlink($this->root_path . 'images/' . $this->config['dmzx_topicimage_img_folder'] . '/' . $del_file);
					}
					meta_refresh(3, append_sid($this->u_action));
					trigger_error($this->language->lang('ACP_DMZX_TOPICIMAGE_CLEAR_ALL_SUCCESS') . adm_back_link($this->u_action));
				}
				else
				{
					$message = $this->language->lang('ACP_DMZX_TOPICIMAGE_CLEAR_ALL_EMPTY');
				}
			}
			else
			{
				$message = $this->language->lang('ACP_DMZX_TOPICIMAGE_CLEAR_ALL_ERROR');
			}
			meta_refresh(3, append_sid($this->u_action));
			trigger_error($message . adm_back_link($this->u_action), E_USER_WARNING);
		}

		if ($this->request->is_set_post('dmzx_topicimage_grab'))
		{
			// Test if the submitted form is valid
			if (!check_form_key('dmzx_topicimage_acp'))
			{
				$errors[] = $this->language->lang('FORM_INVALID');
			}

			$forum_id = $this->request->variable('selectForms',	['']);
			$forum_id	= implode(',', $forum_id);

			if ($forum_id)
			{
				$result = $this->helper->grab_images();

				if ($result['thumbs'])
				{
					$thumb_names = implode('<br>', $result['thumbs']);
					$message = $this->language->lang('ACP_DMZX_TOPICIMAGE_GRAB_IMAGES', $thumb_names);

					meta_refresh(5, append_sid($this->u_action));
					trigger_error($message);
				}
				else
				{
					meta_refresh(2, append_sid($this->u_action));
					trigger_error($this->language->lang('ACP_DMZX_TOPICIMAGE_GRAB_NOTHING'), E_USER_WARNING);
				}
			}
			else
			{
				meta_refresh(2, append_sid($this->u_action));
				trigger_error($this->language->lang('ACP_DMZX_TOPICIMAGE_GRAB_NO_FORUM_SELECTED'), E_USER_WARNING);
			}
		}

		$s_errors = !empty($errors);

		// Set output variables for display in the template
		$this->template->assign_vars([
			'S_ERROR'							=> $s_errors,
			'ERROR_MSG'							=> $s_errors ? implode('<br>', $errors) : '',
			'DMZX_TOPICIMAGE_INCLUDED'			=> $this->forum_select($included_forums),
			'DMZX_TOPICIMAGE_ENABLE'			=> $this->config['dmzx_topicimage_enable'],
			'DMZX_TOPICIMAGE_SIZE'				=> $this->config['dmzx_topicimage_size'],
			'DMZX_TOPICIMAGE_COPYRIGHT'			=> isset($this->config['dmzx_topicimage_copyright']) ? $this->config['dmzx_topicimage_copyright'] : '',
			'DMZX_TOPICIMAGE_PLACE'				=> $this->location($this->config['dmzx_topicimage_place']),
			'DMZX_TOPICIMAGE_IMG_FOLDER'		=> $this->config['dmzx_topicimage_img_folder'],
			'DMZX_TOPICIMAGE_CLEAR_ALL_EXPLAIN'	=> $this->language->lang('ACP_DMZX_TOPICIMAGE_CLEAR_ALL_EXPLAIN', $this->config['dmzx_topicimage_img_folder']),
			'DMZX_TOPICIMAGE_EFFECT'			=> $this->get_dmzx_topicimage_effect(),
			'DMZX_TOPICIMAGE_DIRECTION'			=> $this->get_dmzx_topicimage_direction(),
			'DMZX_TOPICIMAGE_TIMER'				=> $this->config['dmzx_topicimage_timer'],
			'DMZX_TOPICIMAGE_ITEMS'				=> $this->config['dmzx_topicimage_items'],
			'DMZX_TOPICIMAGE_AMOUNT'			=> $this->config['dmzx_topicimage_amount'],
			'DMZX_TOPICIMAGE_TIME_ENABLE'		=> $this->config['dmzx_topicimage_time_enable'],
			'DMZX_TOPICIMAGE_GC'			 	=> $this->config['dmzx_topicimage_gc'] / 3600,
			'DMZX_TOPICIMAGE_VERSION'			=> $this->config['dmzx_topicimage_version'],
			'DMZX_TOPICIMAGE_FOUNDER'			=> $is_founder,
			'U_ACTION'							=> $this->u_action,
		]);
	}

	// Get some effect
	protected function get_dmzx_topicimage_effect()
	{
		$dmzx_topicimage_effect = '';

		$types = [
			'linear'		=> $this->language->lang('ACP_DMZX_TOPICIMAGE_EFFECT_LINEAR'),
			'swing'			=> $this->language->lang('ACP_DMZX_TOPICIMAGE_EFFECT_SWING'),
			'quadratic'		=> $this->language->lang('ACP_DMZX_TOPICIMAGE_EFFECT_QUADRATIC'),
			'cubic'			=> $this->language->lang('ACP_DMZX_TOPICIMAGE_EFFECT_CUBIC'),
			'elastic'		=> $this->language->lang('ACP_DMZX_TOPICIMAGE_EFFECT_ELASTIC'),
		];

		foreach ($types as $type => $lang)
		{
			$selected	= ($this->config['dmzx_topicimage_effect'] == $type) ? ' selected="selected"' : '';
			$dmzx_topicimage_effect .= '<option value="' . $type . '"' . $selected . '>' . $this->language->lang($lang);
			$dmzx_topicimage_effect .= '</option>';
		}

		return '<select name="dmzx_topicimage_effect" id="dmzx_topicimage_effect">' . $dmzx_topicimage_effect . '</select>';
	}

	// Get some direction
	protected function get_dmzx_topicimage_direction()
	{
		$dmzx_topicimage_direction = '';

		$types = [
			'left'			=> $this->language->lang('ACP_DMZX_TOPICIMAGE_EFFECT_LEFT'),
			'right'			=> $this->language->lang('ACP_DMZX_TOPICIMAGE_EFFECT_RIGHT'),
		];

		foreach ($types as $type => $lang)
		{
			$selected	= ($this->config['dmzx_topicimage_direction'] == $type) ? ' selected="selected"' : '';
			$dmzx_topicimage_direction .= '<option value="' . $type . '"' . $selected . '>' . $this->language->lang($lang);
			$dmzx_topicimage_direction .= '</option>';
		}

		return '<select name="dmzx_topicimage_direction" id="dmzx_topicimage_direction">' . $dmzx_topicimage_direction . '</select>';
	}

	// Make a forum select
	private function forum_select($value)
	{
		return '<select id="dmzx_topicimage_included" name="selectForms[]" multiple="multiple" size="10">' . make_forum_select($value, false, true, true) . '</select>';
	}

	// Rename the image folder
	public function rename_folder()
	{
		$dmzx_topicimage_img_folder = $this->request->variable('dmzx_topicimage_img_folder', 'topicimage');

		if (strpbrk($dmzx_topicimage_img_folder, "\\/?%*:|\"<>") === false)
		{
			rename ($this->root_path . 'images/' . $this->config['dmzx_topicimage_img_folder'], $this->root_path . 'images/' . $dmzx_topicimage_img_folder);
			chmod($this->root_path . 'images/' . $dmzx_topicimage_img_folder, 0775);
			$this->config->set('dmzx_topicimage_img_folder', $dmzx_topicimage_img_folder);
		}
	}

	// Recursive directory
	protected function recursive_mkdir($path, $mode = false)
	{
		if (!$mode)
		{
			$mode = 0755;
		}

		$dirs = explode('/', $path);
		$count = sizeof($dirs);
		$path = '.';
		for ($i = 0; $i < $count; $i++)
		{
			$path .= '/' . $dirs[$i];

			if (!is_dir($path))
			{
				@mkdir($path, $mode);
				@chmod($path, $mode);

				if (!is_dir($path))
				{
					return false;
				}
			}
		}
		return true;
	}

	// Location to show on index
	public function location($value, $key = '')
	{
		$radio_ary = [
			$this->topicimage_constants['top_of_index']	=> 'ACP_DMZX_TOPICIMAGE_TOP_OF_FORUM',
			$this->topicimage_constants['bottom_of_index']	=> 'ACP_DMZX_TOPICIMAGE_BOTTOM_OF_FORUM',
		];

		return h_radio('dmzx_topicimage_place', $radio_ary, $value, $key);
	}

	public function set_page_url($u_action)
	{
		$this->u_action = $u_action;
	}
}
