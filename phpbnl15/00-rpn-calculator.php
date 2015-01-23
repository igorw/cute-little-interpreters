<?php

// INTRO

// * Ask questions right away any time you have them
// * We will be pair programming
// * Pair up with someone who has a different experience leve than you

// RULES

// We have some rules at the stack machine workshop:
// * One single file.
// * No classes.
// * No well-actually’s.

// WHY

// * you use programming languages and computers every day
// * HOW DO THEY WORK!
// * whyyyyyy are computers so hard?
// * what even is a zend engine?
// * compilers!!!
// * working on different levels of abstraction aaaah
// * mental model for execution of programs that will help you debug

// ACTUAL REASONS

// * INTERPRETERS ARE SO MUCH FUN
// * COMPILERS ARE AMAZING
// * ALAN TURING ONE OF THE PIONEERS OF COMPUTER SCIENCE IS AMAZING
// * THEORETICAL COMPUTER SCIENCE: BEAUTIFUL

// SIMPLE COMPUTER

// * computer in german = rechner, it's a calculator
// * most early computer designs were calculation machines
// * though many pioneers envisoned much more that number shifting
// * so how to compute?
// [1 + 2] ; ok, but [1 - 2 * 3]
// * reverse-polish notation
// [1 2 +] ; [1 2 3 * -]
// * stack operations
// * MIT cafeteria trays => pushdown stack

// ...

// pitfall: minus in wrong order

// BASIC FRAMEWORK
// we code this together

// write down instruction set

$code = '1 2 +';
$ops = explode(' ', $code);

// var_dump($ops);

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
			// potential pitfall: reverse order
			$b = array_pop($stack);
			$a = array_pop($stack);
			array_push($stack, $a - $b);
			break;
	}
}

// var_dump($stack);
var_dump(array_pop($stack));
