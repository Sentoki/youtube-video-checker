<?php

namespace PetrovEgor\ContentSources;

use PetrovEgor\Common;
use PetrovEgor\SingletonAbstract;
use PetrovEgor\YoutubePlugins\YoutubePluginAbstract;

abstract class ContentSourceAbstract extends SingletonAbstract
{
    abstract function getAllObjects() : array;

    public function isNeedCheckSource($source): bool
    {
        return Common::isNeedCheckPost($source);
    }
}
