<?php

namespace didix16\Grammar;

/**
 * Interface ParserInterface
 * @package didix16\Grammar
 */
interface ParserInterface {

    public function __construct(LexerInterface $input);

    /**
     * If token type passed matches the expected token type then consume next token
     * else throws an exception
     * @param $tokenType
     * @throws Exception
     */
    public function match($tokenType);

    /**
     * Consume current token and store it to token list
     */
    public function consume();

    /**
     * Get all the evaluated tokens
     * @return array
     */
    public function getTokens();

    /**
     * The main entry point of the grammar expression
     * Returns the list of tokens founded
     * @return array
     */
    public function parse() : array ;
}