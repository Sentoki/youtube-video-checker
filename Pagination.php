<?php

namespace PetrovEgor;

class Pagination
{
    public static function getPagesNumber()
    {
        global $wpdb;
        $videosPerPage = 20;
        $tablename = $wpdb->prefix.'posts_with_unavailable_videos';
        $count = $wpdb->get_row(
            "select count(id) from $tablename",
            ARRAY_N
        );
        return ceil($count[0]/$videosPerPage);
    }

    public static function getCurrentPage()
    {
        return isset($_GET['pagination']) ? $_GET['pagination'] : 1;
    }

    public static function getVideosForPage()
    {


    }
}
