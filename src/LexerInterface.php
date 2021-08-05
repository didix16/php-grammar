<?php

namespace didix16\Grammar;

/**
 * Interface LexerInterface
 * @package didix16\Grammar
 */
interface LexerInterface {

    public function __construct(string $input);

    /**
     * From input string, consume a character by default.
     * This method may be override to consume a word or some regexp
     * @return $this
     */
    public function consume(): self;

    /**
     * Check if current character is an ASCII letter
     * @return bool
     */
    public function isLetter();

    /**
     * Consume contiguous white spaces
     */
    public function WS();

    /**
     * Returns the last character readed by this lexer
     * @return string
     */
    public function lastCharacter(): string;

    /**
     * Return the last token founded
     * @return Token
     */
    public function lastToken(): Token;

    /**
     * Returns the next token identified by this lexer
     * @return Token
     */
    public function nextToken(): Token;

    /**
     * Given a token type returns its token name
     * @param $tokenType
     * @return string
     */
    public function getTokenName($tokenType): string;
}