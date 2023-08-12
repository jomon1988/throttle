<?php

$configs = include('config.php');
$throttleLimit = $configs['throttleLimit']; // Change this value to your desired limit in config
$timeLimit = $configs['timeLimit']; // Change this value to your desired time in seconds in config
$referringDomain = $_SERVER['REMOTE_ADDR'];

$throttleDataFilePath = 'throttle_data.json'; // here we are using a JSON file for data saving

function isThrottled($throttleDataFilePath, $referringDomain, $throttleLimit,$timeLimit) {
    if (!file_exists($throttleDataFilePath)) {
        $throttleData = [];
    } else {
        $throttleData = json_decode(file_get_contents($throttleDataFilePath), true);
    }

    if (!isset($throttleData[$referringDomain])) {
        $throttleData[$referringDomain] = [
            'count' => 1,
            'timestamp' => time()
        ];
    } else {
        $currentTime = time();
        $lastTimestamp = $throttleData[$referringDomain]['timestamp'];
        if ($currentTime - $lastTimestamp < $timeLimit) {
            if ($throttleData[$referringDomain]['count'] >= $throttleLimit) {
                return true;
            } else {
                $throttleData[$referringDomain]['count']++;
            }
        } else {
            $throttleData[$referringDomain]['count'] = 1;
        }

        $throttleData[$referringDomain]['timestamp'] = $currentTime;
    }
    file_put_contents($throttleDataFilePath, json_encode($throttleData));

    return false;
}

// checking the throttle condition
if (isThrottled($throttleDataFilePath, $referringDomain, $throttleLimit, $timeLimit)) {
    http_response_code(429);
    die("Throttle limit exceeded. Please wait and try again.");
}
$result = [
    'status' => true,
    'data' => 'data'
];
echo json_encode($result);
?>