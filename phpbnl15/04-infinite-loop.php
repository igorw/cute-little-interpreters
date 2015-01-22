<?php

// stack overflow => memory exhaustion
// $code = 'label:x 1 jump:x';

$code = '1 label:x 1 + jump:x';
$ops = explode(' ', $code);

// some labels hereeeeee
$labels = [];

$stack = [];
$ip = 0;

while ($ip < count($ops)) {
    $op = $ops[$ip];
    $ip++;

    echo "$ip:\t$op\t".json_encode($stack)."\n";

    if (is_numeric($op)) {
        array_push($stack, (int) $op);
        continue;
    }

    // strpos(haystack, needle)
    if (strpos($op, ':') !== false) {
        list($command, $label) = explode(':', $op);
        switch ($command) {
            // LOOK AT THE DUALITY
            case 'label':
                $labels[$label] = $ip;
                break;
            case 'jump':
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
        case 'dup':
            $top = array_pop($stack);
            array_push($stack, $top);
            array_push($stack, $top);
            break;
    }
}

// var_dump($stack);
var_dump(array_pop($stack));
