<?php
// Copy this to settings.php and edit
return array(
    'login' => 'user@example.com',
    'cdn_id' => 1234,
    'api_key_file' => dirname(__FILE__) . '/testdata/key',
    'history_file' => dirname(__FILE__) . '/testdata/db/history.sqlite3',
    'min_time_between_snapshots' => 10*60 // 10 minutes
);