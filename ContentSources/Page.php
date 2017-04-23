<?php

namespace PetrovEgor\ContentSources;

use PetrovEgor\Common;

class Page extends ContentSourceAbstract
{
    public static $instance;

    /**
     * @return array
     */
    public function getAllObjects()
    {
        $pages = get_pages();
        return $pages;
    }
}
