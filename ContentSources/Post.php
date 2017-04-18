<?php

namespace PetrovEgor\ContentSources;

use PetrovEgor\Common;
use PetrovEgor\Logger;

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

    /**
     * @param \WP_Post $source
     * @return bool
     */
    public function isNeedCheckSource($source): bool
    {
        return Common::isNeedCheckPost($source);
    }
}
