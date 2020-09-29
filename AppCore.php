<?php
spl_autoload_register(function ($class) {
    $str = str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';

    if(file_exists($str)) {
        include($str);
    } else {
        return true;
    }
});

class AppCore
{
    function __construct() {
        $db = new dbSetup();
        $router = new util\Router();
        $router->route();

        
    }
}