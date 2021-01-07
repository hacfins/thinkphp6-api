<?php
require __DIR__ . '/../vendor/autoload.php';

$systemIns = new \command\SystemMonitor();

dump($systemIns->GetUpTime());
dump( $systemIns->GetMem(true));
dump( $systemIns->GetCPU());
dump( $systemIns->GetLoad());
dump( $systemIns->GetNetwork(true));
dump( $systemIns->GetDisk('sda', '/mnt/volume1'));