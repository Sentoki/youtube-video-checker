<?php

$url = 'https://www.googleapis.com/youtube/v3/videos?';
$params = [
    'id' => 'pvWlLSGtLWI',
    'key' => 'AIzaSyCkml-hsS5ElMNNiW0R-Vf7hIrWyLwq-wU',
    'part' => 'status',
];

$url .= http_build_query($params);

$curl = curl_init();
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_URL, $url);
$out = curl_exec($curl);
$error = curl_error($curl);
$result = json_decode($out, true);
if (isset($result['pageInfo']) && isset($result['pageInfo']['totalResults'])) {
    if ($result['pageInfo']['totalResults'] > 0) {
        echo "ok\n";
    } else {
        echo "bad\n";
    }
}
//echo $out;