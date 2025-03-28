<?php
/**
 *
 * Topic Image. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2021, dmzx, https://www.dmzx-web.net
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = [];
}

// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine
//
// Some characters you may want to copy&paste:
// ’ » “ ” …
//

$lang = array_merge($lang, [
	'ACP_DMZX_TOPICIMAGE_ENABLE'					=> 'Enable',
	'ACP_DMZX_TOPICIMAGE_SIZE'						=> 'Set size',
	'ACP_DMZX_TOPICIMAGE_SIZE_EXPLAIN'				=> 'Set thumbnail size.',
	'ACP_DMZX_TOPICIMAGE_COPYRIGHT'					=> 'Copyright',
	'ACP_DMZX_TOPICIMAGE_COPYRIGHT_EXPLAIN'			=> 'Add copyright to the images.',
	'ACP_DMZX_TOPICIMAGE_PLACE'						=> 'Select place on index',
	'ACP_DMZX_TOPICIMAGE_PLACE_EXPLAIN'				=> 'Top or bottom of the forum index.',
	'ACP_DMZX_TOPICIMAGE_TOP_OF_FORUM'				=> 'Top of index page',
	'ACP_DMZX_TOPICIMAGE_BOTTOM_OF_FORUM'			=> 'Bottom of index page',
	'ACP_DMZX_TOPICIMAGE_EFFECT'					=> 'Set effect',
	'ACP_DMZX_TOPICIMAGE_EFFECT_EXPLAIN'			=> 'Select your effect here.',
	'ACP_DMZX_TOPICIMAGE_EFFECT_LINEAR'				=> 'Linear',
	'ACP_DMZX_TOPICIMAGE_EFFECT_SWING'				=> 'Swing',
	'ACP_DMZX_TOPICIMAGE_EFFECT_QUADRATIC'			=> 'Quadratic',
	'ACP_DMZX_TOPICIMAGE_EFFECT_CUBIC'				=> 'Cubic',
	'ACP_DMZX_TOPICIMAGE_EFFECT_ELASTIC'			=> 'Elastic',
	'ACP_DMZX_TOPICIMAGE_DIRECTION'					=> 'Set direction',
	'ACP_DMZX_TOPICIMAGE_DIRECTION_EXPLAIN'			=> 'Set image direction.',
	'ACP_DMZX_TOPICIMAGE_EFFECT_LEFT'				=> 'Left',
	'ACP_DMZX_TOPICIMAGE_EFFECT_RIGHT'				=> 'Right',
	'ACP_DMZX_TOPICIMAGE_ITEMS'						=> 'Items',
	'ACP_DMZX_TOPICIMAGE_ITEMS_EXPLAIN'				=> 'The number of items to scroll.',
	'ACP_DMZX_TOPICIMAGE_TIMER'						=> 'Set timer effect',
	'ACP_DMZX_TOPICIMAGE_TIMER_EXPLAIN'				=> 'Timer in milliseconds.<br>From 500 till 5000 milliseconds.',
	'ACP_DMZX_TOPICIMAGE_IMG_FOLDER'				=> 'Directory',
	'ACP_DMZX_TOPICIMAGE_IMG_FOLDER_EXPLAIN'		=> 'Set directory name.<br>All images will be stored in images folder.',
	'ACP_DMZX_TOPICIMAGE_INCLUDE'					=> 'Include forums',
	'ACP_DMZX_TOPICIMAGE_INCLUDE_EXPLAIN'			=> 'Include forums here that will be used to take images.<br>Select multiple groups by holding <samp>CTRL</samp> (or <samp>&#8984;CMD</samp> on Mac) and clicking.',
	'ACP_DMZX_TOPICIMAGE_CLEAR_ALL'					=> 'Delete all images',
	'ACP_DMZX_TOPICIMAGE_CLEAR_ALL_EXPLAIN'			=> 'Delete all images in <b>%s</b> folder.',
	'ACP_DMZX_TOPICIMAGE_CLEAR_ALL_EMPTY'			=> 'All images are already deleted.',
	'ACP_DMZX_TOPICIMAGE_GRAB'						=> 'Grab images',
	'ACP_DMZX_TOPICIMAGE_GRAB_EXPLAIN'				=> 'Grab all images from included forums.<br>Include forums must be set first.',
	'ACP_DMZX_TOPICIMAGE_AMOUNT'					=> 'Set amount',
	'ACP_DMZX_TOPICIMAGE_AMOUNT_EXPLAIN'			=> 'Set amount of last topics that need to be searched.<br>From 1 till 500 topics.',
	'ACP_DMZX_TOPICIMAGE_TIME_ENABLE'				=> 'Enable prune',
	'ACP_DMZX_TOPICIMAGE_TIME_ENABLE_EXPLAIN'		=> 'Enable prune for images.',
	'ACP_DMZX_TOPICIMAGE_PRUNE_TIME'				=> 'Set prune time',
	'ACP_DMZX_TOPICIMAGE_TIME_EXPLAIN'				=> 'Set prune time in hours.<br>All images will be deleted and grabbed again.',
	'ACP_DMZX_TOPICIMAGE_PRUNE_HOURS'				=> 'Hours',
	'ACP_DMZX_TOPICIMAGE_GRAB_NO_FORUM_SELECTED'	=> '<strong>No forums selected.</strong>',
	'ACP_DMZX_TOPICIMAGE_CLEAR_ALL_SUCCESS'			=> 'Deleted all images.',
	'ACP_DMZX_TOPICIMAGE_CLEAR_ALL_ERROR'			=> 'Error cannot find folder.',
	'ACP_DMZX_TOPICIMAGE_GRAB_IMAGES'				=> '<strong>Forums checked</strong><br>The following thumbnails were created or need to be checked<br>%s',
	'ACP_DMZX_TOPICIMAGE_GRAB_NOTHING'				=> '<strong>No new images found.</strong>',
	'ACP_TOPICIMAGE_SETTING_SAVED'					=> 'Settings have been saved successfully!',
	'ACP_DMZX_TOPICIMAGE_CONFIRM_DELETE'			=> 'Confirm delete',
	'ACP_DMZX_TOPICIMAGE_WIDTH'						=> 'Set width',
	'ACP_DMZX_TOPICIMAGE_WIDTH_EXPLAIN'				=> 'Set thumbnail width.',
	'ACP_DMZX_TOPICIMAGE_SHOW_TITLE'				=> 'Show title under image',
	'ACP_DMZX_TOPICIMAGE_SHOW_TITLE_EXPLAIN'		=> 'Show title under image, will be sliced to 20 characters.',
	'ACP_DMZX_TOPICIMAGE_COLOUR_TITLE'				=> 'Set colour title',
	'ACP_DMZX_TOPICIMAGE_COLOUR_TITLE_EXPLAIN'		=> 'Set colour of title under images.',
]);