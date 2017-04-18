<?php

namespace PetrovEgor;

class Database {
    private $prefix;
    private $wpdb;

    public function __construct()
    {
        global $wpdb;
        $this->prefix = $wpdb->prefix;
        $this->wpdb = $wpdb;
    }

    public static function updateSchema()
    {
        global $wpdb;
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $sql = "CREATE TABLE {$wpdb->prefix}posts_with_videos (
id INTEGER NOT NULL AUTO_INCREMENT PRIMARY KEY,
post_id INTEGER NOT NULL UNIQUE,
has_availiable BIT(1),
has_unavailiable BIT(1),
check_at TIMESTAMP
);";
        dbDelta($sql);
    }

    /**
     * @param \WP_Post $post
     */
    public static function markUnavailableVideo($post)
    {
        global $wpdb;
        $time = (new \DateTime('now'))->format('Y-m-d H:i:s');
        $table = $wpdb->prefix.'posts_with_videos';
        $wpdb->query(
            "insert into {$table} (post_id, has_unavailiable, check_at)
                  VALUES ({$post->ID}, TRUE, '{$time}') ON DUPLICATE KEY UPDATE has_unavailiable=TRUE, check_at='{$time}'"
        );
    }

    /**
     * @param \WP_Post $post
     */
    public static function markAvailableVideo($post)
    {
        global $wpdb;
        $time = (new \DateTime('now'))->format('Y-m-d H:i:s');
        $table = $wpdb->prefix.'posts_with_videos';
        $wpdb->query(
            "insert into {$table} (post_id, has_availiable, check_at)
                  VALUES ({$post->ID}, TRUE, '{$time}') ON DUPLICATE KEY UPDATE has_availiable=TRUE, check_at='{$time}'"
        );
    }

    /**
     * @param \WP_Post $post
     */
    public static function unmarkAvailableVideo($post)
    {
        global $wpdb;
        $table = $wpdb->prefix.'posts_with_videos';
        $wpdb->query("update {$table} set has_availiable=FALSE WHERE post_id={$post->ID}");
    }

    /**
     * @param \WP_Post $post
     */
    public static function unmarkUnavailableVideo($post)
    {
        global $wpdb;
        $table = $wpdb->prefix.'posts_with_videos';
        $wpdb->query("update {$table} set has_unavailiable=FALSE WHERE post_id={$post->ID}");
    }

    public static function getAllPostsWithAvailableVideos()
    {
        return self::getPostsWithAvailableVideos(true);
    }

    public static function getPostsWithAvailableVideos($isAllPosts = false)
    {
        global $wpdb;
        $videosPerPage = 20;
        if(isset($_GET['pagination'])) {
            $offset = ($_GET['pagination']-1) * $videosPerPage;
        } else {
            $offset = 0;
        }
        if ($isAllPosts === true) {
            $limit = '';
        } else {
            $limit = "limit $offset,$videosPerPage";
        }
        $tablename = $wpdb->prefix.'posts_with_videos';
        $posts = $wpdb->get_results(
            "select * from $tablename WHERE has_availiable is TRUE ORDER BY id $limit",
            ARRAY_A
        );
        return $posts;
    }

    public static function getAllPostsWithUnavailableVideos()
    {
        return self::getPostsWithUnavailableVideos(true);
    }

    public static function getPostsWithUnavailableVideos($isAllPosts = false)
    {
        global $wpdb;
        $videosPerPage = 20;
        if(isset($_GET['pagination'])) {
            $offset = ($_GET['pagination']-1) * $videosPerPage;
        } else {
            $offset = 0;
        }
        if ($isAllPosts === true) {
            $limit = '';
        } else {
            $limit = "limit $offset,$videosPerPage";
        }
        $tablename = $wpdb->prefix.'posts_with_videos';
        $posts = $wpdb->get_results(
            "select * from $tablename WHERE has_unavailiable IS TRUE ORDER BY id $limit",
            ARRAY_A
        );
        return $posts;
    }
}