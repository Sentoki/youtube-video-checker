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

use PetrovEgor\ContentSources\ContentSourceAbstract;
use PetrovEgor\Database;
use PetrovEgor\Logger;
use PetrovEgor\Common;
use PetrovEgor\Cron;
use PetrovEgor\Pagination;
use PetrovEgor\YoutubePlugins\YoutubePluginAbstract;

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

/*
 * When post or page or WooCommerce product deleted, need delete record about videos
 */
add_action('delete_post', [Database::class, 'deleteVideoRecordForPost']);

register_activation_hook(__FILE__, [\PetrovEgor\Database::class, 'updateSchema']);

$checkFreq = get_option(Common::SETTINGS_CHECK_FREQ);
if (isset($checkFreq)) {
    Logger::info('checkFreq set: ' . $checkFreq);
    $nextScheduled = wp_next_scheduled('youtube-checker-cron');
    if (!$nextScheduled) {
        Logger::info('not scheduled');
        wp_schedule_event(time(), $checkFreq, 'youtube-checker-cron');
//        wp_unschedule_event(time(), 'ten_seconds', 'youtube-checker-cron');
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
    $template = \PetrovEgor\templates\Template::getInstance();
    $template->setTemplate('IndexPage.php');
    $lastCheck = Database::getLastCheckTime();
    $nextScheduled = Database::getNextCheckTime();
    $availableCounter = Common::getAvailableVideoLabelCounter();
    $unavailableCounter = Common::getUnavailableVideoLabelCounter();

    $template->setParams([
        'lastCheck' => $lastCheck,
        'nextScheduled' => $nextScheduled->format('Y-m-d H:i:s'),
        'availableCounter' => $availableCounter,
        'unavailableCounter' => $unavailableCounter,
    ]);
    $template->render();

}

function allVideos()
{
    $posts = \PetrovEgor\Database::getPostsWithAvailableVideos();
    $template = \PetrovEgor\templates\Template::getInstance();
    $template->setTemplate('AvailableVideos.php');
    $pagesNumber = Pagination::getAvailablePagesNumber();
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

function unavailableVideos()
{
    $posts = \PetrovEgor\Database::getPostsWithUnavailableVideos();
    $template = \PetrovEgor\templates\Template::getInstance();
    $template->setTemplate('UnavailableVideos.php');
    $pagesNumber = Pagination::getUnavailablePagesNumber();
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

            wp_unschedule_event(time(), 'ten_seconds', 'youtube-checker-cron');
            wp_schedule_event(time(), $_POST['sync_frequency'], 'youtube-checker-cron');
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
    add_menu_page(
        'Youtube checker',
        'Youtube checker',
        'manage_options',
        'youtube-checker',
        'indexPage');
    $availableLabelCounter = Common::getAvailableVideoLabelCounterHtml();
    add_submenu_page(
        'youtube-checker',
        'Available videos',
        'Available videos' . $availableLabelCounter,
        'manage_options',
        'youtube-checker-all-videos',
            'allVideos');
    $unavailableLabelCounter = Common::getUnavailableVideoLabelCounterHtml();
    add_submenu_page(
        'youtube-checker',
        'Unavailable videos',
        'Unavailable videos' . $unavailableLabelCounter,
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
    /** @var ContentSourceAbstract $contentSource */
    foreach (Common::$supportedContentSources as $contentSource) {
            /** @var ContentSourceAbstract $source */
            $source = $contentSource::getInstance();
            $objects = $source->getAllObjects();
            foreach ($objects as $object) {
                if ($source->isNeedCheckSource($object)) {
                    /** @var YoutubePluginAbstract $supportedPlugin */
                    foreach (Common::$supportedPlugins as $supportedPlugin) {
                        /** @var YoutubePluginAbstract $plugin */
                        $plugin = $supportedPlugin::getInstance();
                        if($plugin->hasShorcode($object)) {
                            $plugin->saveYoutubeIds($object);
                        }
                    }
                }
                Common::updateLastCheckTime($object);
            }
        }
}

function checkByApi($attr)
{
    try {
        $sources = [];
        /** @var ContentSourceAbstract $contentSource */
        foreach (Common::$supportedContentSources as $contentSource) {
            /** @var ContentSourceAbstract $source */
            $source = $contentSource::getInstance();
            $sources = array_merge($sources, $source->getAllObjects());
        }
        /** @var WP_Post $post */
        foreach ($sources as $post) {
            $isHaveUnavailableVideo = false;
            $isHaveAvailableVideo = false;
            \PetrovEgor\Database::unmarkUnavailableVideo($post);
            \PetrovEgor\Database::unmarkAvailableVideo($post);
            $ids = Common::getYoutubeIdsByPost($post);
            Common::resetUnavailableVideoListForPost($post);
            Common::resetAvailableVideoListForPost($post);
            foreach ($ids as $id) {
                if (!Common::isVideoAvailable($id)) {
                    $isHaveUnavailableVideo = true;
                    Common::reportVideoUnavailable($post, $id);
                } else {
                    $isHaveAvailableVideo = true;
                    Common::reportVideoAvailable($post, $id);
                }
            }
            if ($isHaveUnavailableVideo) {
                \PetrovEgor\Database::markUnavailableVideo($post);
            }
            if ($isHaveAvailableVideo) {
                \PetrovEgor\Database::markAvailableVideo($post);
            }
        }
    } catch (\Exception $exception) {
        Logger::info($exception->getMessage(), 'errors.log');
        throw $exception;
    }
    Database::saveCheckTime();
//    $counter = Common::getUnavailableVideoLabelCounter();
//    if ((int)$counter > 0) {
//        Common::sendEmailNotification();
//    }
}
