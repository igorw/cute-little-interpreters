<?php

$code = preg_split('/\s/', '
	0 10 100 108 114 111 119 32 44 111 108 108 101 104
	label(print_char)
		.
		dup
		jnz(print_char)
', -1, PREG_SPLIT_NO_EMPTY);

$labels = [];
foreach ($code as $ip => $instr) {
	if (preg_match('/^label\((.+)\)$/', $instr, $match)) {
		$label = $match[1];
		$labels[$label] = $ip;
	}
}

$ip = 0;
$stack = new SplStack();

while ($ip < count($code)) {
	$instr = $code[$ip++];

	if (is_numeric($instr)) {
		$stack->push((int) $instr);
		continue;
	}

	if (preg_match('/^jnz\((.+)\)$/', $instr, $match)) {
		$label = $match[1];
		if ($stack->pop() !== 0) {
			$ip = $labels[$label];
		}
		continue;
	}

	if (preg_match('/^label\((.+)\)$/', $instr, $match)) {
		// noop
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
		case 'dup':
			$stack->push($stack->top());
			break;
		default:
			throw new InvalidArgumentException("Undefined instruction: $instr");
			break;
	}
}

// var_dump(array_reverse(iterator_to_array($stack)));
