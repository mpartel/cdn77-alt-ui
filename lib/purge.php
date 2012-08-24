<?php
if (php_sapi_name() == 'cli' || $_SERVER['REQUEST_METHOD'] == 'POST') {
    $curl = curl_init("https://client.cdn77.com/api/purge-all");
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, array('id' => $cdnId, 'login' => $userName, 'passwd' => $apiKey));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $jsonData = curl_exec($curl);
    if (empty($jsonData)) { // Sometimes the API indeed returns an empty string
        throw new Exception("Failed to send purge request to CDN77.");
    }
    curl_close($curl);
}
