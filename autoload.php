<?php

spl_autoload_register(function ($className){
    $className = str_replace('\\', '/', $className);
    $className = str_replace('PetrovEgor/', '', $className);
    $file = __DIR__ . DIRECTORY_SEPARATOR .  str_replace('\\', '/', $className) . '.php';
    if(is_file($file)){
        require_once $file;
    }
});
