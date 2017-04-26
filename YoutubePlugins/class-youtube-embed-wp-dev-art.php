<?php

namespace PetrovEgor\YoutubePlugins;
use PetrovEgor\common;
use PetrovEgor\Logger;

/**
 * Class that add support plugin:
 * https://ru.wordpress.org/plugins/youtube-video-player/
 * @package PetrovEgor\YouTubePlugins
 */
class Youtube_Embed_Wp_Dev_Art extends Youtube_Plugin_Abstract {
	public static $instance;

	protected $tag_name = 'wpdevart_youtube';

	public static function class_name() {
		return 'PetrovEgor\YoutubePlugins\Youtube_Embed_Wp_Dev_Art';
	}

	public function short_code_handler( $attr, $content = '' ) {
		global $youtube_checker_current_post;
		$post = $youtube_checker_current_post;

		Logger::info( 'myShortcodeHandler execute for post: ' . $post->ID );

		delete_post_meta( $post->ID, common::LAST_CHECK_TIME_KEY );
		add_post_meta( $post->ID, common::ALL_VIDEOS_IDS_KEY, $content );
	}
}
