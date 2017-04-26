<?php

namespace PetrovEgor\YoutubePlugins;

use PetrovEgor\common;
use PetrovEgor\Logger;
use PetrovEgor\Singleton_Abstract;

abstract class Youtube_Plugin_Abstract extends Singleton_Abstract {
	protected $tag_name;
	protected $short_code_method = 'short_code_handler';

	public static function class_name() {
		return 'PetrovEgor\YoutubePlugins\Youtube_Plugin_Abstract';
	}

	/**
	 * @param \WP_Post $post
	 * @return bool
	 */
	public function has_shortcode( $post ) {
		add_shortcode(
			$this->tag_name,
			array( static::class_name(), $this->short_code_method )
		);
		$has_shortcode = has_shortcode( $post->post_content, $this->tag_name );
		if ( $has_shortcode ) {
			Logger::info( 'post  ' . $post->ID . ', has shortcode ' . $this->tag_name );
			return true;
		}
		Logger::info( 'post  ' . $post->ID . ', no shortcode ' . $this->tag_name );
		return false;
	}

	/**
	 * @param \WP_Post $post
	 */
	public function save_youtube_ids( $post ) {
		global $youtube_checker_current_post;
		Logger::info( 'post  ' . $post->ID . ', saving youtube ids' );

		/**
		 * Clear old data about post
		 */
		delete_post_meta( $post->ID, common::ALL_VIDEOS_IDS_KEY );
		delete_post_meta( $post->ID, common::LAST_CHECK_TIME_KEY );

		$youtube_checker_current_post = $post;

		add_shortcode(
			$this->tag_name,
			array( static::class_name(), $this->short_code_method )
		);

		do_shortcode( $post->post_content );
	}

	abstract public function short_code_handler( $attr, $content = '' );

	public function get_last_check_time_meta_key() {
		return $this->tag_name . '_last_check_time';
	}
}
