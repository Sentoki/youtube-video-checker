<?php

namespace PetrovEgor;

use PetrovEgor\YoutubePlugins\YouTube;
use PetrovEgor\YoutubePlugins\YouTubeEmbedWpDevArt;

class Common {

    const ALL_VIDEOS_IDS_KEY = 'youtube-checker-meta-key';
    const UNAVAILABLE_IDS_KEY = 'unavailable-youtube-checker-meta-key';
    const LAST_CHECK_TIME_KEY = 'youtube-checker-meta-time';

    const SETTINGS_API_KEY = 'youtube-checker-api-key';
    const SETTINGS_CHECK_FREQ = 'youtube-checker-check-freq';

    public static $supportedPlugins = [
        //YouTube::class,
        YouTubeEmbedWpDevArt::class,
    ];

    /**
     * @param \WP_Post $post
     * @return \DateTime|null
     */
    public static function getPostLastCheckTime($post)
    {
        $postLastCheckTime = get_post_meta($post->ID, self::LAST_CHECK_TIME_KEY);
        if (sizeof($postLastCheckTime) > 0) {
            Logger::info('post  ' . $post->ID . ', postLastCheckTime: ' . $postLastCheckTime[0]);
            return new \DateTime($postLastCheckTime[0]);
        } else {
            Logger::info('post  ' . $post->ID . ', no postLastCheckTime');
        }
        return null;
    }

    /**
     * @param \WP_Post $post
     * @return \DateTime
     */
    public static function getPostLastUpdateTime($post)
    {
        Logger::info('post  ' . $post->ID . ', postLastUpdateTime: ' . $post->post_modified_gmt);
        return new \DateTime($post->post_modified_gmt);
    }

    /**
     * @param \WP_Post $post
     * @return array
     */
    public static function getYoutubeIdsByPost($post)
    {
        return get_post_meta($post->ID, self::ALL_VIDEOS_IDS_KEY);
    }

    /**
     * @param string $id
     * @param string $apiKey
     * @return bool
     */
    public static function isVideoAvailable($id, $apiKey = null)
    {
        $url = 'https://www.googleapis.com/youtube/v3/videos?';
        $apiKey = isset($apiKey) ? $apiKey : get_option(Common::SETTINGS_API_KEY);
        if (!isset($apiKey)) {
            return false;
        }
        $params = [
            'id' => $id,
            'key' => $apiKey,
            'part' => 'status',
        ];
        $url .= http_build_query($params);
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_URL, $url);
        $out = curl_exec($curl);
        $error = curl_error($curl);
        curl_close($curl);
        if (strlen($error) > 0) {
            Logger::info('curl error:  ' . $error);
        }
        $result = json_decode($out, true);
        if (isset($result['pageInfo']) && isset($result['pageInfo']['totalResults'])) {
            if ($result['pageInfo']['totalResults'] > 0) {
                Logger::info('ok video: ' . $id);
                return true;
            } else {
                Logger::info('problem video: ' . $id);
                return false;
            }
        }
    }

    /**
     * @param \WP_Post $post
     */
    public static function resetUnavailableVideoListForPost($post)
    {
        delete_post_meta($post->ID, self::UNAVAILABLE_IDS_KEY);
    }

    /**
     * @param \WP_Post $post
     * @param string $videoId
     */
    public static function reportVideoUnavailable($post, $videoId)
    {
        add_post_meta($post->ID, self::UNAVAILABLE_IDS_KEY, $videoId);
    }

    /**
     * @param \WP_Post $post
     * @return array
     */
    public static function getUnavailableVideoList($post)
    {
        return get_post_meta($post->ID, self::UNAVAILABLE_IDS_KEY);
    }

    public static function getUnavailableVideoLabelCounter()
    {
        $counter = 0;
        $posts = Database::getAllPostsWithUnavailableVideos();
        foreach ($posts as $post) {
            $wpPost = get_post($post['post_id']);
            $ids = self::getUnavailableVideoList($wpPost);
            $counter += sizeof($ids);
        }
        $label = "<span class='update-plugins count-$counter' title='Unavailable Videos'><span class='update-count'>$counter</span></span>";
        return $label;
    }

    public static function checkExtensions()
    {
        $requiredExtensions = ['curl'];
        foreach ($requiredExtensions as $extension) {
            extension_loaded($extension);
        }
    }

    /**
     * @param \WP_Post $post
     * @return boolean
     */
    public static function isNeedCheckPost($post)
    {
        $postLastCheckTime = Common::getPostLastCheckTime($post);
        $postLastUpdatetime = Common::getPostLastUpdateTime($post);
        if (!isset($postLastCheckTime) || $postLastUpdatetime > $postLastCheckTime) {
            return true;
        } else {
            Logger::info('post  ' . $post->ID . ', no changes');
            return false;
        }
    }

    /**
     * @param \WP_Post $post
     */
    public static function updateLastCheckTime($post)
    {
        $now = new \DateTime('now');
        add_post_meta(
            $post->ID,
            Common::LAST_CHECK_TIME_KEY,
            $now->format('Y-m-d H:i:s')
        );
    }

    public static function getCurrentUrlWithoutPagination()
    {
        $uri = preg_replace("&pagination=2", '', $_SERVER['REQUEST_URI']);
        return admin_url('admin.php') . $uri;
    }
}
