<?php

namespace PetrovEgor;

class Cron {
	public function cron() {
		$begin = microtime( true );
		Logger::info( 'run cron task' );
		do_action( Common::SEARCH_VIDEOS_IN_POSTS_HOOK );
		do_action( Common::CHECK_BY_API_HOOK );
		$end = microtime( true );
		$time_spend = $end - $begin;
		Logger::info( 'time spend: ' . $time_spend );
	}
}
