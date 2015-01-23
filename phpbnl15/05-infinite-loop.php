<?php

// LABELS

// numeric jumps are really hard to get right
// be it off-by 1, off-by 2 or in this case off by -3 errors
// are common

// what really helps is to give names to memory locations
// that's basically what variables are
// but we will name indexes in the operations

// we do that using labels
// labels are just an assoc array from names to offsets in $ops

// this way, we can jump to a named location instead of
// having to use -3 or some other strange number

// (we have labels in PHP but don't tell anyone (thanks sara))

// we introduce two new instructions:
// * label:<name>
// * jump:<name>

// label declares a label
// jump will jump back to that label

// JUMP == GOTO deal with it


// stack overflow => memory exhaustion
// $code = 'label:x 1 jump:x';

// jump forward... fails
// $code = 'jump:x 1 label:x';

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
        default:
            throw new \RuntimeException("Invalid operation $op at $ip");
            break;
    }
}

// var_dump($stack);
var_dump(array_pop($stack));
