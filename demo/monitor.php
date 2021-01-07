<?php
require __DIR__ . '/../vendor/autoload.php';

$systemIns = new \command\SystemMonitor();

dump($systemIns->GetUpTime());
dump( $systemIns->GetMem());
dump( $systemIns->GetCPU());
dump( $systemIns->GetLoad());
dump( $systemIns->GetNetwork());
dump( $systemIns->GetDisk('sda', '/mnt/volume1'));