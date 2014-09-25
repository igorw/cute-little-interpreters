<?hh
 
// examples:
//
//   ~$ echo '1 jnz(:start) :foo call(:bar) ret :bar 42 print ret :start call(:foo)' | hhvm vm.php
//   float(42)
//
//   ~$ echo '3 :loop 1 - print jnz(:loop)' | hhvm vm.php
//   float(2)
//   float(1)
//   float(0)
 
class InvalidInstructionException extends Exception {
}
 
function tokenize($input) {
    return preg_split('/\s/', trim($input));
}
 
$tokens = tokenize(stream_get_contents(STDIN));
 
$labels = [];
foreach ($tokens as $ip => $inst) {
    if (preg_match('/^:(\w+)$/', $inst, $matches)) {
        $label = $matches[1];
        $labels[$label] = $ip;
        continue;
    }
}
 
$ip = 0;
$stack = new \SplStack();
$calls = new \SplStack();
 
while ($ip < count($tokens)) {
    $inst = $tokens[$ip++];
 
    if (is_numeric($inst)) {
        $stack->push((float) $inst);
        continue;
    }
    if (preg_match('/^:(\w+)$/', $inst, $matches)) {
        // label noop
        continue;
    }
    if (preg_match('/^jnz\\(:(\w+)\\)$/', $inst, $matches)) {
        $a = $stack->top();
        if ($a !== 0.0) {
            $label = $matches[1];
            $ip = $labels[$label];
        }
        continue;
    }
    if (preg_match('/^call\\(:(\w+)\\)$/', $inst, $matches)) {
        $calls->push($ip);
        $label = $matches[1];
        $ip = $labels[$label];
        continue;
    }
    switch ($inst) {
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
        case 'ret':
            $addr = $calls->pop($ip);
            $ip = $addr;
            break;
        case 'print':
            var_dump($stack->top());
            break;
        case 'debug':
            var_dump(iterator_to_array($stack));
            break;
        default:
            throw new InvalidInstructionException($inst);
            break;
    }
}
