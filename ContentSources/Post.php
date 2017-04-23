<?php

namespace PetrovEgor\ContentSources;

class Post extends ContentSourceAbstract
{
    public static $instance;

    /**
     * @return \WP_Post[]
     */
    public function getAllObjects() : array
    {
        $posts = get_posts(['numberposts' => -1]);
        return $posts;
    }
}
