<?php

error_reporting(E_ALL | E_STRICT);

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

    $module = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'stats';
    
    $validModules = array('stats', 'purge');
    if (in_array($module, $validModules, true)) {
        require "lib/$module.php";
    } else {
        throw new Exception('Invalid action parameter');
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
        echo "\n";
    } else {
        require "lib/$module.txt.php";
    }
    exit;
}

?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8" />
    <title>CDN77 stats</title>
    <style>
        #nav {
            margin-bottom: 2em;
            margin-left: 3em;
            margin-right: 3em;
        }
        #nav a {
            padding-left: 0.5em;
            padding-right: 0.5em;
        }
        #nav a {
            color: blue;
            text-decoration: none;
        }
        #nav a:visited {
            color: blue;
        }
        #nav a.current {
            font-weight: bold;
        }
    </style>
</head>
<body>
    <section id="nav">
        <?php
        $links = array(
            'stats' => 'Stats',
            'purge' => 'Purge'
        );
        $linksHtml = array();
        foreach ($links as $key => $title) {
            $class = ($key == $module) ? 'navlink current' : 'navlink';
            $linksHtml[] = '<a href="?action=' . $key . '" class="' . $class . '">' . $title . '</a>';
        }
        echo join($linksHtml, " | \n");
        ?>
    </section>
    <section id="main">
        <?php if (isset($failure)): ?>
            <p class="error"><?php echo htmlspecialchars($failure); ?></p>
        <?php else: ?>
            <?php require "lib/$module.html.php"; ?>
        <?php endif; ?>
    <section>
</body>
</html>
