<?php

$code = preg_split('/\s/', '1 2 +');

$stack = new SplStack();

foreach ($code as $instr) {
	if (is_numeric($instr)) {
		$stack->push((int) $instr);
		continue;
	}

	switch ($instr) {
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
