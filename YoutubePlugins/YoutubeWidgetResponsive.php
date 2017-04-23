<?php

namespace PetrovEgor\YoutubePlugins;

use PetrovEgor\Common;
use PetrovEgor\Logger;

/**
 * Class that add support Youtube Widget Responsive plugin
 * https://ru.wordpress.org/plugins/youtube-widget-responsive/
 *
 * @package PetrovEgor\YouTubePlugins
 */
class YoutubeWidgetResponsive extends YoutubePluginAbstract
{
    public static $instance;

    protected $tagName = 'youtube';

    public function shortCodeHandler($attr, $content = '')
    {
        global $youtubeCheckerCurrentPost;
        $post = $youtubeCheckerCurrentPost;

        $videoId = $attr['video'];

        delete_post_meta($post->ID, Common::LAST_CHECK_TIME_KEY);
        add_post_meta($post->ID, Common::ALL_VIDEOS_IDS_KEY, $videoId);
        $now = new \DateTime('now');
        add_post_meta($post->ID, Common::LAST_CHECK_TIME_KEY, $now->format('Y-m-d H:i:s'));
    }
}
