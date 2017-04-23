<?php

namespace PetrovEgor\YoutubePlugins;
use PetrovEgor\Common;
use PetrovEgor\Logger;

/**
 * Class that add support Youtube plugin
 * https://ru.wordpress.org/plugins/youtube-embed-plus/
 *
 * @package PetrovEgor\YouTubePlugins
 */
class YouTube extends YoutubePluginAbstract
{
    public static $instance;

    protected $tagName = 'embedyt';

    public static function className()
    {
        return 'PetrovEgor\YoutubePlugins\YouTube';
    }

    public function shortCodeHandler($attr, $content = '')
    {
        global $youtubeCheckerCurrentPost;
        $post = $youtubeCheckerCurrentPost;
        $content = Common::getId($content);

        Logger::info('myShortcodeHandler execute for post: ' . $post->ID);

        delete_post_meta($post->ID, Common::LAST_CHECK_TIME_KEY);
        add_post_meta($post->ID, Common::ALL_VIDEOS_IDS_KEY, $content);
        $now = new \DateTime('now');
        add_post_meta($post->ID, Common::LAST_CHECK_TIME_KEY, $now->format('Y-m-d H:i:s'));
        $break = 1;
    }
}
