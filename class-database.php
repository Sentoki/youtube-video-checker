<?php

namespace PetrovEgor;

class Database {
	private $prefix;
	private $wpdb;

	public function __construct() {
		global $wpdb;
		$this->prefix = $wpdb->prefix;
		$this->wpdb = $wpdb;
	}

	public static function update_schema() {
		global $wpdb;
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$sql = "CREATE TABLE {$wpdb->prefix}posts_with_videos (
id INTEGER NOT NULL AUTO_INCREMENT PRIMARY KEY,
post_id INTEGER NOT NULL UNIQUE,
has_availiable BIT(1),
has_unavailiable BIT(1),
check_at TIMESTAMP);
CREATE TABLE {$wpdb->prefix}youtube_check_history (
  id INTEGER NOT NULL AUTO_INCREMENT PRIMARY KEY,
  check_at TIMESTAMP);
);";
		dbDelta( $sql );
	}

	/**
	 * @param \WP_Post $post
	 */
	public static function mark_unavailable_video( $post ) {
		global $wpdb;
		$time = new \DateTime( 'now' );
		$time = $time->format( 'Y-m-d H:i:s' );

		Logger::info( 'mark unavailable for : ' . $post->ID . ', prefix: ' . $wpdb->prefix );
		$wpdb->query(
			$wpdb->prepare("insert into {$wpdb->prefix}posts_with_videos (post_id, has_unavailiable, check_at)
				  VALUES (%d, TRUE, %s) ON DUPLICATE KEY UPDATE has_unavailiable=TRUE, check_at=%s",
				array( $post->ID, $time, $time )
			)
		);
	}

	/**
	 * @param \WP_Post $post
	 */
	public static function mark_available_video( $post ) {
		global $wpdb;
		$time = new \DateTime( 'now' );
		$time = $time->format( 'Y-m-d H:i:s' );

		Logger::info( 'mark available for : ' . $post->ID . ', prefix: ' . $wpdb->prefix );
		$wpdb->query(
			$wpdb->prepare("insert into {$wpdb->prefix}posts_with_videos (post_id, has_availiable, check_at)
				  VALUES (%d, TRUE, %s) ON DUPLICATE KEY UPDATE has_availiable=TRUE, check_at=%s",
				array( $post->ID, $time, $time )
			)
		);
	}

	/**
	 * @param \WP_Post $post
	 */
	public static function unmark_available_video( $post ) {
		global $wpdb;
		$wpdb->query( $wpdb->prepare(
			"update {$wpdb->prefix} posts_with_videos set has_availiable=FALSE WHERE post_id=%d",
			array( $post->ID )
		) );
	}

	/**
	 * @param \WP_Post $post
	 */
	public static function unmark_unavailable_video( $post ) {
		global $wpdb;
		$wpdb->query(
			$wpdb->prepare(
				"update {$wpdb->prefix}posts_with_videos set has_unavailiable=FALSE WHERE post_id=%d",
				array( $post->ID )
			)
		);
	}

	public static function get_all_posts_with_available_videos() {
		global $wpdb;
		$posts = $wpdb->get_results(
			$wpdb->prepare(
				"select * from {$wpdb->prefix}posts_with_videos WHERE has_availiable is TRUE ORDER BY id",
				array()
			),
			ARRAY_A
		);
		return $posts;
	}

	public static function get_posts_with_available_videos() {
		global $wpdb;
		$videos_per_page = 20;
		$pagination = $_GET['pagination'];
		if ( isset( $pagination ) ) {
			$offset = ($pagination - 1) * $videos_per_page;
		} else {
			$offset = 0;
		}
		$posts = $wpdb->get_results(
			$wpdb->prepare(
				"select * from {$wpdb->prefix}posts_with_videos WHERE has_availiable is TRUE ORDER BY id limit %d,%d",
				array( $offset, $videos_per_page )
			),
			ARRAY_A
		);
		return $posts;
	}

	public static function get_all_posts_with_unavailable_videos() {
		global $wpdb;
		$posts = $wpdb->get_results(
			$wpdb->prepare(
				"select * from {$wpdb->prefix}posts_with_videos WHERE has_unavailiable IS TRUE ORDER BY id",
				array()
			),
			ARRAY_A
		);
		return $posts;
	}

	public static function get_posts_with_unavailable_videos() {
		global $wpdb;
		$videos_per_page = 20;
		$pagination = $_GET['pagination'];
		if ( isset( $pagination ) ) {
			$offset = ($pagination - 1) * $videos_per_page;
		} else {
			$offset = 0;
		}
		$posts = $wpdb->get_results(
			$wpdb->prepare(
				"select * from {$wpdb->prefix}posts_with_videos WHERE has_unavailiable IS TRUE ORDER BY id limit %d,%d",
				array( $offset, $videos_per_page )
			),
			ARRAY_A
		);
		return $posts;
	}

	public static function delete_video_record_for_post( $post_id ) {
		global $wpdb;
		try {
			$wpdb->delete(
				"{$wpdb->prefix}posts_with_videos",
				array(
					'post_id' => $post_id,
				)
			);
		} catch ( \Exception $exception ) {
			$break = 1;
		}
	}

	public static function save_check_time() {
		global $wpdb;
		$now = new \DateTime( 'now' );
		$wpdb->query(
			$wpdb->prepare(
				"insert into {$wpdb->prefix}youtube_check_history (check_at) VALUES (%s)",
				array( $now->format( 'Y-m-d H:i:s' ) )
			)
		);
	}

	public static function get_interval_string( \DateTime $time_from_now ) {
		if ( null !== $time_from_now ) {
			$now = new \DateTime( current_time( 'mysql' ) );
			$diff = $now->diff( $time_from_now );
			$diff_string = '';
			if ( 0 !== (int) $diff->y ) {
				$diff_string .= "{$diff->y} year, ";
			}
			if ( 0 !== (int) $diff->m ) {
				$diff_string .= "{$diff->m} month, ";
			}
			if ( 0 !== (int) $diff->d ) {
				$diff_string .= "{$diff->d} day, ";
			}
			if ( 0 !== (int) $diff->h ) {
				$diff_string .= "{$diff->h} hour, ";
			}
			if ( 0 !== (int) $diff->i ) {
				$diff_string .= "{$diff->i} minutes, ";
			}
			if ( 0 !== (int) $diff->s ) {
				$diff_string .= "{$diff->s} seconds ";
			}
		} else {
			$diff_string = null;
		}
		return $diff_string;
	}

	public static function get_last_check_time() {
		global $wpdb;
		$last_check_time = $wpdb->get_row(
			$wpdb->prepare(
				"select * from {$wpdb->prefix}youtube_check_history ORDER BY id DESC",
				array()
			)
		);
		if ( null !== $last_check_time ) {
			$last_check_time = new \DateTime( $last_check_time->check_at );
			$diff_string = self::get_diff_string( $last_check_time );
			$diff_string .= 'ago';
		} else {
			$diff_string = 'no checks was made';
		}

		return $diff_string;
	}

	public static function get_next_check_time() {
		$next_scheduled = \DateTime::createFromFormat(
			'U',
			wp_next_scheduled( 'youtube-checker-cron' )
		);
		if ( $next_scheduled ) {
			$diff_string = self::get_diff_string( $next_scheduled );
			return 'In ' . $diff_string;
		} else {
			return null;
		}
	}

	private static function get_diff_string( $target_time ) {
		$now = new \DateTime( 'now' );
		$diff = $now->diff( $target_time );
		$diff_string = '';
		if ( 0 !== (int) $diff->y ) {
			$diff_string .= "{$diff->y} year, ";
		}
		if ( 0 !== (int) $diff->m ) {
			$diff_string .= "{$diff->m} month, ";
		}
		if ( 0 !== (int) $diff->d ) {
			$diff_string .= "{$diff->d} day, ";
		}
		if ( 0 !== (int) $diff->h ) {
			$diff_string .= "{$diff->h} hour, ";
		}
		if ( 0 !== (int) $diff->i ) {
			$diff_string .= "{$diff->i} minutes, ";
		}
		if ( 0 !== (int) $diff->s ) {
			$diff_string .= "{$diff->s} seconds ";
		}
		return $diff_string;
	}
}
