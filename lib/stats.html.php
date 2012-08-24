<?php
$stats = array(
    '24h' => 'last 24 hours',
    '48h' => 'last 48 hours',
    '30d' => 'last 30 days',
    '00m' => 'current month',
    '01m' => 'previous month',
    '02m' => 'month before previous month'
);

?>
<ul>
    <?php foreach ($stats as $key => $title): ?>
        <li><?php echo htmlspecialchars(ucfirst($title)); ?>: <?php echo htmlspecialchars($records[$key]); ?> </li>
    <?php endforeach; ?>
</ul>
