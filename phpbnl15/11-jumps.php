<?php

// $a = 1;
// $b = 10;
// $c = 5;
// if ($c != 0) {
//     $c = $a + $b;
// }
// echo $c;
// echo "\n";

$ops = [
    ["op" => 'const',   "value" => 1,   "result" => 1],
    ["op" => 'const',   "value" => 10,  "result" => 2],
    ["op" => 'const',   "value" => 5,   "result" => 3],
    ["op" => 'jump',    "offset" => 5],
    ["op" => '+',       "left" => 1,    "right" => 2,   "result" => 3],
    ["op" => 'print',   "address" => 3],
    ["op" => 'const',   "value" => 10,  "result" => 4],
    ["op" => '.',       "address" => 4],
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
            $heap[$op["result"]] = $op["value"];
            break;
        case '+':
            $heap[$op["result"]] = $heap[$op["left"]] + $heap[$op["right"]];
            break;
        case '-':
            $heap[$op["result"]] = $heap[$op["left"]] - $heap[$op["right"]];
            break;
        case 'jump':
            $ip = $op["offset"];
            break;
        case 'jumpz':
            if ($heap[$op["address"]] == 0) {
                $ip = $op["offset"];
            }
            break;
        case 'jumpnz':
            if ($heap[$op["address"]] != 0) {
                $ip = $op["offset"];
            }
            break;
        case 'print':
            echo $heap[$op["address"]];
            break;
        case '.':
            echo chr($heap[$op["address"]]);
            break;
        default:
            throw new \RuntimeException("Invalid operation {$op['op']} at $ip");
            break;
    }
}

echo "\n";