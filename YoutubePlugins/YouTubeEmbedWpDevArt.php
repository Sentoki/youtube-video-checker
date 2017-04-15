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
    protected $tagName = 'wpdevart_youtube';

    public function shortCodeHandler($attr, $content = '')
    {
        global $youtubeCheckerCurrentPost;
        $post = $youtubeCheckerCurrentPost;

        Logger::info('myShortcodeHandler execute for post: ' . $post->ID);

        delete_post_meta($post->ID, Common::LAST_CHECK_TIME_KEY);
        add_post_meta($post->ID, Common::ALL_VIDEOS_IDS_KEY, $content);
        $now = new \DateTime('now');
        add_post_meta($post->ID, Common::LAST_CHECK_TIME_KEY, $now->format('Y-m-d H:i:s'));
        $break = 1;
    }
}
