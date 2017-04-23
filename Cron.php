<?php

namespace PetrovEgor;

class Cron
{
    public function everyTenSecondsInterval($schedules){
        $schedules['ten_seconds'] = array(
            'interval' => 60*60*24,
            'display' => esc_html__('Ten seconds'),
        );

        return $schedules;
    }

    public function cron()
    {
        $begin = microtime(true);
        Logger::info('run cron task');
        do_action('search-videos-in-posts');
        do_action('check-by-api');
        $end = microtime(true);
        $timeSpend = $end - $begin;
        Logger::info('time spend: ' . $timeSpend);
    }
}
