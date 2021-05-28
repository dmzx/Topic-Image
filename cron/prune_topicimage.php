<?php
/**
 *
 * Topic Image. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2021, dmzx, https://www.dmzx-web.net
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace dmzx\topicimage\cron;

use phpbb\config\config;
use dmzx\topicimage\event\helper;

class prune_topicimage extends \phpbb\cron\task\base
{
	/** @var config */
	protected $config;

	/** @var helper */
	protected $helper;

	/**
	 * Constructor
	 *
	 * @param config		$config
	 * @param helper		$helper
	 *
	 */
	public function __construct(
		config $config,
		helper $helper
	)
	{
		$this->config			= $config;
		$this->helper 			= $helper;
	}

	/**
	 * Runs this cron task.
	 *
	 * @return null
	 */
	public function run()
	{
		$this->helper->cron_images();
		$this->config->set('dmzx_topicimage_last_gc', time());
	}

	/**
	 * Returns whether this cron task can run, given current board configuration.
	 *
	 * If warnings are set to never expire, this cron task will not run.
	 *
	 * @return bool
	 */
	public function is_runnable()
	{
		return $this->config['dmzx_topicimage_time_enable'];
	}

	/**
	 * Returns whether this cron task should run now, because enough time
	 * has passed since it was last run (24 hours).
	 *
	 * @return bool
	 */
	public function should_run()
	{
		return $this->config['dmzx_topicimage_last_gc'] < time() - $this->config['dmzx_topicimage_gc'];
	}
}
