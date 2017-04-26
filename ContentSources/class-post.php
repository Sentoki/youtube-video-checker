<?php

namespace PetrovEgor\ContentSources;

class Post extends Content_Source_Abstract {
	public static $instance;

	/**
	 * @return \WP_Post[]
	 */
	public function get_all_objects() {
		$posts = get_posts(array(
			'numberposts' => -1,
		));
		return $posts;
	}
}
