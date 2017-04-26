<?php

namespace PetrovEgor\templates;

class Template {
	/**
	 * @var array
	 */
	private $params;

	/**
	 * @var string
	 */
	private $template;

	/**
	 * @var self
	 */
	private static $instance;


	private function __construct() {
	}

	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * @param array $params
	 */
	public function set_params( $params ) {
		$this->params = $params;
	}

	/**
	 * @return array
	 */
	public function get_params() {
		return $this->params;
	}

	public function set_template( $template ) {
		$this->template = $template;
	}

	public function get_template() {
		return $this->template;
	}

	public function render() {
		require_once $this->template;
	}
}
