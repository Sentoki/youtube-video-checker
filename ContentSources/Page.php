<?php

namespace PetrovEgor\ContentSources;

use PetrovEgor\Common;

class Page extends ContentSourceAbstract
{
    public static $instance;

    /**
     * @return array
     */
    public function getAllObjects(): array
    {
        $pages = get_pages();
        return $pages;
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
