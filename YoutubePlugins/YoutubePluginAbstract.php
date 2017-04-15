<?php

namespace PetrovEgor\YoutubePlugins;

use PetrovEgor\Common;
use PetrovEgor\Logger;

abstract class YoutubePluginAbstract
{
    protected $tagName;
    protected $shortCodeMethod = 'shortCodeHandler';

    public static $instance;

    private function __construct()
    {
    }

    public static function getInstance()
    {
        if (!isset(static::$instance)) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    /**
     * @param \WP_Post $post
     * @return bool
     */
    public function hasShorcode($post)
    {
        $hasShortcode = has_shortcode($post->post_content, $this->tagName);
        if ($hasShortcode) {
            Logger::info('post  ' . $post->ID . ', has shortcode');
            return true;
        }
        Logger::info('post  ' . $post->ID . ', no shortcode');
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

        add_shortcode($this->tagName, [static::class, $this->shortCodeMethod]);

        do_shortcode($post->post_content);
    }

    abstract public function shortCodeHandler($attr, $content = '');
}