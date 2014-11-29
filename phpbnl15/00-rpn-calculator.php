<?php

$ops = preg_split('/\s/', '1 2 +');

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
