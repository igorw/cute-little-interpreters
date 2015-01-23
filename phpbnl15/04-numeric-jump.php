<?php

// TURING MACHINES OMG

// ALAN FUCKING TURING
// pretty much invented theoretical computer science in 1936
// built some of the first computers in manchester
// helped the polish crack the enigma code and destroy the nazi empire

// wrote a paper about turing machines
// ok he did not call them that
// hilbert problems
// decidability
// etc.

// remember infinite loops?

// THE HALTING PROBLEM

// limiting factor
// limitless factor!!!
// systems that emulate each other

// church-turing thesis

$code = '1 1 + -4 jump';
$ops = explode(' ', $code);

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
        // new instruction
        case 'jump':
            $offset = array_pop($stack);
            $ip += $offset;
            break;
        default:
            throw new \RuntimeException("Invalid operation $op at $ip");
            break;
    }
}

// var_dump($stack);
var_dump(array_pop($stack));
