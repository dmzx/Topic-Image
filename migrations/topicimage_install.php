<?php
/**
 *
 * Topic Image. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2021, dmzx, https://www.dmzx-web.net
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace dmzx\topicimage\migrations;

use phpbb\db\migration\migration;

class topicimage_install extends migration
{
	public static function depends_on()
	{
		return [
			'\phpbb\db\migration\data\v330\v330'
		];
	}

	public function update_data()
	{
		return [
			// Add config
			['config.add', ['dmzx_topicimage_version', '1.0.0']],
			['config.add', ['dmzx_topicimage_enable', 0]],
			['config.add', ['dmzx_topicimage_size', 120]],
			['config.add', ['dmzx_topicimage_copyright', $this->config['sitename']]],
			['config.add', ['dmzx_topicimage_place', 0]],
			['config.add', ['dmzx_topicimage_img_folder', 'topicimage']],
			['config.add', ['dmzx_topicimage_included', 0]],
			['config.add', ['dmzx_topicimage_effect', 'linear']],
			['config.add', ['dmzx_topicimage_direction', 'left']],
			['config.add', ['dmzx_topicimage_timer', 500]],
			['config.add', ['dmzx_topicimage_items', 1]],

			// Add permission
			['permission.add', ['u_dmzx_topicimage_use', true]],

			// Set permission
			['permission.permission_set', ['ADMINISTRATORS', 'u_dmzx_topicimage_use', 'group']],

			['module.add', [
				'acp',
				'ACP_CAT_DOT_MODS',
				'ACP_TOPICIMAGE_TITLE'
			]],
			['module.add', [
				'acp',
				'ACP_TOPICIMAGE_TITLE',
				[
					'module_basename'	=> '\dmzx\topicimage\acp\main_module',
					'modes'				=> ['settings'],
				],
			]],
		];
	}
}
