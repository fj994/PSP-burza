<?php
class Router
{
    function route()
    {
        $url = parse_url($_SERVER['REQUEST_URI']);

        $path = explode('/', $url['path']);
        array_shift($path);

        if (count($path) <= 1) {
            include($_SERVER['DOCUMENT_ROOT'] . '/view/documentationView.php');
            return;
        }

        $controller = $path[0];
        $method = $path[1];

        require($_SERVER['DOCUMENT_ROOT'] . '/controller/share.php');

        if (method_exists($controller, $method)) {
            if ($method == 'get') {
                if(count($path) < 3) {
                    echo 'bad api call';
                    return;
                }
                
                $controller::$method($path[2], $_GET);
            } else {
                $controller::$method();
            }
        } else {
            echo 'wrong api call';
        }


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
