<?php

namespace PetrovEgor\ContentSources;

class Page extends Content_Source_Abstract {
	public static $instance;

	/**
	 * @return array
	 */
	public function get_all_objects() {
		$pages = get_pages();
		return $pages;
	}
}
