<?php

namespace PetrovEgor\ContentSources;

use PetrovEgor\Common;

class WooCommerce extends ContentSourceAbstract
{
    public static $instance;

    public function getAllObjects(): array
    {
        $products = get_posts(['post_type' => 'product']);
        return $products;
    }

    public function isNeedCheckSource($source): bool
    {
        return Common::isNeedCheckPost($source);
    }

}
