<?php
if (isset($jsonData)) {
    $data = json_decode($jsonData, true);
    $result = $data['description'];
    if ($result == 'Purge request successfull') { // [sic]
        $result = 'Purge request successful';
    }
    echo $result;
    return;
}

?>
<p>
    Purging CDN caches means that all CDN servers around the world will fetch
    fresh versions of all files from your push zone or main server.
    The purge will typically take effect within minutes.
</p>
<form method="post" action="?action=purge">
  <button type="submit">Purge caches</button>
</form>