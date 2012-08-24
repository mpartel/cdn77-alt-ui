<?php
$pdo = new PDO('sqlite:' . $historyFile);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$tableName = 'cdn77_history';

$schema = <<<EOS
    CREATE TABLE $tableName (
        json_data TEXT,
        recorded_at INT
    )
EOS;

if ($pdo->query("SELECT 1 FROM sqlite_master WHERE type='table' AND name='$tableName'")->fetchColumn() === false) {
    $pdo->exec($schema);
}

$now = time();
$recentSnapshotMinTime = $now - $minTimeBetweenSnapshots;

$stmt = $pdo->prepare("SELECT json_data FROM $tableName WHERE recorded_at >= ?");
$stmt->execute(array($recentSnapshotMinTime));
$jsonData = $stmt->fetchColumn();
if ($jsonData === false) {
    $curl = curl_init("https://client.cdn77.com/api/traffic");
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, array('id' => $cdnId, 'login' => $userName, 'passwd' => $apiKey));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $jsonData = curl_exec($curl);
    curl_close($curl);
    if (empty($jsonData)) { // Sometimes the API indeed returns an empty string
        throw new Exception("Failed to get data from CDN77.");
    }
    $jsonData = trim($jsonData);

    $records = json_decode($jsonData, true); // Parse before inserting to ensure validity

    $stmt = $pdo->prepare("INSERT INTO $tableName (json_data, recorded_at) VALUES (?, ?)");
    $stmt->execute(array($jsonData, $now));
} else {
    $records = json_decode($jsonData, true);
}
