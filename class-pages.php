<?php

namespace PetrovEgor;

class Pages {
	public static function index_page() {
		switch ($_GET['youtube_checker_action']) {
			case 'search-videos-in-posts':
				do_action( Common::SEARCH_VIDEOS_IN_POSTS_HOOK );
				break;
			case 'check-by-api':
				do_action( Common::CHECK_BY_API_HOOK );
				break;
		}
		$template = \PetrovEgor\templates\Template::get_instance();
		$template->set_template( 'index-page.php' );
		$last_check = Database::get_last_check_time();
		$next_scheduled = Database::get_next_check_time();
		$available_counter = common::get_available_video_label_counter();
		$unavailable_counter = common::get_unavailable_video_label_counter();

		$template->set_params(array(
			'lastCheck' => $last_check,
			'nextScheduled' => $next_scheduled,
			'availableCounter' => $available_counter,
			'unavailableCounter' => $unavailable_counter,
		));
		$template->render();

	}

	public static function all_videos() {
		$posts = \PetrovEgor\Database::get_posts_with_available_videos();
		$template = \PetrovEgor\templates\Template::get_instance();
		$template->set_template( 'available-videos.php' );
		$pages_number = Pagination::get_available_pages_number();
		$current_page = Pagination::get_current_page();
		$pagination_links = paginate_links(
			array(
				'base' => add_query_arg( 'pagination', '%#%' ),
				'format' => '',
				'prev_text' => __( '« Previous' ),
				'next_text' => __( 'Next »' ),
				'total' => $pages_number,
				'current' => $current_page,
				)
		);
		$template->set_params(array(
			'posts' => $posts,
			'pagesNumber' => $pages_number,
			'currentPage' => $current_page,
			'paginationLinks' => $pagination_links,
		));
		$template->render();
	}

	public static function unavailable_videos() {
		$posts = \PetrovEgor\Database::get_posts_with_unavailable_videos();
		$template = \PetrovEgor\templates\Template::get_instance();
		$template->set_template( 'unavailable-videos.php' );
		$pages_number = Pagination::get_unavailable_pages_number();
		$current_page = Pagination::get_current_page();
		$pagination_links = paginate_links(
			array(
				'base' => add_query_arg( 'pagination', '%#%' ),
				'format' => '',
				'prev_text' => __( '« Previous' ),
				'next_text' => __( 'Next »' ),
				'total' => $pages_number,
				'current' => $current_page,
			)
		);
		$template->set_params(array(
			'posts' => $posts,
			'pagesNumber' => $pages_number,
			'currentPage' => $current_page,
			'paginationLinks' => $pagination_links,
		));
		$template->render();
	}

	public static function settings() {
		$template = \PetrovEgor\templates\Template::get_instance();
		$template->set_template( 'settings.php' );
		$params = array();

		if (
			! empty( $_POST ) &&
			wp_verify_nonce( $_POST['_wpnonce'] ) === 1 &&
			isset( $_POST['api_key'] ) &&
			isset( $_POST['sync_frequency'] )
		) {
			$api_key = sanitize_text_field( strval( $_POST['api_key'] ) );
			$sync_frequency = sanitize_text_field( strval( $_POST['sync_frequency'] ) );
			if ( ! common::is_video_available( 'jNQXAC9IVRw', $api_key ) ) {
				$params['is_wrong_api_key'] = true;
				//wrong api key
			} else {
				delete_option( common::SETTINGS_API_KEY );
				add_option( common::SETTINGS_API_KEY, $api_key );
				delete_option( common::SETTINGS_CHECK_FREQ );
				add_option( common::SETTINGS_CHECK_FREQ, $sync_frequency );

				wp_unschedule_event( time(), $sync_frequency, 'youtube-checker-cron' );
				wp_schedule_event( time(), $sync_frequency, 'youtube-checker-cron' );
				echo "<meta http-equiv='refresh' content='0'>";
			}
		}
		$api_key = get_option( common::SETTINGS_API_KEY );
		$check_freq = get_option( common::SETTINGS_CHECK_FREQ );
		if ( isset( $api_key ) && isset( $check_freq ) ) {
			$params['apiKey'] = $api_key;
			$params['checkFreq'] = $check_freq;
			$template->set_params( $params );
		}
		$template->render();
	}
}
