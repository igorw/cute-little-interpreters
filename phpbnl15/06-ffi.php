<?php

$code = '
	0 12 -
	ffi(abs)
';

$ops = preg_split('/\s+/', $code, -1, PREG_SPLIT_NO_EMPTY);

$labels = [];
foreach ($ops as $ip => $op) {
	if (preg_match('/^label\((.+)\)$/', $op, $match)) {
		$label = $match[1];
		$labels[$label] = $ip;
	}
}

$ip = 0;
$stack = new SplStack();
$calls = new SplStack();

while ($ip < count($ops)) {
	$op = $ops[$ip++];

	if (is_numeric($op)) {
		$stack->push((int) $op);
		continue;
	}

	if (preg_match('/^ffi\((.+)\)$/', $op, $match)) {
		$fn = $match[1];
		$stack->push($fn($stack->pop()));
		continue;
	}

	if (preg_match('/^jmp\((.+)\)$/', $op, $match)) {
		$label = $match[1];
		$ip = $labels[$label];
		continue;
	}

	if (preg_match('/^je\((.+)\)$/', $op, $match)) {
		$label = $match[1];
		$b = $stack->pop();
		$a = $stack->pop();
		if ($a === $b) {
			$ip = $labels[$label];
		}
		continue;
	}

	if (preg_match('/^jnz\((.+)\)$/', $op, $match)) {
		$label = $match[1];
		if ($stack->pop() !== 0) {
			$ip = $labels[$label];
		}
		continue;
	}

	if (preg_match('/^call\((.+)\)$/', $op, $match)) {
		$label = $match[1];
		$calls->push($ip);
		$ip = $labels[$label];
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
			throw new InvalidArgumentException("Undefined instruction: $op");
			break;
	}
}

var_dump(array_reverse(iterator_to_array($stack)));
