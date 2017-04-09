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

$break = 1;
/*
 * Adding new actions
 */
add_action('search-videos-in-posts', 'searchVideosInPost');
add_action('check-by-api', 'checkByApi');

register_activation_hook(__FILE__, [\PetrovEgor\Database::class, 'updateSchema']);


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
    $template->setParams([
        'posts' => $posts,
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
        'All videos',
        'All videos',
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
    global $youtubeCheckerCurrentPost;
    add_shortcode('wpdevart_youtube', 'myShortcodeHandler');
    $break = 1;
    $posts = get_posts();
    Logger::info('posts found: ' . sizeof($posts));
    $pages = get_pages();
    Logger::info('pages found: ' . sizeof($pages));
    /** @var WP_Post $post */
    foreach ($posts as $post) {
        Logger::info('post  ' . $post->ID);
        $postLastCheckTime = Common::getPostLastCheckTime($post);
        $postLastUpdatetime = Common::getPostLastUpdateTime($post);
        if (!isset($postLastCheckTime) || $postLastUpdatetime > $postLastCheckTime) {
            if(has_shortcode($post->post_content, 'wpdevart_youtube')) {
                Logger::info('post  ' . $post->ID . ', has shortcode');
                $youtubeCheckerCurrentPost = $post;
                delete_post_meta($post->ID, Common::ALL_IDS_KEY);
                delete_post_meta($post->ID, Common::TIME_KEY);
                Logger::info('post  ' . $post->ID . ', saving youtube ids');
                do_shortcode($post->post_content);
            } else {
                Logger::info('post  ' . $post->ID . ', no shortcode');
            }
            $now = new DateTime('now');
            add_post_meta($post->ID, Common::TIME_KEY, $now->format('Y-m-d H:i:s'));
        } else {
            Logger::info('post  ' . $post->ID . ', no changes');
        }
        $break = 1;
    }
    $break = 1;
}

function myShortcodeHandler($attr, $content = '')
{
    global $youtubeCheckerCurrentPost;
    $post = $youtubeCheckerCurrentPost;

    delete_post_meta($post->ID, Common::TIME_KEY);
    add_post_meta($post->ID, Common::ALL_IDS_KEY, $content);
    $now = new DateTime('now');
    add_post_meta($post->ID, Common::TIME_KEY, $now->format('Y-m-d H:i:s'));
    $break = 1;
}

function checkByApi($attr)
{
    $break = 1;
    $posts = get_posts();
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
