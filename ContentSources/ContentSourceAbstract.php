<?php

namespace PetrovEgor\ContentSources;

use PetrovEgor\Common;
use PetrovEgor\SingletonAbstract;
use PetrovEgor\YoutubePlugins\YoutubePluginAbstract;

abstract class ContentSourceAbstract extends SingletonAbstract
{
    abstract function getAllObjects();

    public function isNeedCheckSource($source)
    {
        return Common::isNeedCheckPost($source);
    }
}
