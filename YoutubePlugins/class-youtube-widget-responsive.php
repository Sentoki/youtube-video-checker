<?php

namespace PetrovEgor\YoutubePlugins;

use PetrovEgor\common;
use PetrovEgor\Logger;

/**
 * Class that add support Youtube Widget Responsive plugin
 * https://ru.wordpress.org/plugins/youtube-widget-responsive/
 *
 * @package PetrovEgor\YouTubePlugins
 */
class Youtube_Widget_Responsive extends Youtube_Plugin_Abstract {
	public static $instance;

	protected $tag_name = 'youtube';

	public static function class_name() {
		return 'PetrovEgor\YoutubePlugins\Youtube_Widget_Responsive';
	}

	public function short_code_handler( $attr, $content = '' ) {
		global $youtube_checker_current_post;
		$post = $youtube_checker_current_post;

		$video_id = $attr['video'];

		delete_post_meta( $post->ID, common::LAST_CHECK_TIME_KEY );
		add_post_meta( $post->ID, common::ALL_VIDEOS_IDS_KEY, $video_id );
		$now = new \DateTime( 'now' );
		add_post_meta( $post->ID, common::LAST_CHECK_TIME_KEY, $now->format( 'Y-m-d H:i:s' ) );
	}
}
