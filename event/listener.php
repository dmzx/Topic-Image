<?php
/**
 *
 * Topic Image. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2021, dmzx, https://www.dmzx-web.net
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace dmzx\topicimage\event;

use phpbb\config\config;
use phpbb\language\language;
use phpbb\template\template;
use phpbb\path_helper;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Topic Image Event listener.
 */
class listener implements EventSubscriberInterface
{
	/** @var config */
	protected $config;

	/** @var language */
	protected $language;

	/** @var helper */
	protected $helper;

	/** @var template */
	protected $template;

	/** @var path_helper */
	protected $path_helper;

	/** @var string */
	protected $root_path;

	/** @var string */
	protected $php_ext;

	/**
	 * {@inheritdoc}
	 */
	public static function getSubscribedEvents()
	{
		return [
			'core.index_modify_page_title'			=> 'index_modify_page_title',
			'core.viewforum_modify_topics_data'		=> 'viewforum_modify_topics_data',
			'core.viewforum_modify_topicrow'		=> 'viewforum_modify_topicrow',
			'core.permissions'						=> 'permissions',
		];
	}

	/**
	 * Constructor
	 *
	 * @param config 					$config
	 * @param language					$language
	 * @param helper 					$helper
	 * @param template					$template
	 * @param path_helper 				$path_helper
	 * @param string					$root_path
	 * @param string					$php_ext
	 */
	public function __construct(
		config $config,
		language $language,
		helper $helper,
		template $template,
		path_helper $path_helper,
		string $root_path,
		string $php_ext
	)
	{
		$this->config 		= $config;
		$this->language		= $language;
		$this->helper 		= $helper;
		$this->template 	= $template;
		$this->path_helper 	= $path_helper;
		$this->root_path 	= $root_path;
		$this->php_ext 		= $php_ext;
	}

	public function index_modify_page_title($event)
	{
		if ($this->config['dmzx_topicimage_enable'])
		{
			// Add our common language file
			$this->language->add_lang('common', 'dmzx/topicimage');

			$board_url = generate_board_url() . '/';
			$corrected_path = $this->path_helper->get_web_root_path();
			$image_path = ((defined('PHPBB_USE_BOARD_URL_PATH') && PHPBB_USE_BOARD_URL_PATH) ? $board_url : $corrected_path) . 'images/' . $this->config['dmzx_topicimage_img_folder'] . '/';

			$files = glob($image_path . "*.*",GLOB_BRACE);

			for ($i = 0; $i < count($files); $i++)
			{
				$image = $files[$i];
				$name_explode	= explode("-", basename($image));

				$this->template->assign_block_vars('dmzx_topicimage', [
					'DMZX_TOPICIMAGE_POST'	=> append_sid("{$this->root_path}viewtopic.$this->php_ext", 'f=' . $name_explode[1]) . '&amp;t=' . $name_explode[2],
					'DMZX_TOPICIMAGE_IMG'	=> generate_board_url() . '/' . $image,
					'DMZX_TOPICIMAGE_ALT'	=> $name_explode[4],
					'DMZX_TOPICIMAGE_TITLE'	=> $name_explode[3],
				]);
			}

			$this->template->assign_vars([
				'S_DMZX_TOPICIMAGE_ENABLE'		=> $this->config['dmzx_topicimage_enable'],
				'DMZX_TOPICIMAGE_SIZE'			=> $this->config['dmzx_topicimage_size'] + 10,
				'DMZX_TOPICIMAGE_HEIGHT'		=> $this->config['dmzx_topicimage_size'],
				'S_DMZX_TOPICIMAGE_PLACE'		=> $this->config['dmzx_topicimage_place'],
				'DMZX_TOPICIMAGE_EFFECT'		=> $this->config['dmzx_topicimage_effect'],
				'DMZX_TOPICIMAGE_DIRECTION'		=> $this->config['dmzx_topicimage_direction'],
				'DMZX_TOPICIMAGE_TIMER'			=> $this->config['dmzx_topicimage_timer'],
				'DMZX_TOPICIMAGE_ITEMS'			=> $this->config['dmzx_topicimage_items'],
			]);
		}
	}

	public function permissions($event)
	{
		$event['permissions'] = array_merge($event['permissions'], [
			'u_dmzx_topicimage_use'	=> [
				'lang'		=> 'ACL_U_DMZX_TOPICIMAGE_USE',
				'cat'		=> 'Topic Image'
			],
		]);
		$event['categories'] = array_merge($event['categories'], [
			'Topic Image'	=> 'ACL_U_DMZX_TOPICIMAGE',
		]);
	}

	public function viewforum_modify_topics_data($event)
	{
		$this->helper->update_row_data($event);
	}

	public function viewforum_modify_topicrow($event)
	{
		$this->helper->update_tpl_data($event);
	}
}
