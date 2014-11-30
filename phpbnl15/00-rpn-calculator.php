<?php

$code = '1 2 +';
$ops = preg_split('/\s/', $code);

$stack = new SplStack();

foreach ($ops as $op) {
	if (is_numeric($op)) {
		$stack->push((int) $op);
		continue;
	}

	switch ($op) {
		case '+':
			$b = $stack->pop();
			$a = $stack->pop();
			$stack->push($a + $b);
			break;
		case '-':
			$b = $stack->pop();
			$a = $stack->pop();
			$stack->push($a - $b);
			break;
	}
}

var_dump($stack->top());
// var_dump(array_reverse(iterator_to_array($stack)));
