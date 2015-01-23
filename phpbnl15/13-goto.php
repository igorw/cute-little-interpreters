<?php

require 'vendor/autoload.php';

$code = <<<'CODE'
$a = 1;
b:
echo $a;
goto b;
CODE;

// Note that we're only supporting backwards jumps right now.


$parser = new PhpParser\Parser(new PhpParser\Lexer);

$ast = $parser->parse('<?php ' . $code);

$state = [
    "variables" => [],
    "variableIndex" => 0,
    "ops" => [],
    "labels" => [],
];

compile($ast, $state);

$ops = $state['ops'];

$heap = [];
$ip = 0;

while ($ip < count($ops)) {
    $op = $ops[$ip];
    $ip++;

    echo "\n$ip:\t{$op['op']}\t".json_encode($heap)."\n";


    switch ($op['op']) {
        case 'assign':
            $heap[$op['result']] = $heap[$op['address']];
            break;
        case 'const':
            $heap[$op["result"]] = $op["value"];
            break;
        case '+':
            $heap[$op["result"]] = $heap[$op["left"]] + $heap[$op["right"]];
            break;
        case '-':
            $heap[$op["result"]] = $heap[$op["left"]] - $heap[$op["right"]];
            break;
        case 'jump':
            $ip = $op["offset"];
            break;
        case 'jumpz':
            if ($heap[$op["address"]] == 0) {
                $ip = $op["offset"];
            }
            break;
        case 'jumpnz':
            if ($heap[$op["address"]] != 0) {
                $ip = $op["offset"];
            }
            break;
        case 'print':
            echo $heap[$op["address"]];
            break;
        case '.':
            echo chr($heap[$op["address"]]);
            break;
        default:
            throw new \RuntimeException("Invalid operation {$op['op']} at $ip");
            break;
    }
}

echo "\n";

function compile(array $ast, array &$state) {
    foreach ($ast as $node) {
        compileNode($node, $state);
    }
}

function compileNode(PhpParser\Node $node, array &$state) {
    switch ($node->getType()) {
        case 'Expr_Assign':
            $target = compileNode($node->var, $state);
            $value = compileNode($node->expr, $state);
            $state['ops'][] = ["op" => "assign", "address" => $value, "result" => $target];
            return $target;
        case 'Expr_BinaryOp_Plus':
            $target = ++$state['variableIndex'];
            $left = compileNode($node->left, $state);

            $right = compileNode($node->right, $state);
            $state['ops'][] = ["op" => "+", "left" => $left, "right" => $right, "result" => $target];
            return $target;
        case 'Expr_Variable':
            if (!isset($state['variables'][$node->name])) {
                $state['variables'][$node->name] = ++$state['variableIndex'];
            }
            return $state['variables'][$node->name];
        case 'Scalar_LNumber':
            $target = ++$state['variableIndex'];
            $state['ops'][] = ["op" => "const", "value" => $node->value, "result" => $target];
            return $target;
        case 'Stmt_Echo':
            foreach ($node->exprs as $expr) {
                $value = compileNode($expr, $state);
                $state['ops'][] = ["op" => "print", "address" => $value];
            }
            break;
        case 'Stmt_Goto':
            $state['ops'][] = ["op" => "jump", "offset" => $state['labels'][$node->name]];
            break;
        case 'Stmt_Label':
            // set the label to point to the next statement
            $state['labels'][$node->name] = count($state['ops']);
            break;
        default:
            throw new \RuntimeException("Unsupported node type found: " . $node->getType());
    }
}