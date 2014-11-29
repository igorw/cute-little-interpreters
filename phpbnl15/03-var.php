<?php

$code = preg_split('/\s/', preg_replace('/^\s*#.*$/m', '', '
	# define vars
	0 !var(i)
	0 !var(p)
	1 !var(n)
	0 !var(tmp)

	# output i prev
	var(i) .num 32 .
	var(p) .num 10 .

	# output i n
	var(i) .num 32 .
	var(n) .num 10 .

	label(next)

	# tmp = n + p
	# p = n
	# n = tmp
	var(p) var(n) + !var(tmp)
	var(n) !var(p)
	var(tmp) !var(n)

	# output i n
	var(i) .num 32 .
	var(n) .num 10 .

	# i++
	var(i) 1 + !var(i)

	jmp(next)
'), -1, PREG_SPLIT_NO_EMPTY);

$labels = [];
foreach ($code as $ip => $instr) {
	if (preg_match('/^label\((.+)\)$/', $instr, $match)) {
		$label = $match[1];
		$labels[$label] = $ip;
	}
}

$ip = 0;
$stack = new SplStack();
$vars = [];

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

	if (preg_match('/^var\((.+)\)$/', $instr, $match)) {
		$var = $match[1];
		$stack->push($vars[$var]);
		continue;
	}

	if (preg_match('/^!var\((.+)\)$/', $instr, $match)) {
		$var = $match[1];
		$vars[$var] = $stack->pop();
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
		case '.num':
			echo $stack->pop();
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
