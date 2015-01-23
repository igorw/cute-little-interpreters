<?php

require 'vendor/autoload.php';

$code = <<<'CODE'
$a = 2;
while ($a < 10) {
    $a = $a + 1;
}
echo $a;
CODE;



$parser = new PhpParser\Parser(new PhpParser\Lexer);

$ast = $parser->parse('<?php ' . $code);

$state = [
    "variables" => [],
    "variableIndex" => 0,
    "ops" => [],
    "labels" => [],
    "labelJumps" => [],
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
        case '<':
            $heap[$op["result"]] = $heap[$op["left"]] < $heap[$op["right"]];
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
        case 'Expr_BinaryOp_Minus':
            $target = ++$state['variableIndex'];
            $left = compileNode($node->left, $state);

            $right = compileNode($node->right, $state);
            $state['ops'][] = ["op" => "-", "left" => $left, "right" => $right, "result" => $target];
            return $target;
        case 'Expr_BinaryOp_Plus':
            $target = ++$state['variableIndex'];
            $left = compileNode($node->left, $state);

            $right = compileNode($node->right, $state);
            $state['ops'][] = ["op" => "+", "left" => $left, "right" => $right, "result" => $target];
            return $target;
        case 'Expr_BinaryOp_Smaller':
            $target = ++$state['variableIndex'];
            $left = compileNode($node->left, $state);
            $right = compileNode($node->right, $state);
            $state['ops'][] = ["op" => "<", "left" => $left, "right" => $right, "result" => $target];
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
            if (isset($state['labels'][$node->name])) {
                $state['ops'][] = ["op" => "jump", "offset" => $state['labels'][$node->name]];
            } else {
                $state['labelJumps'][$node->name][] = count($state['ops']);
                // set a placeholder value that won't crash the program
                $state['ops'][] = ["op" => "jump", "offset" => PHP_INT_MAX];
            }
            break;
        case 'Stmt_Label':
            // set the label to point to the next statement
            $state['labels'][$node->name] = count($state['ops']);
            if (isset($state['labelJumps'][$node->name])) {
                // we had found jumps referencing this node before, update them
                foreach ($state['labelJumps'][$node->name] as $jumpidx) {
                    $state['ops'][$jumpidx]['offset'] = $state['labels'][$node->name];
                }
            }
            break;
        case 'Stmt_If':
            $cond = compileNode($node->cond, $state);
            // record where the jumpnz node goes so we can update it later
            $jumpidx = count($state['ops']);
            $state['ops'][] = ["op" => "jumpz", "address" => $cond, "offset" => PHP_INT_MAX];
            // compile all of the statements
            compile($node->stmts, $state);
            $closingidx = count($state['ops']);
            if ($node->else) {
                $endjumpidx = count($state['ops']);
                $state['ops'][] = ["op" => "jump", "offset" => PHP_INT_MAX];
                $closingidx = count($state['ops']);
                compile($node->else->stmts, $state);
                $state['ops'][$endjumpidx]["offset"] = count($state['ops']);
            }
            // finally update the jump index
            $state['ops'][$jumpidx]['offset'] = $closingidx;
            break;
        case 'Stmt_While':
            $condPos = count($state['ops']);
            $cond = compileNode($node->cond, $state);
            $jumpidx = count($state['ops']);
            $state['ops'][] = ["op" => "jumpz", "address" => $cond, "offset" => PHP_INT_MAX];
            compile($node->stmts, $state);
            $state['ops'][] = ["op" => "jump", "offset" => $condPos];
            $state['ops'][$jumpidx]['offset'] = count($state['ops']);
            break;
        default:
            throw new \RuntimeException("Unsupported node type found: " . $node->getType());
    }
}