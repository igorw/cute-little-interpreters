<?php

$code = '
	0 10 100 108 114 111 119 32 44 111 108 108 101 104
	label(print_char)
		.
		dup
		jnz(print_char)
';

$ops = preg_split('/\s/', $code, -1, PREG_SPLIT_NO_EMPTY);

$labels = [];
foreach ($ops as $ip => $op) {
	if (preg_match('/^label\((.+)\)$/', $op, $match)) {
		$label = $match[1];
		$labels[$label] = $ip;
	}
}

$ip = 0;
$stack = new SplStack();

while ($ip < count($ops)) {
	$op = $ops[$ip++];

	if (is_numeric($op)) {
		$stack->push((int) $op);
		continue;
	}

	if (preg_match('/^jnz\((.+)\)$/', $op, $match)) {
		$label = $match[1];
		if ($stack->pop() !== 0) {
			$ip = $labels[$label];
		}
		continue;
	}

	if (preg_match('/^label\((.+)\)$/', $op, $match)) {
		// noop
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
		case '.':
			echo chr($stack->pop());
			break;
		case 'dup':
			$stack->push($stack->top());
			break;
		default:
			throw new InvalidArgumentException("Undefined instruction: $op");
			break;
	}
}

// var_dump(array_reverse(iterator_to_array($stack)));
