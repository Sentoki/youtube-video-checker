<?php

namespace PetrovEgor;

abstract class SingletonAbstract
{
    public static $instance;

    protected function __construct()
    {
    }

    public static function getInstance()
    {
        if (!isset(static::$instance)) {
            static::$instance = new static();
        }
        return static::$instance;
    }
}
