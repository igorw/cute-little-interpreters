<?php

// BUT COMPUTERS DON'T JUST ADD NUMBERS

// this RPN expression really is a program
// that consists of instructions for the computer

// when we write programs, what kind of stuff do we want to do?
// => output stuff
// => not just stuff, but text

// ok cool story, how do we make text from numbers
// computers store everything in numbers
// strings in C are arrays of numbers
// each element is one character

// there is a way to translate from number representation to chars
// ASCII

// \n => 10
// space => 32
// 0-9 => 48-57
// A-Z => 65-90
// a-z => 97-122

// PHP chr() function converts from ASCII to character

// ...

// pitfall: pop in wrong order

// whoops, popped in wrong order
// $code = '72 73 10 . . .';

$code = '72 . 73 . 10 .';
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
	}
}

// var_dump($stack);
