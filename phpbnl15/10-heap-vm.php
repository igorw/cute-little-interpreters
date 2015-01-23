<?php

$ops = [
    ["op" => 'const',   "value" => 1,   "address" => 1],   // $a = 1
    ["op" => 'const',   "value" => 10,  "address" => 2],  // $b = 10
    ["op" => '+',       "left" => 1,    "right" => 2,   "result" => 3],    // $c = $a + $b
    ["op" => 'print',   "address" => 3],      // print $c
    ["op" => 'const',   "value" => 10,  "address" => 4],  // $d = 10
    ["op" => '.',       "address" => 4],          // print chr($d)
];

$heap = [];
$ip = 0;

while ($ip < count($ops)) {
    $op = $ops[$ip];
    $ip++;

    echo "\n$ip:\t{$op['op']}\t".json_encode($heap)."\n";


    switch ($op['op']) {
        case 'assign':
            $heap[$op['result']] = $heap[$op['address']];
            break;
        case 'const':
            $heap[$op['address']] = $op['value'];
            break;
        case '+':
            $heap[$op['result']] = $heap[$op['left']] + $heap[$op['right']];
            break;
        case '-':
            $heap[$op['result']] = $heap[$op['left']] - $heap[$op['right']];
            break;
        case 'print':
            echo $heap[$op['address']];
            break;
        case '.':
            echo chr($heap[$op['address']]);
            break;
        default:
            throw new \RuntimeException("Invalid operation {$op['op']} at $ip");
            break;
    }
}

echo "\n";