<?php
require_once('util/rb-mysql.php');
R::setup('mysql:host=localhost;dbname=burza', 'root', '');


function updateDaily($symbol, $compact = true)
{
    $share = R::findOne('share', 'symbol=?', array($symbol));

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
                $dailyValue = R::dispense('daily');
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
        R::store($share);

        return 'share ';
    } else {
        return 'share with symbol not found';
    }
}

function update()
{
    $shares = R::find('share');
    foreach ($shares as $share) {
        updateDaily($share->symbol);
    }
}

update();
