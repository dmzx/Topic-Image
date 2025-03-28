<?php
/**
 *
 * Topic Image. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2025, dmzx, https://www.dmzx-web.net
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace dmzx\topicimage\migrations;

class topicimage_v102 extends \phpbb\db\migration\migration
{
	public static function depends_on()
	{
		return [
			'\dmzx\topicimage\migrations\topicimage_v101'
		];
	}

	public function update_data()
	{
		return [
			// Update config
			['config.update', ['dmzx_topicimage_version', '1.0.2']],
			// Add config
			['config.add', ['dmzx_topicimage_width', 90]],
			['config.add', ['dmzx_topicimage_show_title', 0]],
			['config.add', ['dmzx_topicimage_colour_title', '000000']],
		];
	}
}
