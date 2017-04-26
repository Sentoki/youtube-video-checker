<?php

namespace PetrovEgor;

abstract class Singleton_Abstract {
	public static $instance;

	protected function __construct() {
	}

	public static function get_instance() {
		if ( ! isset( static::$instance ) ) {
			static::$instance = new static();
		}
		return static::$instance;
	}
}
