<?php

// compiler:
// source => AST => bytecode => VM

// instruction set:
// * store val addr
// * assign from_addr to_addr
// * add a b c
// * print_num addr
// * jump ip
// * jumpz cond_addr ip

$code = '
    $a = 1;
    $b = 2;
    $c = $a + $b;
';
$ops = compile($code);

// $ops = [
//     ['store', 1, 0],
//     ['assign', 0, 1],
//     ['store', 2, 2],
//     ['assign, 2, 3'],
//     ['add', 1, 3, 4],
//     ['assign', 4, 5],
// ];
