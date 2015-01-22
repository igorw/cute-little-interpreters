<?php

$code = '
jump:main

label:prn
    dup .num .newline
    ret

label:main    
    10
    call:prn
    label:x
        1 -
        call:prn
        dup jumpnz:x
';
$ops = preg_split('/\s+/', trim($code));

$labels = [];

foreach ($ops as $ip => $op) {
    // look, it's copy-pasted from the other place
    if (strpos($op, ':') !== false) {
        list($command, $label) = explode(':', $op);
        switch ($command) {
            case 'label':
                $labels[$label] = $ip;
                break;
        }
        continue;
    }
}

$stack = [];
$ip = 0;

// new data structure
$calls = [];

while ($ip < count($ops)) {
    $op = $ops[$ip];
    $ip++;

    // echo "$ip:\t$op\t".json_encode($stack)."\n";

    if (is_numeric($op)) {
        array_push($stack, (int) $op);
        continue;
    }

    if (strpos($op, ':') !== false) {
        list($command, $label) = explode(':', $op);
        switch ($command) {
            case 'jump':
                $ip = $labels[$label];
                break;
            case 'jumpnz':
                $cond = array_pop($stack);
                if ($cond) {
                    $ip = $labels[$label];
                }
                break;
            // new instruction
            case 'call':
                array_push($calls, $ip);
                $ip = $labels[$label];
                break;
        }
        continue;
    }

    switch ($op) {
        case '+':
            $b = array_pop($stack);
            $a = array_pop($stack);
            array_push($stack, $a + $b);
            break;
        case '-':
            $b = array_pop($stack);
            $a = array_pop($stack);
            array_push($stack, $a - $b);
            break;
        case '.':
            echo chr(array_pop($stack));
            break;
        case '.num':
            echo array_pop($stack);
            break;
        case '.newline':
            echo "\n";
            break;
        case 'dup':
            $top = array_pop($stack);
            array_push($stack, $top);
            array_push($stack, $top);
            break;
        // new instruction
        case 'ret':
            $ip = array_pop($calls);
            break;
        default:
            throw new \RuntimeException("Invalid operation $op at $ip");
            break;
    }
}

// var_dump($stack);
var_dump(array_pop($stack));
