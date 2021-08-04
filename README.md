PHP Grammar
=

A simple library to make Lexer and Parsers to build a language.

## Content

* [What is a Token](#what-is-a-token)
* [What is a Lexer](#what-is-a-lexer)
* [What is a Parser](#what-is-a-parser)
* [Installation](#installation)
* [Usage](#usage)
* [Check also](#check-also)


### What is a Token

A token is the most atomic piece of your language. It could be a single character in an specific alphabet A or a composition of some of them.

### What is a Lexer

Lexer is the responsible of parsing the raw text and tokenize it for a Parser, which will interpret them using a grammar.

Example: Supose we have the following text:
```
$library = require('path/to/lib');
```

Lets say our language has variables, functions and some else.

A result of applying a parser will generate the following tokens:

* **T_VAR < library >** A variable identifier called 'library'
* **T_ASIGN < = >** A single character which represents an asignation from right to left
* **T_FUNC < require >** A function name. the language should recongize the function and execute it.
* **T_FUNC_ARG < 'path/to/lib' >** A function argument passed to the previous function.
* **T_ENDLINE <;>** A single character which represents and end of line

With that tokens then the Parser should identify in its grammar a properly syntax for the language you are building.


### What is a Parser

A parser or grammar is the responsible of interpret the tokens generated by the Lexer and evaluate them identifying the correct syntax of your language you are builing.

Also we call the grammar, Context Free Grammar. More info here [Context Free Grammar - Wikipedia](https://en.wikipedia.org/wiki/Context-free_grammar)


To program a grammar, we are gona use the [Chomsky formal form](https://en.wikipedia.org/wiki/Chomsky_normal_form) since we must have well defined rules for our grammar and also most important, **we are gonna use the [BNF - Backus-Naur Form](https://en.wikipedia.org/wiki/Backus%E2%80%93Naur_form)** to define each rules.



### Installation

```php
composer require didix16/php-grammar
```

### Usage

I'm gonna show a simple usage for build a language which recognise a supe simple script to instruct some API do something like GET url and process it.

Let's gonna call it APIScript and should use APILexer and APIParser.

A simple script would have a structure like this:

```
GET https://some-awesome-api.com/endpoint?token=TOKEN
PIPETO $mySuperService
SAVEDB localhost:10000
```

We can identify lines, which each line is an action.
Each action has a keyname, a space and a param ( it could be a list of params)

So lets say we have to identify tokens like:

T_ACTION, T_WHITESPACE, T_ARG and T_NEWLINE

```php
<?php

// APILexer.php

use didix16\Grammar\Lexer;
use didix16\Grammar\Token;

class APILexer extends Lexer {

    /**
     * Token identifiers
     */
    const T_ACTION = 2;
    const T_WHITESPACE = 3;
    const T_ARG = 4;
    const T_NEWLINE = 5;

    /**
     * For the sake of tokenization, its necessary to make a general regular expression
     * to identify the tokens inside the raw text.
     */
    const REG_TOKEN = '/([A-Za-z_][A-Za-z0-9_\-]*)(?= )|( )|([^ \n\r]+)|(\n?)/';

    /**
     * These are the string representation of each token. It is using the token ID
     * to get the name so, the order matters and should be the same you specified
     * on Token identifiers.
     * 
     * Index 0 and 1 should be allways "n/a" for 0 and <EOF> for 1.
     */
    static $tokenNames = ["n/a", "<EOF>", "T_ACTION", "T_WHITESPACE", "T_ARG", "T_NEWLINE"];

    /**
     * These are the regular expressions that identifies each token.
     * They will be used to compare against the next word found by REG_TOKEN and must
     * check if is one of our language tokens.
     */
    const REG_T_ACTION = '/^[a-zA-Z_][a-zA-Z0-9_]*$/';
    const REG_T_WHITESPACE = '/^ $/';
    const REG_T_ARG = '/^([^ \n]+)$/';
    const REG_T_NEWLINE = '/^(\n)$/'
    


    /**
     * Returns the next word is ahead current lexer pointer
     * @return string
     */
    protected function lookahead(): string
    {
        $word = self::LAMBDA;
        if (0 != preg_match(self::REG_TOKEN, $this->input, $matches, PREG_OFFSET_CAPTURE, $this->p + strlen($this->word))){
            $word = $matches[0][0];
        }
        return $word;
    }

    /**
     * From input string, consume a word and advance the internal pointer to the next word
     */
    public function consume(): Lexer
    {
        if (0 != preg_match(self::REG_TOKEN, $this->input, $matches, PREG_OFFSET_CAPTURE, $this->p)){

            $this->word = $matches[0][0];
            $this->p = $matches[0][1] + strlen($this->word);
        } else {
            $this->word = self::LAMBDA;
        }

        return $this;
    }

    /**
     * Check if word $text is an action
     */
    protected function isAction($text){

        return 1 === preg_match(self::REG_T_ACTION, $text);
    }

    /**
     * Check if word $text is a whitespace character
     */
    protected function isWhitespace($text){
        
        return 1 === preg_match(self::REG_T_WHITESPACE, $text);
    }

    /**
     * Check if word $text is an argument
     */
    protected function isArg($text){
        
        return 1 === preg_match(self::REG_T_ARG, $text);
    }

    /**
     * Check if word $text is a new line character
     */
    protected function isNewLine($text){

        return 1 === preg_match(self::REG_T_NEWLINE, $text);
    }

    /**
     * Returns the next token identified by this lexer
     * @return Token
     * @throws \Exception
     */
    public function nextToken(): Token {

        // current word being processed
        $word = $this->word;
        if ($this->word != self::LAMBDA) {
            
            // action and arg are very similiar. We have to differentiate them
            if (
                $this->isAction($this->word) ||
                $this->isArg($this->word)
            ){
                
                $lastTokenType = $this->lastToken()->getType();

                $this->consume();

                // if last token was a whitspace then we are on an arg token
                $type = $lastTokenType !== self::T_WHITESPACE ? self::T_ACTION : self::T_ARG;

                return $this->returnToken($type, $word);

                
            } else 
            if ($this->isWhitespace($this->word)) {

                $this->consume();
                return $this->returnToken(self::T_WHITESPACE, $word);
            } else 
            if ($this->isNewLine($this->word)) {

                $this->consume();
                return $this->returnToken(self::T_NEWLINE, $word);
            }
            
            else {
                throw new \Exception("Invalid symbol [" . $word . "]");
            }


        }

        return $this->returnToken(self::EOF_TYPE, self::$tokenNames[1]);
    }

    /**
     * Given a token type, returns its token name representation
     * @return string
     */
    public function getTokenName($tokenType): string {

        return APILexer::$tokenNames[$tokenType];
    }
}
```

We are gonna define our Grammar now. Take a look on how to apply the BNF into a programmary form.

I assume that APILexer and APIParser are on the same directory.

```php
<?php

// APIParser.php

use didix16\Grammar\Lexer;
use didix16\Grammar\Parser;

/**
 * Grammar: S (Syntax) is the entrypoint
 * ------------------------------------------------------
 * S            := LineList
 * Line         := Action Whitespace Argument
 * LineList     := Line [NewLine LineList]
 * Action       := /^[a-zA-Z_][a-zA-Z0-9_]*$/
 * Argument     := /^[^ \n]+/
 * Whitespace   := /^ $/
 * NewLine      := /^\n$/
 */
class APIParser extends Parser {

    /**
     * Line := Action Whitespace Argument
     */
    public function line(){

        $this->action();

        $this->whitespace();

        $this->argument();

    }

    /**
     * LineList := Line [NewLine LineList]
     */
    public function lineList(){

        $this->line();

        if ( $this->lookahead->getType() === APILexer::T_NEWLINE ){

            $this->newLine();
            $this->lineList();
        }

    }

    /**
     * Action := ^[a-zA-Z_][a-zA-Z0-9_]*$
     */
    public function action(){

        $this->match(APILexer::T_ACTION);
    }

    /**
     * Argument := /^[^ \n]+/
     */
    public function argument(){

        $this->match(APILexer::T_ARG);
    }

    /**
     * Whitespace := /^ $/
     */
    public function whitespace(){

        $this->match(APILexer::T_WHITESPACE);
    }

    /**
     * NewLine := ^\n$
     */
    public function newLine(){

        $this->match(APILexer::T_NEWLINE);
    }

    /**
     * S := LineList
     */
    public function parse(): array {

        $this->lineList();
        $this->match(Lexer::EOF_TYPE);

        return $this->getTokens();
    }
}

```

Finally lets do a simple code example

```php

$script = "GET https://some-awesome-api.com/endpoint?token=TOKEN
PIPETO $mySuperService
SAVEDB localhost:10000";


$lexer = new APILexer($script);
$parser = new APIParser($lexer);

$tokens = $parser->parse();

// var_dump($tokens) should be:

array(12) {
  [0]=>
  object(Token)#4 (2) {
    ["value":protected]=>
    string(3) "GET"
    ["type":protected]=>
    int(2)
  }
  [1]=>
  object(Token)#2 (2) {
    ["value":protected]=>
    string(1) " "
    ["type":protected]=>
    int(3)
  }
  [2]=>
  object(Token)#5 (2) {
    ["value":protected]=>
    string(49) "https://some-awesome-api.com/endpoint?token=TOKEN"
    ["type":protected]=>
    int(4)
  }
  [3]=>
  object(Token)#6 (2) {
    ["value":protected]=>
    string(1) "
"
    ["type":protected]=>
    int(5)
  }
  [4]=>
  object(Token)#7 (2) {
    ["value":protected]=>
    string(6) "PIPETO"
    ["type":protected]=>
    int(2)
  }
  [5]=>
  object(Token)#8 (2) {
    ["value":protected]=>
    string(1) " "
    ["type":protected]=>
    int(3)
  }
  [6]=>
  object(Token)#9 (2) {
    ["value":protected]=>
    string(15) "$mySuperService"
    ["type":protected]=>
    int(4)
  }
  [7]=>
  object(Token)#10 (2) {
    ["value":protected]=>
    string(1) "
"
    ["type":protected]=>
    int(5)
  }
  [8]=>
  object(Token)#11 (2) {
    ["value":protected]=>
    string(6) "SAVEDB"
    ["type":protected]=>
    int(2)
  }
  [9]=>
  object(Token)#12 (2) {
    ["value":protected]=>
    string(1) " "
    ["type":protected]=>
    int(3)
  }
  [10]=>
  object(Token)#13 (2) {
    ["value":protected]=>
    string(15) "localhost:10000"
    ["type":protected]=>
    int(4)
  }
  [11]=>
  object(Token)#14 (2) {
    ["value":protected]=>
    string(5) "<EOF>"
    ["type":protected]=>
    int(1)
  }
}
```

Now, with the parsed tokens you can do anything that evaluates the
actions with its arguments.

For example, you may have an Interpreter that evaluates the tokens and do the corresponding actions.

I.e, GET may use some library to make HTTP GET requests; PIPETO may pass the gathered data to some of your system service and SAVEDB may save the result from PIPETO $mySuperService to the specified DDBB host.

### Check also

* [php-interpreter]() - An interpreter base made in PHP to parse your own Tokens.