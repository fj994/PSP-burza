<?php
namespace model;

class ShareModel
{
    function __construct($symbol, $last_refreshed, $time_zone)
    {
        $this->symbol = $symbol;
        $this->last_refreshed = $last_refreshed;
        $this->time_zone = $time_zone;
    }

    function add()
    {
        $share = \R::findOne('share', 'symbol=?', array($this->symbol));

        if (!$share) {
            $share = \R::dispense('share');
            $share->symbol = $this->symbol;
            $share->last_refreshed = $this->last_refreshed;
            $share->time_zone = $this->time_zone;
            $share->ownDailyList = array();
            $id = \R::store($share);

            ShareModel::updateDaily($share->symbol, false);
            return 'Added succesfully';
        }

        return 'Share already exists';
    }

    static function delete($symbol)
    {
        $share = \R::findOne('share', 'symbol=?', array($symbol));

        if ($share) {
            $share->xownDailyList = array();
            \R::store($share);
            \R::trash($share);
            return $symbol . ' successfully deleted';
        }

        return $symbol . ' not found';
    }

    static function updateDaily($symbol, $compact = true)
    {
        $share = \R::findOne('share', 'symbol=?', array($symbol));

        if ($share) {
            $lastDate = end($share->ownDailyList);

            if ($lastDate) {
                $lastDate = $lastDate->date;
            }

            $compact = $compact ? 'compact' : 'full';
            $response = file_get_contents("https://www.alphavantage.co/query?function=TIME_SERIES_DAILY&symbol=" . $share->symbol . "&outputsize=" . $compact . "&apikey=T3TWWW76K5E070HC");

            $obj = json_decode($response, true);

            if ($obj["Meta Data"]["3. Last Refreshed"]) {
                $share->last_refreshed = $obj["Meta Data"]["3. Last Refreshed"];
            }

            foreach (array_reverse($obj["Time Series (Daily)"]) as $key => $value) {
                if ($lastDate < $key) {
                    $dailyValue = \R::dispense('daily');
                    echo $key;
                    $dailyValue->date = $key;
                    $dailyValue->open = $value["1. open"];
                    $dailyValue->high = $value["2. high"];
                    $dailyValue->low = $value["3. low"];
                    $dailyValue->close = $value["4. close"];
                    $dailyValue->volume = $value["5. volume"];

                    $share->ownDailyList[] = $dailyValue;
                }
            }
            \R::store($share);

            return 'share ';
        } else {
            return 'share with symbol not found';
        }
    }

    static function update()
    {
        $shares = \R::find('share');
        foreach ($shares as $share) {
            ShareModel::updateDaily($share->symbol);
        }
    }

    static function day($params)
    {
        $response = [];

        $date = $params['year'] . '-' . ($params['month'] < 10 ? '0' : '') . $params['month'] . '-' . ($params['day'] < 10 ? '0' : '') . $params['day'];

        $shares = \R::findAll('share');

        foreach ($shares as $share) {
            $shareData = \R::findAll('daily', "share_id = ? AND date = ?", [$share['id'], $date]);

            $shareObject = new \stdClass();
            $shareObject->symbol = $share['symbol'];
            $shareObject->data = $shareData;

            array_push($response, $shareObject);
        }

        return json_encode($response);
    }
    static function week($params)
    {
        $rangeStart = $params['year'] . '-' . ($params['month'] < 10 ? '0' : '') . $params['month'] . '-' . ($params['day'] < 10 ? '0' : '') . $params['day'];
        $rangeEnd = $params['year'] . '-' . ($params['month'] < 10 ? '0' : '') . $params['month'] . '-' . ($params['day'] < 10 ? '0' : '') . ($params['day'] + 6);

        return ShareModel::getShareBetweenDates($rangeStart, $rangeEnd);    
    }

    static function month($params)
    {
        $rangeStart = $params['year'] . '-' . ($params['month'] < 10 ? '0' : '') . $params['month'] . '-01';
        $rangeEnd = $params['year'] . '-' . ($params['month'] + 1 < 10 ? '0' : '') . ($params['month'] + 1) . '-01';

        return ShareModel::getShareBetweenDates($rangeStart, $rangeEnd);    
    }

    static function year($params)
    {
        $rangeStart = $params['year'] . '-01-01';
        $rangeEnd = $params['year'] . '-12-31';

        return ShareModel::getShareBetweenDates($rangeStart, $rangeEnd);    
    }

    static function getShareBetweenDates($rangeStart, $rangeEnd) {
        $response = [];

        $shares = \R::findAll('share');

        foreach ($shares as $share) {
            $shareData = \R::findAll('daily', "share_id = ? AND date BETWEEN '$rangeStart' AND '$rangeEnd'", [$share['id']]);

            $shareObject = new \stdClass();
            $shareObject->symbol = $share['symbol'];
            $shareObject->data = $shareData;

            array_push($response, $shareObject);
        }

        return json_encode($response);
    }
}
