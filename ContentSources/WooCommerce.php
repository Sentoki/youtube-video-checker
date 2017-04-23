<?php

namespace PetrovEgor\ContentSources;

use PetrovEgor\Common;

class WooCommerce extends ContentSourceAbstract
{
    public static $instance;

    public function getAllObjects()
    {
        $products = get_posts(array('post_type' => 'product'));
        return $products;
    }
}
