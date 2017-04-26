<?php

namespace PetrovEgor\YoutubePlugins;
use PetrovEgor\common;
use PetrovEgor\Logger;

/**
 * Class that add support Youtube plugin
 * https://ru.wordpress.org/plugins/youtube-embed-plus/
 *
 * @package PetrovEgor\YouTubePlugins
 */
class Youtube extends Youtube_Plugin_Abstract {
	public static $instance;

	protected $tag_name = 'embedyt';

	public static function class_name() {
		return 'PetrovEgor\YoutubePlugins\Youtube';
	}

	public function short_code_handler( $attr, $content = '' ) {
		global $youtube_checker_current_post;
		$post = $youtube_checker_current_post;
		$content = common::get_id( $content );

		Logger::info( 'short_code_handler execute for post: ' . $post->ID );

		delete_post_meta( $post->ID, common::LAST_CHECK_TIME_KEY );
		add_post_meta( $post->ID, common::ALL_VIDEOS_IDS_KEY, $content );
		$now = new \DateTime( 'now' );
		add_post_meta( $post->ID, common::LAST_CHECK_TIME_KEY, $now->format( 'Y-m-d H:i:s' ) );
	}
}
