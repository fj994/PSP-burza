<?php
namespace controller;

class Share
{
    static function add()
    {
        $params = json_decode(file_get_contents('php://input'), true);

        $response = file_get_contents("https://www.alphavantage.co/query?function=TIME_SERIES_DAILY&symbol=" . $params['symbol'] . "&apikey=T3TWWW76K5E070HC");
        
        if (!$response) {
            $msg = 'False symbol';
            return;
        }
        $obj = json_decode($response, true);

        if (!array_key_exists('Error Message', $obj)) {

            $share = new \model\ShareModel($obj['Meta Data']['2. Symbol'], $obj['Meta Data']['3. Last Refreshed'], $obj['Meta Data']['5. Time Zone']);
            $msg = $share->add();
        } else {
            $msg = 'Invalid share symbol';
        }

        include($_SERVER['DOCUMENT_ROOT'] . '/view/Response.php');
    }

    static function delete()
    {
        $params = json_decode(file_get_contents('php://input'), true);
        $msg = \model\ShareModel::delete($params['symbol']);
        include($_SERVER['DOCUMENT_ROOT'] . '/view/Response.php');
    }

    static function getSymbolParam() {
        parse_str(parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY), $params);
        return $params;
    }

    static function update() {
        $msg = \model\ShareModel::update();
        include($_SERVER['DOCUMENT_ROOT'] . '/view/Response.php');
    }

    static function get($format, $params) {
        $msg = \model\ShareModel::$format($params);
        include($_SERVER['DOCUMENT_ROOT'] . '/view/Response.php');
    }
}
