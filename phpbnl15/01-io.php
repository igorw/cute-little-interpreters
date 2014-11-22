<?php

$code = preg_split('/\s/', '10 100 108 114 111 119 32 44 111 108 108 101 104 . . . . . . . . . . . . .');

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
		case '.':
			echo chr($stack->pop());
			break;
	}
}

// var_dump(array_reverse(iterator_to_array($stack)));
