<?php

namespace PetrovEgor\ContentSources;

use PetrovEgor\SingletonAbstract;

abstract class ContentSourceAbstract extends SingletonAbstract
{
    abstract function getAllObjects() : array;

    abstract function isNeedCheckSource($source) : bool;
}
