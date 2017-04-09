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

        $sql = "CREATE TABLE {$wpdb->prefix}posts_with_unavailable_videos (
id INTEGER NOT NULL AUTO_INCREMENT PRIMARY KEY,
post_id INTEGER NOT NULL,
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
        $wpdb->insert(
            $wpdb->prefix.'posts_with_unavailable_videos',
            [
                'post_id' => $post->ID,
                'check_at' => (new \DateTime('now'))->format('Y-m-d H:i:s'),
            ]
        );
    }

    /**
     * @param \WP_Post $post
     */
    public static function unmarkUnavailableVideo($post)
    {
        global $wpdb;
        $wpdb->delete(
            $wpdb->prefix.'posts_with_unavailable_videos',
            [
                'post_id' => $post->ID,
            ]
        );
    }

    public static function getPostsWithUnavailableVideos()
    {
        global $wpdb;
        $tablename = $wpdb->prefix.'posts_with_unavailable_videos';
        $posts = $wpdb->get_results("select * from $tablename", ARRAY_A);
        return $posts;
    }
}