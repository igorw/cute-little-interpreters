# cute little interpreters

outline
=======

* ROBOTS! IN YOUR COMPUTER!
* what the hell is a stack
* RPN calculator for funsies
* extend it with I/O
* extend it with control flow
* OMG we added conditional jump and now we have a language
* MOAR FEATURES!
    * increment
    * equals, greater than, less than
    * if, while, negation
    * exit
    * procedure calls
    * memory registers
    * FFI (arbitrary host function calls)
* write programs!

* extra fun:
    * compiling to RPN (shunting yard or parser -- or direct AST)
    * brainfuck, befunge, whitespace, unlambda
    * rewrite in functional style

resources
=========

general
-------

* igor.io
* github.com/igorw
  (chicken, edn, rpn, conway, brainfuck,
   turing, automata, lambda, whitespace,
   smaug)
* speakerdeck.com/igorw
* esolangs.org

writing programs
----------------

* hello world
* hello $name
* count to 10
* read two numbers, add them
* reverse a string

parsing
-------

* JSON
* XML (lol!)
* plain PHP arrays
* s-expressions
* custom text based

* shunting-yard
* parser combinator
* recursive descent
* compiler?

* github.com/jakubledl/dissect
* github.com/nikic/Phlexy

interpreting
------------

concepts:

* instructions
* instruction pointer
* stack

* paradigm?
* small-step operational semantics
* big-step operational semantics

* rpn calculator
* stack machine

* arithmetic
* i/o
* control flow
* ffi for interop

