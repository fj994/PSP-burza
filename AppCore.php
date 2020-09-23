<?php
class AppCore
{
    function __construct() {
        require_once($_SERVER['DOCUMENT_ROOT'] . '/dbSetup.php');
        require('util/router.php');

        $router = new Router();
        $router->route();
    }
}