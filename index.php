<?php

function myErrorHandler($errno, $errstr, $errfile, $errline, $errcontext) {
    throw new Exception("$errfile:$errline: $errstr", $errno);
}

set_error_handler('myErrorHandler');

try {
    $settings = require 'settings.php';

    $cdnId = $settings['cdn_id'];
    $userName = $settings['login'];
    $apiKey = trim(file_get_contents($settings['api_key_file']));
    $historyFile = $settings['history_file'];
    $minTimeBetweenSnapshots = $settings['min_time_between_snapshots'];

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

} catch (Exception $e) {
    $failure = "Error: " . $e->getMessage();
}

$stats = array(
    '24h' => 'last 24 hours',
    '48h' => 'last 48 hours',
    '30d' => 'last 30 days',
    '00m' => 'current month',
    '01m' => 'previous month',
    '02m' => 'month before previous month'
);

if (php_sapi_name() == 'cli') {
    if (isset($failure)) {
        echo $failure;
    } else {
        echo $jsonData;
    }
    exit;
}

?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8" />
    <title>CDN77 stats</title>
</head>
<body>
    <?php if (isset($failure)): ?>
        <p class="error"><?php echo htmlspecialchars($failure); ?></p>
    <?php else: ?>
        <ul>
            <?php foreach ($stats as $key => $title): ?>
                <li><?php echo htmlspecialchars(ucfirst($title)); ?>: <?php echo htmlspecialchars($records[$key]); ?> </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</body>
</html>
