<?php

namespace PetrovEgor\YoutubePlugins;

use PetrovEgor\Common;
use PetrovEgor\Logger;
use PetrovEgor\SingletonAbstract;

abstract class YoutubePluginAbstract extends SingletonAbstract
{
    protected $tagName;
    protected $shortCodeMethod = 'shortCodeHandler';

    public static function className()
    {
        return 'PetrovEgor\YoutubePlugins\YoutubePluginAbstract';
    }

    /**
     * @param \WP_Post $post
     * @return bool
     */
    public function hasShorcode($post)
    {
        add_shortcode($this->tagName, array(static::className(), $this->shortCodeMethod));
        $hasShortcode = has_shortcode($post->post_content, $this->tagName);
        if ($hasShortcode) {
            Logger::info('post  ' . $post->ID . ', has shortcode ' . $this->tagName);
            return true;
        }
        Logger::info('post  ' . $post->ID . ', no shortcode ' . $this->tagName);
        return false;
    }

    /**
     * @param \WP_Post $post
     */
    public function saveYoutubeIds($post)
    {
        global $youtubeCheckerCurrentPost;
        Logger::info('post  ' . $post->ID . ', saving youtube ids');

        /**
         * Clear old data about post
         */
        delete_post_meta($post->ID, Common::ALL_VIDEOS_IDS_KEY);
        delete_post_meta($post->ID, Common::LAST_CHECK_TIME_KEY);

        $youtubeCheckerCurrentPost = $post;

        add_shortcode($this->tagName, array(static::className(), $this->shortCodeMethod));

        do_shortcode($post->post_content);
    }

    abstract public function shortCodeHandler($attr, $content = '');

    public function getLastCheckTimeMetaKey()
    {
        return $this->tagName . '_last_check_time';
    }
}
