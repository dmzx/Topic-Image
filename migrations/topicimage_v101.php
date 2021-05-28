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

class topicimage_v101 extends \phpbb\db\migration\migration
{
	public static function depends_on()
	{
		return [
			'\dmzx\topicimage\migrations\topicimage_install'
		];
	}

	public function update_data()
	{
		return [
			// Update config
			['config.update', ['dmzx_topicimage_version', '1.0.1']],
			// Add config
			['config.add', ['dmzx_topicimage_amount', 500]],
			['config.add', ['dmzx_topicimage_time_enable', 0]],
			['config.add', ['dmzx_topicimage_gc', 86400]],
			['config.add', ['dmzx_topicimage_last_gc', 0, 1]],
		];
	}
}
