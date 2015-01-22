<?php

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
