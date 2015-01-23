<?php

// we can combine computation with I/O!

// DUP
// duplicate the top element of the stack YO

// we can compute the message "Hi" by pushing
// 72 for H, duplicating, printing out H,
// incrementing the other H to I and then
// adding 32 to lowercase it

// strtolower()
// if char in range 65-90, add 32

// then print newline!

$code = '72 dup . 1 + 32 + . 10 .';
$ops = explode(' ', $code);

$stack = [];

foreach ($ops as $op) {
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
    }
}

// var_dump($stack);
