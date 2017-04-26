<?php

namespace PetrovEgor\ContentSources;

class Woo_Commerce extends Content_Source_Abstract {
	public static $instance;

	public function get_all_objects() {
		$products = get_posts(array(
			'post_type' => 'product',
		));
		return $products;
	}
}
