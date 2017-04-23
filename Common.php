<?php

namespace PetrovEgor;

use PetrovEgor\ContentSources\Page;
use PetrovEgor\ContentSources\WooCommerce;
use PetrovEgor\templates\Template;
use PetrovEgor\YoutubePlugins\YouTube;
use PetrovEgor\YoutubePlugins\YouTubeEmbedWpDevArt;
use PetrovEgor\ContentSources\Post;
use PetrovEgor\YoutubePlugins\YoutubePluginAbstract;
use PetrovEgor\YoutubePlugins\YoutubeWidgetResponsive;

class Common {

    const ALL_VIDEOS_IDS_KEY = 'youtube-checker-meta-key';
    const AVAILABLE_IDS_KEY = 'available-youtube-checker-meta-key';
    const UNAVAILABLE_IDS_KEY = 'unavailable-youtube-checker-meta-key';
    const LAST_CHECK_TIME_KEY = 'youtube-checker-meta-time';

    const SETTINGS_API_KEY = 'youtube-checker-api-key';
    const SETTINGS_CHECK_FREQ = 'youtube-checker-check-freq';

    public static $supportedPlugins = [
        YouTube::class,
        YouTubeEmbedWpDevArt::class,
        YoutubeWidgetResponsive::class,
    ];

    public static $supportedContentSources = [
        Post::class,
        Page::class,
        WooCommerce::class,
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
     */
    public static function resetAvailableVideoListForPost($post)
    {
        delete_post_meta($post->ID, self::AVAILABLE_IDS_KEY);
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
     * @param string $videoId
     */
    public static function reportVideoAvailable($post, $videoId)
    {
        add_post_meta($post->ID, self::AVAILABLE_IDS_KEY, $videoId);
    }

    /**
     * @param \WP_Post $post
     * @return array
     */
    public static function getUnavailableVideoList($post)
    {
        return get_post_meta($post->ID, self::UNAVAILABLE_IDS_KEY);
    }

    /**
     * @param \WP_Post $post
     * @return array
     */
    public static function getAvailableVideoList($post)
    {
        return get_post_meta($post->ID, self::AVAILABLE_IDS_KEY);
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
        return $counter;
    }

    public static function getUnavailableVideoLabelCounterHtml()
    {
        $counter = self::getUnavailableVideoLabelCounter();
        $label = "<span class='update-plugins count-$counter' title='Unavailable Videos'><span class='update-count'>$counter</span></span>";
        return $label;
    }

    public static function getAvailableVideoLabelCounter()
    {
        $counter = 0;
        $posts = Database::getAllPostsWithAvailableVideos();
        foreach ($posts as $post) {
            $wpPost = get_post($post['post_id']);
            $ids = self::getAvailableVideoList($wpPost);
            $counter += sizeof($ids);
        }
        return $counter;
    }

    public static function getAvailableVideoLabelCounterHtml()
    {
        $counter = self::getAvailableVideoLabelCounter();
        $label = "<span class='update-plugins count-$counter' style='background-color: #2ea2cc;' title='Unavailable Videos'><span class='update-count'>$counter</span></span>";
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
     * @param \WP_Post $source
     * @return boolean
     */
    public static function isNeedCheckPost($source)
    {
        $postLastCheckTime = get_post_meta($source->ID, Common::LAST_CHECK_TIME_KEY);
        if (sizeof($postLastCheckTime) > 0) {
            $postLastCheckTime = new \DateTime($postLastCheckTime[0]);
        }
        $postLastUpdatetime = new \DateTime($source->post_modified_gmt);

        if (!isset($postLastCheckTime) || $postLastUpdatetime > $postLastCheckTime) {
            Logger::info('post  ' . $source->ID . ', need update');
            return true;
        } else {
            Logger::info('post  ' . $source->ID . ', no changes');
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

    public static function getId(string $url) : ?string
    {
        $url = trim($url);
        $parsedUrl = parse_url($url);
        if (isset($parsedUrl['host'])) {
            if ($parsedUrl['host'] == 'youtu.be') {
                $id = str_replace('/', '', $parsedUrl['path']);
            } elseif ($parsedUrl['host'] == 'www.youtube.com') {
                $params = [];
                parse_str($parsedUrl['query'], $params);
                if (isset($params['v'])) {
                    $id = $params['v'];
                } else {
                    $id = null;
                }
            } else {
                $id = null;
            }
        } else {
            $id = null;
        }
        return $id;
    }

    public static function isDevelopMode()
    {
        return file_exists(__DIR__ . '/develop_mode.enable');
    }

    public static function sendEmailNotification()
    {
        $posts = Database::getPostsWithUnavailableVideos(true);
        $email = get_option('admin_email');
        $homeUrl = get_home_url();
        $template = Template::getInstance();
        $template->setTemplate('UnavailableVideosMail.php');
        $template->setParams(['posts' => $posts]);
        $message = $template->render();
        wp_mail($email, 'Youtube checker, ' . $homeUrl, $message);
    }

    public static function notifyIfNotConfigured()
    {
        $apiKey = get_option(Common::SETTINGS_API_KEY);
        if(!$apiKey) {
            echo '<div class="notice notice-warning">
        <p>
    Youtube checker: Google API key not set.
        </p>
    </div>';
        }
        $checkFreq = get_option(Common::SETTINGS_CHECK_FREQ);
        if(!$checkFreq) {
            echo '<div class="notice notice-warning">
        <p>
    Youtube checker: search and check videos don\'t scheduled. 
        </p>
    </div>';
        }
    }
}
