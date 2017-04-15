<?php
/**
 * Youtube video checker
 *
 * @package YoutubeVideoChecker
 * @author Egor Petrov
 *
 * @wordpress-plugin
 * Plugin Name: Youtube video checker
 * Plugin URI: http://hayate.ru
 * Description: This plugin finds youtube video ids on posts and send request to youtube API that check - does that videos still available. If video became unavailable, plugin send notifications about that.
 * Version 1.0.0
 * Author: Egor Petrov
 * Author URI: http://hayate.ru
 * Text Domain: youtube-video-checker
 */


require_once 'autoload.php';

use PetrovEgor\Logger;
use PetrovEgor\Common;
use PetrovEgor\Cron;
use PetrovEgor\Pagination;

$break = 1;
/*
 * Adding new actions
 */
add_action('search-videos-in-posts', 'searchVideosInPost');
add_action('check-by-api', 'checkByApi');

/*
 * Cron actions and filters
 */
add_action('youtube-checker-cron', [Cron::class, 'cron']);
add_filter('cron_schedules', [Cron::class, 'everyTenSecondsInterval']);

register_activation_hook(__FILE__, [\PetrovEgor\Database::class, 'updateSchema']);

$checkFreq = get_option(Common::SETTINGS_CHECK_FREQ);
if (isset($checkFreq)) {
    Logger::info('checkFreq set: ' . $checkFreq);
    $nextScheduled = wp_next_scheduled('youtube-checker-cron');
    if (!$nextScheduled) {
        Logger::info('not scheduled');
        wp_schedule_event(time(), 'ten_seconds', 'youtube-checker-cron');
    } else {
        $nextScheduled = new DateTime('@' . $nextScheduled);
        Logger::info('scheduled: ' . $nextScheduled->format('Y-m-d H:i:s'));
    }
} else {
    Logger::info('checkFreq NOT set');
}

function indexPage()
{
    switch ($_GET['youtube_checker_action']) {
        case 'search-videos-in-posts':
            do_action('search-videos-in-posts');
            break;
        case 'check-by-api':
            do_action('check-by-api');
            break;
    }
    echo "<h1>index page</h1>";
    echo "<a href='/wp-admin/admin.php?page=youtube-checker&youtube_checker_action=search-videos-in-posts'>Search videos in posts</a><br>";
    echo "<a href='/wp-admin/admin.php?page=youtube-checker&youtube_checker_action=check-by-api'>Check videos by API</a><br>";
}

function allVideos()
{
    //add
    echo "allVideos";
}

function unavailableVideos()
{
    $posts = \PetrovEgor\Database::getPostsWithUnavailableVideos();
    $template = \PetrovEgor\templates\Template::getInstance();
    $template->setTemplate('UnavailableVideos.php');
    $pagesNumber = Pagination::getPagesNumber();
    $currentPage = Pagination::getCurrentPage();
    $paginationLinks = paginate_links(
        [
            'base' => add_query_arg('pagination', '%#%'),
            'format' => '',
            'prev_text' => __('« Previous'),
            'next_text' => __('Next »'),
            'total' => $pagesNumber,
            'current' => $currentPage,
//            'type' => 'array'
        ]);
    $template->setParams([
        'posts' => $posts,
        'pagesNumber' => $pagesNumber,
        'currentPage' => $currentPage,
        'paginationLinks' => $paginationLinks,
    ]);
    $template->render();
}

function settings()
{
    $template = \PetrovEgor\templates\Template::getInstance();
    $template->setTemplate('Settings.php');
    $params = [];

    if (!empty($_POST) && isset($_POST['api_key']) && isset($_POST['sync_frequency'])) {
        if (!Common::isVideoAvailable('jNQXAC9IVRw', $_POST['api_key'])) {
            $params['is_wrong_api_key'] = true;
            //wrong api key
        } else {
            delete_option(Common::SETTINGS_API_KEY);
            add_option(Common::SETTINGS_API_KEY, $_POST['api_key']);
            delete_option(Common::SETTINGS_CHECK_FREQ);
            add_option(Common::SETTINGS_CHECK_FREQ, $_POST['sync_frequency']);
        }
    }
    $apiKey = get_option(Common::SETTINGS_API_KEY);
    $checkFreq = get_option(Common::SETTINGS_CHECK_FREQ);
    if (isset($apiKey) && isset($checkFreq)) {
        $params['apiKey'] = $apiKey;
        $params['checkFreq'] = $checkFreq;
        $template->setParams($params);
    }
    $template->render();
}

$menuIndex = function() {
    add_menu_page('Youtube checker', 'Youtube checker', 'manage_options', 'youtube-checker', 'indexPage');
    add_submenu_page(
        'youtube-checker',
        'Available videos',
        'Available videos',
        'manage_options',
        'youtube-checker-all-videos',
            'allVideos');
    $labelCounter = Common::getUnavailableVideoLabelCounter();
    add_submenu_page(
        'youtube-checker',
        'Unavailable videos',
        'Unavailable videos' . $labelCounter,
        'manage_options',
        'youtube-checker-unavailable-videos',
        'unavailableVideos');
    add_submenu_page(
        'youtube-checker',
        'Settings',
        'Settings',
        'manage_options',
        'youtube-checker-settings',
        'settings');
};

add_action('admin_menu', $menuIndex);

function searchVideosInPost($attr)
{
    $break = 1;
    $posts = get_posts(['numberposts' => -1]);
    Logger::info('posts found: ' . sizeof($posts));
    $pages = get_pages();
    Logger::info('pages found: ' . sizeof($pages));
    /** @var WP_Post $post */
    foreach ($posts as $post) {
        /** @var \PetrovEgor\YoutubePlugins\YoutubePluginAbstract $plugin */
        foreach (Common::$supportedPlugins as $supportedPlugin) {
            $plugin = $supportedPlugin::getInstance();
            Logger::info('post  ' . $post->ID);
            if (Common::isNeedCheckPost($post)) {
                if($plugin->hasShorcode($post)) {
                    $plugin->saveYoutubeIds($post);
                }
                Common::updateLastCheckTime($post);
            }
        }
    }
}

function checkByApi($attr)
{
    $break = 1;
    $posts = get_posts(['numberposts' => -1]);
    /** @var WP_Post $post */
    foreach ($posts as $post) {
        $isHaveUnavailableVideo = false;
        \PetrovEgor\Database::unmarkUnavailableVideo($post);
        $ids = Common::getYoutubeIdsByPost($post);
        Common::resetUnavailableVideoListForPost($post);
        foreach ($ids as $id) {
            if (!Common::isVideoAvailable($id)) {
                $isHaveUnavailableVideo = true;
                Common::reportVideoUnavailable($post, $id);
            }
        }
        if ($isHaveUnavailableVideo) {
            \PetrovEgor\Database::markUnavailableVideo($post);
        }
    }
}

$break = 1;
