<?php

namespace PetrovEgor;

class Logger {
    public static function info($text, $logName = 'default.log')
    {
        if (file_exists(__DIR__ . '/do_debug_log.enable')) {
            $dateTime = new \DateTime('now');
            $dateTime = $dateTime->format('d-m-Y H:i:s');
            $text = $dateTime . ": " . $text . "\n";
            file_put_contents(__DIR__ . '/logs/' . $logName, $text, FILE_APPEND);
        }
    }
}
