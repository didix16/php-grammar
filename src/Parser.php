<?php


namespace didix16\Grammar;
use Exception;

/**
 * Class Parser
 * @package didix16\Grammar
 */
abstract class Parser {

    /**
     * The lexer from we get the tokens
     * @var Lexer
     */
    protected $input;

    /**
     * Current lookahead Token
     * @var Token
     */
    protected $lookahead;

    /**
     * Stores the processed tokens
     * @var array
     */
    protected $tokenList = [];

    public function __construct(Lexer $input)
    {
        $this->input = $input;
        $this->lookahead = $this->input->nextToken();
    }

    /**
     * If token type passed matches the expected token type then consume next token
     * else throws an exception
     * @param $tokenType
     * @throws Exception
     */
    public function match($tokenType){

        if ($this->lookahead->getType() === $tokenType){
            $this->consume();
        } else {
            throw new Exception(
                "Expecting token: [" . $this->input->getTokenName($tokenType) . "]\n
                Found: [" . $this->input->getTokenName($this->lookahead->getType()) . "]"
            );
        }

    }

    /**
     * Consume current token and store it to token list
     */
    public function consume(){
        $this->tokenList[] = $this->lookahead;
        $this->lookahead = $this->input->nextToken();
    }

    /**
     * Get all the evaluated tokens
     * @return array
     */
    public function getTokens(){

        return $this->tokenList;
    }

    /**
     * The main entry point of the grammar expression
     * Returns the list of tokens founded
     * @return array
     */
    public abstract function parse() : array ;

}