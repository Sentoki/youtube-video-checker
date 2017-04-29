<?php
/**
 * Checker youtube videos
 *
 * @package CheckerYoutubeVideos
 * @author Egor Petrov
 * @license GPL-2.0+
 *
 * @wordpress-plugin
 * Plugin Name: Checker youtube videos
 * Plugin URI: https://github.com/Sentoki/youtube-video-checker
 * Description: This plugin finds youtube video ids in posts, pages and WooCommerce products and send request to youtube API that check - does this videos still available. If video became unavailable, plugin show notifications about that.
 * Version: 1.0.1
 * Author: Egor Petrov
 * Author URI: http://hayate.ru
 * Text Domain: checker-youtube-videos
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */

require_once 'autoload.php';

use PetrovEgor\ContentSources\Content_Source_Abstract;
use PetrovEgor\Database;
use PetrovEgor\Logger;
use PetrovEgor\Common;
use PetrovEgor\Cron;
use PetrovEgor\Pagination;
use PetrovEgor\YoutubePlugins\Youtube_Plugin_Abstract;

/*
 * Adding new actions
 */
add_action( Common::SEARCH_VIDEOS_IN_POSTS_HOOK, 'search_videos_in_post' );
add_action( Common::CHECK_BY_API_HOOK, 'checkByApi' );
add_action( 'admin_notices', array( 'PetrovEgor\Common', 'notify_if_not_configured' ) );

/*
 * Cron actions and filters
 */
add_action( 'youtube-checker-cron', array( 'PetrovEgor\Cron', 'cron' ) );

/*
 * When post or page or WooCommerce product deleted, need delete record about videos
 */
add_action( 'delete_post', array( 'PetrovEgor\Database', 'delete_video_record_for_post' ) );

register_activation_hook( __FILE__, array( 'PetrovEgor\Database', 'update_schema' ) );

/*
 * Filters
 */
add_filter( 'plugin_row_meta', array( 'PetrovEgor\Common', 'donate_link' ), 10, 2 );

$check_freq = get_option( common::SETTINGS_CHECK_FREQ );
if ( isset( $check_freq ) ) {
	Logger::info( 'checkFreq set: ' . $check_freq );
	$next_scheduled = wp_next_scheduled( 'youtube-checker-cron' );
	if ( ! $next_scheduled ) {
		Logger::info( 'not scheduled' );
		wp_schedule_event( time(), $check_freq, 'youtube-checker-cron' );
	} else {
		$next_scheduled = new DateTime( '@' . $next_scheduled );
		Logger::info( 'scheduled: ' . $next_scheduled->format( 'Y-m-d H:i:s' ) );
	}
} else {
	Logger::info( 'checkFreq NOT set' );
}

$menu_index = function() {
	add_menu_page(
		'Youtube checker',
		'Youtube checker',
		'manage_options',
		'youtube-checker',
		array( 'PetrovEgor\Pages', 'index_page' ),
		'dashicons-yes'
	);
	$available_label_counter = common::get_available_video_label_counter_html();
	add_submenu_page(
		'youtube-checker',
		'Available videos',
		'Available videos' . $available_label_counter,
		'manage_options',
		'youtube-checker-all-videos',
		array( 'PetrovEgor\Pages', 'all_videos' )
	);
	$unavailable_label_counter = common::get_unavailable_video_label_counter_html();
	add_submenu_page(
		'youtube-checker',
		'Unavailable videos',
		'Unavailable videos' . $unavailable_label_counter,
		'manage_options',
		'youtube-checker-unavailable-videos',
		array( 'PetrovEgor\Pages', 'unavailable_videos' )
	);
	add_submenu_page(
		'youtube-checker',
		'Settings',
		'Settings',
		'manage_options',
		'youtube-checker-settings',
		array( 'PetrovEgor\Pages', 'settings' )
	);
};

add_action( 'admin_menu', $menu_index );

function search_videos_in_post( $attr ) {
	/** @var Content_Source_Abstract $content_source */
	foreach ( Common::$supported_content_sources as $content_source ) {
		/** @var Content_Source_Abstract $source */
		$source = $content_source::get_instance();
		$objects = $source->get_all_objects();
		foreach ( $objects as $object ) {
			if ( $source->is_need_check_source( $object ) ) {
				/** @var Youtube_Plugin_Abstract $supported_plugin */
				foreach ( common::$supported_plugins as $supported_plugin ) {
					/** @var Youtube_Plugin_Abstract $plugin */
					$plugin = $supported_plugin::get_instance();
					if ( $plugin->has_shortcode( $object ) ) {
						$plugin->save_youtube_ids( $object );
					}
				}
			}
			common::update_last_check_time( $object );
		}
	}
}

function checkByApi( $attr ) {
	try {
		$sources = array();
		/** @var Content_Source_Abstract $content_source */
		foreach ( common::$supported_content_sources as $content_source ) {
			/** @var Content_Source_Abstract $source */
			$source = $content_source::get_instance();
			$sources = array_merge( $sources, $source->get_all_objects() );
		}
		/** @var WP_Post $post */
		foreach ( $sources as $post ) {
			$is_have_unavailable_video = false;
			$is_have_available_video = false;
			\PetrovEgor\Database::unmark_unavailable_video( $post );
			\PetrovEgor\Database::unmark_available_video( $post );
			$ids = common::get_youtube_ids_by_post( $post );
			Common::reset_unavailable_video_list_for_post( $post );
			Common::reset_available_video_list_for_post( $post );
			foreach ( $ids as $id ) {
				if ( ! Common::is_video_available( $id ) ) {
					$is_have_unavailable_video = true;
					Common::report_video_unavailable( $post, $id );
				} else {
					$is_have_available_video = true;
					Common::report_video_available( $post, $id );
				}
			}
			if ( $is_have_unavailable_video ) {
				\PetrovEgor\Database::mark_unavailable_video( $post );
			}
			if ( $is_have_available_video ) {
				\PetrovEgor\Database::mark_available_video( $post );
			}
		}
	} catch ( \Exception $exception ) {
		Logger::info( $exception->getMessage(), 'errors.log' );
		throw $exception;
	}
	Database::save_check_time();
}
