<?php

// DEBUGGING

// LOL ever had a program that did not do what you expected it to?
// you were wrong
// the computer is always right

// interpreters are loops with switch statements in them
// loops are notoriously hard to debug

// ever had a loop or a recursion go in an infinite loop?
// this is because loops are notoriously difficult

// how do you debug such a program?

// LOL PRINT STATEMENTS
// debugger, step through
// look at instruction and stack

$code = '1 2 + 3 4 + +';
$ops = explode(' ', $code);

$stack = [];

// look! $ip is the index into the ops
// it's the instruction pointer!
foreach ($ops as $ip => $op) {
    echo "$ip:\t$op\t".json_encode($stack)."\n";

    if (is_numeric($op)) {
        array_push($stack, (int) $op);
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
        // default case for missing instructions!
        default:
            throw new \RuntimeException("Invalid operation $op at $ip");
            break;
    }
}

// var_dump($stack);
var_dump(array_pop($stack));
