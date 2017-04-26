<?php

namespace PetrovEgor;

class Pagination {
	public static function get_unavailable_pages_number() {
		global $wpdb;
		$videos_per_page = 20;
		$tablename = $wpdb->prefix . 'posts_with_videos';
		$count = $wpdb->get_row(
			"select count(id) from $tablename WHERE has_unavailiable is TRUE ",
			ARRAY_N
		);
		return ceil( $count[0] / $videos_per_page );
	}

	public static function get_available_pages_number() {
		global $wpdb;
		$videos_per_page = 20;
		$tablename = $wpdb->prefix . 'posts_with_videos';
		$count = $wpdb->get_row(
			"select count(id) from $tablename WHERE has_availiable is TRUE ",
			ARRAY_N
		);
		return ceil( $count[0] / $videos_per_page );
	}

	public static function get_current_page() {
		return isset($_GET['pagination']) ? $_GET['pagination'] : 1;
	}
}
