<?php

namespace PetrovEgor;

class Logger {
	public static function info( $text, $log_name = 'default.log' ) {
	    global $wp_filesystem;
		if ( file_exists( __DIR__ . '/do_debug_log.enable' ) ) {
			$date_time = new \DateTime( 'now' );
			$date_time = $date_time->format( 'd-m-Y H:i:s' );
			$text = $date_time . ': ' . $text . "\n";
			file_put_contents( __DIR__ . '/logs/' . $log_name, $text, FILE_APPEND );
		}
	}
}
