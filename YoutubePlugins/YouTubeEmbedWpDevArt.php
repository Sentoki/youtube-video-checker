<?php

namespace PetrovEgor\YoutubePlugins;
use PetrovEgor\Common;
use PetrovEgor\Logger;

/**
 * Class that add support plugin:
 * https://ru.wordpress.org/plugins/youtube-video-player/
 * @package PetrovEgor\YouTubePlugins
 */
class YouTubeEmbedWpDevArt extends YoutubePluginAbstract
{
    public static $instance;

    protected $tagName = 'wpdevart_youtube';

    public function shortCodeHandler($attr, $content = '')
    {
        global $youtubeCheckerCurrentPost;
        $post = $youtubeCheckerCurrentPost;

        Logger::info('myShortcodeHandler execute for post: ' . $post->ID);

        delete_post_meta($post->ID, Common::LAST_CHECK_TIME_KEY);
        add_post_meta($post->ID, Common::ALL_VIDEOS_IDS_KEY, $content);
        $break = 1;
    }
}
