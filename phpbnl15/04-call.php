<?php

$code = preg_split('/\s+/', '
	jmp(start)

	label(fib)
		dup 0 je(fib_zero)
		dup 1 je(fib_one)

		dup 1 -
		call(fib)
		swap 2 -
		call(fib)
		+

		ret
	label(fib_zero)
		ret
	label(fib_one)
		ret

	label(start)
		15 call(fib)
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
$calls = new SplStack();

while ($ip < count($code)) {
	$instr = $code[$ip++];

	if (is_numeric($instr)) {
		$stack->push((int) $instr);
		continue;
	}

	if (preg_match('/^jmp\((.+)\)$/', $instr, $match)) {
		$label = $match[1];
		$ip = $labels[$label];
		continue;
	}

	if (preg_match('/^je\((.+)\)$/', $instr, $match)) {
		$label = $match[1];
		$b = $stack->pop();
		$a = $stack->pop();
		if ($a === $b) {
			$ip = $labels[$label];
		}
		continue;
	}

	if (preg_match('/^jnz\((.+)\)$/', $instr, $match)) {
		$label = $match[1];
		if ($stack->pop() !== 0) {
			$ip = $labels[$label];
		}
		continue;
	}

	if (preg_match('/^call\((.+)\)$/', $instr, $match)) {
		$label = $match[1];
		$calls->push($ip);
		$ip = $labels[$label];
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
		case 'swap':
			$b = $stack->pop();
			$a = $stack->pop();
			$stack->push($b);
			$stack->push($a);
			break;
		case 'ret':
			$ip = $calls->pop();
			break;
		default:
			throw new InvalidArgumentException("Undefined instruction: $instr");
			break;
	}
}

var_dump(array_reverse(iterator_to_array($stack)));
