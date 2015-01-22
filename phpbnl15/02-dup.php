<?php

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
