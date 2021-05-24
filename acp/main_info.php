<?php
/**
 *
 * Topic Image. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2021, dmzx, https://www.dmzx-web.net
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace dmzx\topicimage\acp;

/**
 * Topic Image ACP module info.
 */
class main_info
{
	public function module()
	{
		return [
			'filename'	=> '\dmzx\topicimage\acp\main_module',
			'title'		=> 'ACP_TOPICIMAGE_TITLE',
			'modes'		=> [
				'settings'	=> [
					'title'	=> 'ACP_TOPICIMAGE',
					'auth'	=> 'ext_dmzx/topicimage && acl_a_board',
					'cat'	=> ['ACP_TOPICIMAGE_TITLE']
				],
			],
		];
	}
}
