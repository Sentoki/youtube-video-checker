<?php

namespace PetrovEgor;

use PetrovEgor\ContentSources\Page;
use PetrovEgor\ContentSources\Woo_Commerce;
use PetrovEgor\templates\Template;
use PetrovEgor\YoutubePlugins\Youtube;
use PetrovEgor\YoutubePlugins\Youtube_Embed_Wp_Dev_Art;
use PetrovEgor\ContentSources\Post;
use PetrovEgor\YoutubePlugins\Youtube_Plugin_Abstract;
use PetrovEgor\YoutubePlugins\Youtube_Widget_Responsive;

class Common {

	const ALL_VIDEOS_IDS_KEY = 'youtube-checker-meta-key';
	const AVAILABLE_IDS_KEY = 'available-youtube-checker-meta-key';
	const UNAVAILABLE_IDS_KEY = 'unavailable-youtube-checker-meta-key';
	const LAST_CHECK_TIME_KEY = 'youtube-checker-meta-time';

	const SETTINGS_API_KEY = 'youtube-checker-api-key';
	const SETTINGS_CHECK_FREQ = 'youtube-checker-check-freq';

	/*
	 * Hooks
	 */
	const SEARCH_VIDEOS_IN_POSTS_HOOK = 'search_videos_in_posts';
	const CHECK_BY_API_HOOK = 'check_by_api';

	public static $supported_plugins = array(
		'PetrovEgor\YoutubePlugins\Youtube',
		'PetrovEgor\YoutubePlugins\Youtube_Embed_Wp_Dev_Art',
		'PetrovEgor\YoutubePlugins\Youtube_Widget_Responsive',
	);

	public static $supported_content_sources = array(
		'PetrovEgor\ContentSources\Post',
		'PetrovEgor\ContentSources\Page',
		'PetrovEgor\ContentSources\Woo_Commerce',
	);

	/**
	 * @param \WP_Post $post
	 * @return \DateTime|null
	 */
	public static function get_post_last_check_time( $post ) {
		$post_last_check_time = get_post_meta( $post->ID, self::LAST_CHECK_TIME_KEY );
		if ( count( $post_last_check_time ) > 0 ) {
			Logger::info( 'post  ' . $post->ID . ', postLastCheckTime: ' . $post_last_check_time[0] );
			return new \DateTime( $post_last_check_time[0] );
		} else {
			Logger::info( 'post  ' . $post->ID . ', no postLastCheckTime' );
		}
		return null;
	}

	/**
	 * @param \WP_Post $post
	 * @return \DateTime
	 */
	public static function get_post_last_update_time( $post ) {
		Logger::info( 'post  ' . $post->ID . ', postLastUpdateTime: ' . $post->post_modified_gmt );
		return new \DateTime( $post->post_modified_gmt );
	}

	/**
	 * @param \WP_Post $post
	 * @return array
	 */
	public static function get_youtube_ids_by_post( $post ) {
		return get_post_meta( $post->ID, self::ALL_VIDEOS_IDS_KEY );
	}

	/**
	 * @param string $id
	 * @param string $api_key
	 * @return bool
	 */
	public static function is_video_available( $id, $api_key = null ) {
		$url = 'https://www.googleapis.com/youtube/v3/videos?';
		$api_key = isset( $api_key ) ? $api_key : get_option( common::SETTINGS_API_KEY );
		if ( ! isset( $api_key ) ) {
			return false;
		}
		$params = array(
			'id' => $id,
			'key' => $api_key,
			'part' => 'status',
		);
		$url .= http_build_query( $params );
		$answer = wp_remote_get( $url );
		$result = json_decode( $answer['body'], true );
		if ( isset( $result['pageInfo'] ) && isset( $result['pageInfo']['totalResults'] ) ) {
			if ( $result['pageInfo']['totalResults'] > 0 ) {
				Logger::info( 'ok video: ' . $id );
				return true;
			} else {
				Logger::info( 'problem video: ' . $id );
				return false;
			}
		}
	}

	/**
	 * @param \WP_Post $post
	 */
	public static function reset_unavailable_video_list_for_post( $post ) {
		delete_post_meta( $post->ID, self::UNAVAILABLE_IDS_KEY );
	}

	/**
	 * @param \WP_Post $post
	 */
	public static function reset_available_video_list_for_post( $post ) {
		delete_post_meta( $post->ID, self::AVAILABLE_IDS_KEY );
	}

	/**
	 * @param \WP_Post $post
	 * @param string $video_id
	 */
	public static function report_video_unavailable( $post, $video_id ) {
		add_post_meta( $post->ID, self::UNAVAILABLE_IDS_KEY, $video_id );
	}

	/**
	 * @param \WP_Post $post
	 * @param string $video_id
	 */
	public static function report_video_available( $post, $video_id ) {
		add_post_meta( $post->ID, self::AVAILABLE_IDS_KEY, $video_id );
	}

	/**
	 * @param \WP_Post $post
	 * @return array
	 */
	public static function get_unavailable_video_list( $post ) {
		return get_post_meta( $post->ID, self::UNAVAILABLE_IDS_KEY );
	}

	/**
	 * @param \WP_Post $post
	 * @return array
	 */
	public static function get_available_video_list( $post ) {
		return get_post_meta( $post->ID, self::AVAILABLE_IDS_KEY );
	}

	public static function get_unavailable_video_label_counter() {
		$counter = 0;
		$posts = Database::get_all_posts_with_unavailable_videos();
		foreach ( $posts as $post ) {
			$wp_post = get_post( $post['post_id'] );
			$ids = self::get_unavailable_video_list( $wp_post );
			$counter += count( $ids );
		}
		return $counter;
	}

	public static function get_unavailable_video_label_counter_html() {
		$counter = self::get_unavailable_video_label_counter();
		$label = "<span class='update-plugins count-$counter' title='Unavailable Videos'><span class='update-count'>$counter</span></span>";
		return $label;
	}

	public static function get_available_video_label_counter() {
		$counter = 0;
		$posts = Database::get_all_posts_with_available_videos();
		foreach ( $posts as $post ) {
			$wp_post = get_post( $post['post_id'] );
			$ids = self::get_available_video_list( $wp_post );
			$counter += count( $ids );
		}
		return $counter;
	}

	public static function get_available_video_label_counter_html() {
		$counter = self::get_available_video_label_counter();
		$label = "<span class='update-plugins count-$counter' style='background-color: #2ea2cc;' title='Unavailable Videos'><span class='update-count'>$counter</span></span>";
		return $label;
	}

	/**
	 * @param \WP_Post $source
	 * @return boolean
	 */
	public static function is_need_check_post( $source ) {
		$post_last_check_time = get_post_meta( $source->ID, common::LAST_CHECK_TIME_KEY );
		if ( count( $post_last_check_time ) > 0 ) {
			$post_last_check_time = new \DateTime( $post_last_check_time[0] );
		}
		$post_last_update_time = new \DateTime( $source->post_modified_gmt );

		if ( ! isset( $post_last_check_time ) || $post_last_update_time > $post_last_check_time ) {
			Logger::info( 'post  ' . $source->ID . ', need update' );
			return true;
		} else {
			Logger::info( 'post  ' . $source->ID . ', no changes' );
			return false;
		}
	}

	/**
	 * @param \WP_Post $post
	 */
	public static function update_last_check_time( $post ) {
		$now = new \DateTime( 'now' );
		add_post_meta(
			$post->ID,
			common::LAST_CHECK_TIME_KEY,
			$now->format( 'Y-m-d H:i:s' )
		);
	}

	public static function get_id( $url ) {
		$url = trim( $url );
		$parsed_url = wp_parse_url( $url );
		if ( isset( $parsed_url['host'] ) ) {
			if ( 'youtu.be' === $parsed_url['host'] ) {
				$id = str_replace( '/', '', $parsed_url['path'] );
			} elseif ( 'www.youtube.com' === $parsed_url['host'] ) {
				$params = array();
				parse_str( $parsed_url['query'], $params );
				if ( isset( $params['v'] ) ) {
					$id = $params['v'];
				} else {
					$id = null;
				}
			} else {
				$id = null;
			}
		} else {
			$id = null;
		}
		return $id;
	}

	public static function is_develop_mode() {
		return file_exists( __DIR__ . '/develop_mode.enable' );
	}

	public static function send_email_notification() {
		$posts = Database::get_posts_with_unavailable_videos( true );
		$email = get_option( 'admin_email' );
		$home_url = get_home_url();
		$template = Template::get_instance();
		$template->set_template( 'UnavailableVideosMail.php' );
		$template->set_params( array(
			'posts' => $posts,
		) );
		$message = $template->render();
		wp_mail( $email, 'Youtube checker, ' . $home_url, $message );
	}

	public static function notify_if_not_configured() {
		$api_key = get_option( common::SETTINGS_API_KEY );
		if ( ! $api_key ) {
			echo '<div class="notice notice-warning">
		<p>
	Youtube checker: Google API key not set.
		</p>
	</div>';
		}
		$check_freq = get_option( common::SETTINGS_CHECK_FREQ );
		if ( ! $check_freq ) {
			echo '<div class="notice notice-warning">
		<p>
	Youtube checker: search and check videos don\'t scheduled. 
		</p>
	</div>';
		}
	}
}
