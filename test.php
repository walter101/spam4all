<?php

$page = 10;

for($page=10;$page < 20;$page++)
{
    if ($page == 14) { echo '14....'; }
    echo $page.PHP_EOL;
}

$a = 10;
$a++;

$start = microtime(true);
echo 'Start: '.$start.PHP_EOL;

sleep(2);
$end = microtime(true);
echo 'End: '.$end.PHP_EOL;

$total = 'Total: '.($end - $start).PHP_EOL;

echo $total;
echo $a;