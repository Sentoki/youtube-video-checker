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
check_at TIMESTAMP);
CREATE TABLE {$wpdb->prefix}youtube_check_history (
  id INTEGER NOT NULL AUTO_INCREMENT PRIMARY KEY,
  check_at TIMESTAMP);
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

    public static function deleteVideoRecordForPost($post_id)
    {
        global $wpdb;
        $break = 1;
        try {
            $wpdb->delete(
                "{$wpdb->prefix}posts_with_videos",
                ['post_id' => $post_id]
            );
        } catch (\Exception $exception) {
            $break = 1;
        }
    }

    public static function saveCheckTime()
    {
        global $wpdb;
        $wpdb->query("insert into {$wpdb->prefix}youtube_check_history () VALUES ()");
    }

    public static function getIntervalString(\DateTime $timeFromNow) : ?string
    {
        if ($timeFromNow !== null) {
            $now = new \DateTime(current_time('mysql'));
            $diff = $now->diff($timeFromNow);
            $diffString = '';
            if ($diff->y != 0) {
                $diffString .= "{$diff->y} year, ";
            }
            if ($diff->m != 0) {
                $diffString .= "{$diff->m} month, ";
            }
            if ($diff->d != 0) {
                $diffString .= "{$diff->d} day, ";
            }
            if ($diff->h != 0) {
                $diffString .= "{$diff->h} hour, ";
            }
            if ($diff->i != 0) {
                $diffString .= "{$diff->i} minutes, ";
            }
            if ($diff->s != 0) {
                $diffString .= "{$diff->s} seconds ";
            }
        } else {
            $diffString = null;
        }
        return $diffString;
    }

    public static function getLastCheckTime()
    {
        global $wpdb;
        $lastCheckTime = $wpdb->get_row("select * from {$wpdb->prefix}youtube_check_history ORDER BY id DESC");
        if ($lastCheckTime !== null) {
            $lastCheckTime = new \DateTime($lastCheckTime->check_at);
            $now = new \DateTime(current_time('mysql'));
            $diff = $now->diff($lastCheckTime);
            $diffString = '';
            if ($diff->y != 0) {
                $diffString .= "{$diff->y} year, ";
            }
            if ($diff->m != 0) {
                $diffString .= "{$diff->m} month, ";
            }
            if ($diff->d != 0) {
                $diffString .= "{$diff->d} day, ";
            }
            if ($diff->h != 0) {
                $diffString .= "{$diff->h} hour, ";
            }
            if ($diff->i != 0) {
                $diffString .= "{$diff->i} minutes, ";
            }
            if ($diff->s != 0) {
                $diffString .= "{$diff->s} seconds ";
            }
            $diffString .= "ago";
        } else {
            $diffString = 'no checks was made';
        }

        return $diffString;
    }

    public static function getNextCheckTime()
    {
        $nextScheduled = \DateTime::createFromFormat(
            'U',
            wp_next_scheduled('youtube-checker-cron')
        );
        $gmtOffset = get_option('gmt_offset');
        $nextScheduled->modify("+$gmtOffset hour");
        return $nextScheduled;
    }
}