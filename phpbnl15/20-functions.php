<?php

require 'vendor/autoload.php';

$code = <<<'CODE'
function id($a) {
    backtrace();
    return $a + 1;
}
function main() {
    echo id(123);
}
CODE;

$parser = new PhpParser\Parser(new PhpParser\Lexer);

$ast = $parser->parse('<?php ' . $code);

$state = [
    "functions" => [],
    "variables" => [],
    "variableIndex" => 0,
    "ops" => [],
    "labels" => [],
    "labelJumps" => [],
];

compileTop($ast, $state);

$functions = $state['functions'];

$stack = [];
$stack[] = new StackFrame("main", $functions['main'], 0);

while (end($stack)->ip < count(end($stack)->ops)) {
    $frame = end($stack);
    $op = $frame->ops[$frame->ip];
    $frame->ip++;

    echo "\n{$op['op']}\t".json_encode($frame)."\n";


    switch ($op['op']) {
        case 'assign':
            $frame->heap[$op['result']] = $frame->heap[$op['address']];
            break;
        case 'const':
            $frame->heap[$op["result"]] = $op["value"];
            break;
        case '+':
            $frame->heap[$op["result"]] = $frame->heap[$op["left"]] + $frame->heap[$op["right"]];
            break;
        case '-':
            $frame->heap[$op["result"]] = $frame->heap[$op["left"]] - $frame->heap[$op["right"]];
            break;
        case '<':
            $frame->heap[$op["result"]] = $frame->heap[$op["left"]] < $frame->heap[$op["right"]];
            break;
        case 'jump':
            $frame->ip = $op["offset"];
            break;
        case 'jumpz':
            if ($frame->heap[$op["address"]] == 0) {
                $frame->ip = $op["offset"];
            }
            break;
        case 'jumpnz':
            if ($frame->heap[$op["address"]] != 0) {
                $frame->ip = $op["offset"];
            }
            break;
        case 'print':
            echo $frame->heap[$op["address"]];
            break;
        case '.':
            echo chr($frame->heap[$op["address"]]);
            break;
        case 'return':
            $returnValue = $frame->heap[$op["address"]];
            array_pop($stack);
            end($stack)->heap[$frame->returnOffset] = $returnValue;
            break;
        case 'send':
            $frame->argStack[] = $frame->heap[$op["address"]];
            break;
        case 'funcCall':
            if ($op["name"] == "backtrace") {
                echo "\nBacktrace\n";
                foreach (array_reverse($stack) as $id => $subframe) {
                    echo "$id: Function {$subframe->functionName}(";
                    echo implode(",", $subframe->args);
                    echo "): {$subframe->ip}\n"; 
                }
                break;
            }
            $newFrame = new StackFrame($op["name"], $functions[$op["name"]], $op["result"]);
            $idx = 0;
            foreach ($frame->argStack as $arg) {
                $newFrame->args[++$idx] = $arg;
                $newFrame->heap[$idx] = $arg;
            }
            $frame->argStack = [];
            $stack[] = $newFrame;
            break;  
        default:
            throw new \RuntimeException("Invalid operation {$op['op']} at $frame->ip");
            break;
    }
}

echo "\n";

function compileTop(array $ast, array &$state) {
    foreach ($ast as $node) {
        if ($node->getType() != "Stmt_Function") {
            throw new \RuntimeException("Only functions allowed at the top level");
        }
        foreach ($node->params as $param) {
            $state['variables'][$param->name] = ++$state['variableIndex'];
        }
        compile($node->stmts, $state);
        $state['functions'][$node->name] = $state['ops'];
        $state['ops'] = [];
        $state['variables'] = [];
        $state['variableIndex'] = 0;
        $state['labels'] = [];
        $state['labelJumps'] = [];
    }
}

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
        case 'Expr_FuncCall':
            $target = ++$state['variableIndex'];
            foreach ($node->args as $arg) {
                $state['ops'][] = ["op" => "send", "address" => compileNode($arg->value, $state)];
            }
            $state['ops'][] = ["op" => "funcCall", "name" => (string) $node->name, "result" => $target];
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
        case 'Stmt_Return':
            $value = compileNode($node->expr, $state);
            $state['ops'][] = ["op" => "return", "address" => $value];
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

class StackFrame {
    public $functionName;
    public $ip = 0;
    public $heap = [];
    public $returnOffset;
    public $ops = [];
    public $argStack = [];
    public $args = [];

    public function __construct($name, array $ops, $returnOffset) {
        $this->functionName = $name;
        $this->ops = $ops;
        $this->returnOffset = $returnOffset;
    }
}