<?php

namespace PetrovEgor\ContentSources;

use PetrovEgor\common;
use PetrovEgor\Singleton_Abstract;
use PetrovEgor\YoutubePlugins\Youtube_Plugin_Abstract;

abstract class Content_Source_Abstract extends Singleton_Abstract {
	abstract function get_all_objects();

	public function is_need_check_source( $source ) {
		return common::is_need_check_post( $source );
	}
}
