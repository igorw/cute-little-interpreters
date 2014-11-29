<?php

$code = '
	jmp(start)

	label(fib)
		# pop n
		!var(n)

		var(n) 0 je(fib_n)
		var(n) 1 je(fib_n)

		# fib(n-1) + fib(n-2)
		var(n) 1 - call(fib)
		var(n) 2 - call(fib)
		+

		ret

		label(fib_n)
			var(n)
			ret

	label(start)
		0 !var(i)

		label(loop)
			var(i) .num 32 .
			var(i) call(fib) .num 10 .

			# i++
			var(i) 1 + !var(i)
			var(i) 10 - jnz(loop)
';

$ops = preg_split('/\s/', preg_replace('/^\s*#.*$/m', '', $code), -1, PREG_SPLIT_NO_EMPTY);

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
$vars = [];

while ($ip < count($ops)) {
	$op = $ops[$ip++];

	if (is_numeric($op)) {
		$stack->push((int) $op);
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
		$calls->push([$ip, $vars]);
		$ip = $labels[$label];
		continue;
	}

	if (preg_match('/^label\((.+)\)$/', $op, $match)) {
		// noop
		continue;
	}

	if (preg_match('/^var\((.+)\)$/', $op, $match)) {
		$var = $match[1];
		$stack->push($vars[$var]);
		continue;
	}

	if (preg_match('/^!var\((.+)\)$/', $op, $match)) {
		$var = $match[1];
		$vars[$var] = $stack->pop();
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
		case '.num':
			echo $stack->pop();
			break;
		case 'dup':
			$stack->push($stack->top());
			break;
		case 'ret':
			list($ip, $vars) = $calls->pop();
			break;
		default:
			throw new InvalidArgumentException("Undefined instruction: $op");
			break;
	}
}

var_dump(array_reverse(iterator_to_array($stack)));
