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

use phpbb\auth\auth;
use phpbb\config\config;
use phpbb\db\driver\driver_interface;

/**
 * Topic Image helper.
 */
class helper
{
	/** @var auth */
	protected $auth;

	/** @var config */
	protected $config;

	/** @var driver_interface */
	protected $db;

	/** @var string */
	protected $tables;

	/** @var string */
	protected $root_path;

	/**
	 * Constructor
	 *
	 * @param auth			 			$auth
	 * @param config					$config
	 * @param driver_interface 			$db
	 * @param tables					$tables
	 * @param string					$root_path
	 */
	public function __construct(
		auth $auth,
		config $config,
		driver_interface $db,
		array $tables,
		string $root_path
	)
	{
		$this->auth 		= $auth;
		$this->config 		= $config;
		$this->db 			= $db;
		$this->tables		= $tables;
		$this->root_path 	= $root_path;
	}

	public function update_row_data($event)
	{
		if (!$this->config['dmzx_topicimage_enable'] || !$this->auth->acl_get('u_dmzx_topicimage_use'))
		{
			return;
		}

		$topicimage = $event->offsetExists('topic_list') ? $event['topic_list'] : array_keys($event['rowset']);

		if (count($topicimage))
		{
			$event['rowset'] = $this->query_images($topicimage, $event['rowset']);
		}
	}

	public function update_tpl_data($event)
	{
		if (empty($event['row']['post_text']) || !$this->forum_allowed($event['row']['forum_id']) || !$this->config['dmzx_topicimage_enable'])
		{
			return;
		}

		$thumbs = [];

		$image_path = '/images/' . $this->config['dmzx_topicimage_img_folder'] . '/';

		$topic_title = str_replace(['&quot;', '&amp;', '/'], ' ', $event['row']['topic_title']);

		$topicimage 		= $this->extract_images($event['row']['post_text']);
		$topicimage			= str_replace('https', 'http', json_encode($topicimage));
		$postimage_pre		= strrchr($topicimage,"/");
		$postimage_pre		= substr($postimage_pre, 1);
		$postimage_pre		= strtolower(preg_replace('#[^a-zA-Z0-9_+.-]#', '', $postimage_pre));
		$thumbnail_file 	= $image_path . 'topicimage-' . $event['row']['forum_id'] . '-' . $event['row']['topic_id']	. '-' . $topic_title . '-' . $postimage_pre;

		$create_count = 0;
		if (!file_exists($this->root_path . $thumbnail_file))
		{
			foreach($this->extract_images($event['row']['post_text']) as $image)
			{
				if ($this->url_exists($image))
				{
					$this->img_resize($image, (int) $this->config['dmzx_topicimage_size'], $this->root_path . $thumbnail_file, $this->config['dmzx_topicimage_copyright']);
					$create_count++;
					$thumbs[] = 'topicimage-' . $event['row']['forum_id'] . '-' . $event['row']['topic_id'] . '-' . $topic_title . '-' . $postimage_pre;
				}
				else
				{
					return;
				}
			}
		}
		else
		{
			return;
		}

		$counts = [$create_count];
		$create_thumbs['thumbs'] = $thumbs;
		$create_thumbs['counts'] = $counts;

		return $create_thumbs;
	}

	protected function query_images(array $topicimage, array $rowset)
	{
		foreach ($topicimage as $topic_id)
		{
			if (!$this->forum_allowed($rowset[$topic_id]['forum_id']))
			{
				continue;
			}

			$sql = 'SELECT p.post_id, p.topic_id, p.forum_id, p.post_text, p.post_subject, p.post_time, p.post_visibility, t.topic_id, t.topic_title, t.topic_first_post_id
				FROM ' . $this->tables['posts'] . ' p, ' . $this->tables['topics'] . ' t
				WHERE post_text ' . $this->db->sql_like_expression('<r>' . $this->db->get_any_char() . '<IMG ' . $this->db->get_any_char()) . '
					AND p.topic_id = ' . (int) $topic_id . '
					AND p.post_visibility = ' . ITEM_APPROVED . '
					AND p.post_id = t.topic_first_post_id
				ORDER BY p.post_time DESC';
			$result = $this->db->sql_query($sql);
			while ($row = $this->db->sql_fetchrow($result))
			{
				$rowset[$row['topic_id']]['post_text'] = $row['post_text'];
			}
			$this->db->sql_freeresult($result);
		}
		return $rowset;
	}

	public function cron_images()
	{
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
			}
		}
		$this->grab_images();
	}

	public function grab_images()
	{
		$thumbs = [];

		$sql = 'SELECT p.post_id, p.topic_id, p.forum_id, p.post_text, p.post_subject, p.post_time, p.post_visibility, t.topic_id, t.topic_title, t.topic_first_post_id
			FROM ' . $this->tables['posts'] . ' p, ' . $this->tables['topics'] . ' t
			WHERE post_text ' . $this->db->sql_like_expression('<r>' . $this->db->get_any_char() . '<IMG ' . $this->db->get_any_char()) . '
				AND p.forum_id IN (' . chop($this->config['dmzx_topicimage_included'], ' ,') . ')
				AND p.post_visibility = ' . ITEM_APPROVED . '
				AND p.post_id = t.topic_first_post_id
			ORDER BY p.post_time DESC';
		$result = $this->db->sql_query_limit($sql, $this->config['dmzx_topicimage_amount']);
		$att_count = $create_count = 0;
		while ($row = $this->db->sql_fetchrow($result))
		{
			$image_path = '/images/' . $this->config['dmzx_topicimage_img_folder'] . '/';

			$topic_title = str_replace(['&quot;', '&amp;', '/'], ' ', $row['topic_title']);

			$topicimage 		= $this->extract_images($row['post_text']);
			$topicimage			= str_replace('https', 'http', json_encode($topicimage));
			$postimage_pre		= strrchr($topicimage,"/");
			$postimage_pre		= substr($postimage_pre, 1);
			$postimage_pre		= strtolower(preg_replace('#[^a-zA-Z0-9_+.-]#', '', $postimage_pre));
			$thumbnail_file 	= $image_path . 'topicimage-' . $row['forum_id'] . '-' . $row['topic_id']	. '-' . $topic_title . '-' . $postimage_pre;

			$results = true;
			if (!file_exists($this->root_path . $thumbnail_file))
			{
				foreach($this->extract_images($row['post_text']) as $image)
				{
					if ($this->url_exists($image))
					{
						$this->img_resize($image, (int) $this->config['dmzx_topicimage_size'], $this->root_path . $thumbnail_file, $this->config['dmzx_topicimage_copyright']);
						$results = true;
						$create_count++;
						$thumbs[] = 'topicimage-' . $row['forum_id'] . '-' . $row['topic_id'] . '-' . $topic_title . '-' . $postimage_pre;
					}
					else
					{
						$results = false;
					}
				}
			}

			if ($results)
			{
				$att_count++;
			}
		}
		$this->db->sql_freeresult($result);

		$counts = [$att_count, $create_count];
		$create_thumbs['thumbs'] = $thumbs;
		$create_thumbs['counts'] = $counts;

		return $create_thumbs;
	}

	protected function extract_images($post)
	{
		$images = [];
		$dom = new \DOMDocument;
		$dom->loadXML($post);
		$xpath = new \DOMXPath($dom);
		foreach ($xpath->query('//IMG[not(ancestor::IMG)]/@src') as $image)
		{
			$images[] = $image->textContent;
		}

		return array_slice($images, 0, (int) 1, true); // set to 1 for 1 image first post
	}

	protected function forum_allowed($forum_id)
	{
		return in_array($forum_id, explode(',', $this->config['dmzx_topicimage_included']));
	}

	protected function url_exists($url)
	{
		if (@getimagesize($url))
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	protected function img_resize($file, $resize, $thumbnail_file, $copy = false)
	{
		$size = @getimagesize($file);

		if (!isset($size[0]) || !isset($size[1]))
		{
			return;
		}
		$image_type = $size[2];

		if ($resize > $size[0] || $resize > $size[1])
		{
			return;
		}

		switch ($image_type)
		{
			case IMAGETYPE_JPEG:
				$image = imagecreatefromjpeg($file);
			break;
			case IMAGETYPE_GIF:
				$image = imagecreatefromgif($file);
			break;
			case IMAGETYPE_PNG:
				$image = imagecreatefrompng($file);
			break;
			default:
			return;
		}

		$width = imagesx($image);
		$height = imagesy($image);

		$thumb_width = $thumb_height = $resize;
		$thumbnail_width = $thumb_width;
		$thumbnail_height = floor($height * ($thumbnail_width/$width));

		$new_left = 0;
		$new_top = floor(($thumbnail_height - $thumb_height)/2);

		if ($thumbnail_height < $thumb_height)
		{
			$thumbnail_height = $thumb_height;
			$thumbnail_width = floor($width * ($thumbnail_height/$height));

			$new_left = floor(($thumbnail_width - $thumb_width)/2);
			$new_top = 0;
		}

		$thumbnail2 = imagecreatetruecolor($thumbnail_width, $thumbnail_height);
		imagecopyresampled($thumbnail2, $image, 0, 0, 0, 0, $thumbnail_width, $thumbnail_height, $width, $height);

		if ($this->config['dmzx_topicimage_size'] || $thumbnail_height > $thumbnail_width)
		{
			$thumbnail = imagecreatetruecolor($thumb_width, $thumb_height);
			imagecopy($thumbnail, $thumbnail2, 0, 0, $new_left, $new_top, $thumb_width, $thumb_height);
			imagedestroy($thumbnail2);
		}
		else
		{
			$thumbnail = $thumbnail2;
		}

		if ($copy && ($thumb_height > 40 || $thumb_width > 40))
		{
			$color = imageColorAllocate($image, 140, 120, 90);
			imageString($thumbnail, 1, ($thumb_width/2)-(strlen($copy)*3-5), $thumb_height-10, $copy, $color);
		}

		switch ($image_type)
		{
			case IMAGETYPE_JPEG:
				imagejpeg($thumbnail, $thumbnail_file, 90);
			break;
			case IMAGETYPE_GIF:
				imagegif($thumbnail, $thumbnail_file);
			break;
			case IMAGETYPE_PNG:
				imagepng($thumbnail, $thumbnail_file, 0);
			break;
		}
		imagedestroy($thumbnail);
	}
}
