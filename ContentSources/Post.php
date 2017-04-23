<?php

namespace PetrovEgor\ContentSources;

class Post extends ContentSourceAbstract
{
    public static $instance;

    /**
     * @return \WP_Post[]
     */
    public function getAllObjects()
    {
        $posts = get_posts(array('numberposts' => -1));
        return $posts;
    }
}
