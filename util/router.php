<?php

namespace util;

class Router
{
    function route()
    {
        $url = parse_url($_SERVER['REQUEST_URI']);

        $path = explode('/', $url['path']);
        array_shift($path);

        if (count($path) <= 1 && $_SERVER['REQUEST_METHOD'] == 'GET') {
            include($_SERVER['DOCUMENT_ROOT'] . '/view/documentationView.php');
            return;
        }

        $controller = '\\controller\\' . ucfirst($path[0]);
        if (sizeof($path) > 1) {
            $method = $path[1];
        }

        switch ($_SERVER['REQUEST_METHOD']) {
            case 'GET':
                if (!$controller) {
                    include($_SERVER['DOCUMENT_ROOT'] . '/view/documentationView.php');
                } else {
                    $controller::$method($path[2], $_GET);
                }
                break;

            case 'POST':
                $controller::add();
                break;
                case 'PUT':
                    $controller::update();
                break;
            case 'DELETE':
                $controller::delete();
                break;
        }

        // if (method_exists($controller, $method)) {
        //     if ($method == 'get') {
        //         if(count($path) < 3) {
        //             echo 'bad api call';
        //             return;
        //         }

        //         $controller::$method($path[2], $_GET);
        //     } else {
        //         \controller\Share::$controller();
        //     }
        // } else {
        //     echo 'wrong api call';
        // }


        // switch($url) {
        //     case $url[0] == "":
        //         include($_SERVER['DOCUMENT_ROOT'] . '/view/documentationView.php');
        //     break;

        //     default:
        //         echo 'url not found!';
        //     break;
        // }
    }
}
