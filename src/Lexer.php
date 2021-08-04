<?php


namespace didix16\Grammar;

/**
 * Class Lexer
 * @package didix16\Grammar
 */
abstract class Lexer {

    const NULL_TOKEN_TYPE = Token::NULL_TOKEN_VALUE;
    const NULL_TOKEN_VALUE = Token::NULL_TOKEN_TYPE;
    /**
     * End of file char
     */
    const EOF = -1;

    /**
     * Empty string
     */
    const LAMBDA = '';

    /**
     * EOF token type
     */
    const EOF_TYPE = 1;
    /**
     * EOF token value
     */
    const EOF_VALUE = '<EOF>';

    /**
     * Input string
     * @var string
     */
    protected $input;

    /**
     * Index of input current character
     * @var int
     */
    protected $p = 0;

    /**
     * Current processing char
     * @var string
     */
    protected $c;
    /**
     * Last char that has been processed
     * @var
     */
    protected $lastChar;

    /**
     * Current processing word
     * @var string
     */
    protected $word;

    /**
     * Last token that has been obtained by this lexer
     * @var Token
     */
    protected $lastToken;

    public function __construct(string $input)
    {
        $this->lastToken = new Token(self::NULL_TOKEN_TYPE, self::NULL_TOKEN_VALUE);
        $this->input = $input;
        // call consume to stablish an initial value
        $this->consume();
    }

    /**
     * Returns the next word or char that is ahead of current lexer pointer
     * @return string
     */
    protected function lookahead(): string {

        if (($this->p + 1)>= strlen($this->input)) {
            return Lexer::EOF;
        }else{
            return substr($this->input, $this->p + 1, 1);
        }
    }

    /**
     * From input string, consume a character by default.
     * This method may be override to consume a word or some regexp
     * @return $this
     */
    public function consume(): self {

        $this->lastChar = $this->c;
        ++$this->p;
        if ($this->p >= strlen($this->input)) {
            $this->c = Lexer::EOF;
        }else{
            $this->c = substr($this->input, $this->p, 1);
        }

        return $this;
    }

    /**
     * Check if current character is an ASCII letter
     * @return bool
     */
    public function isLetter() {

        return ctype_alpha($this->c);
    }

    /**
     * Consume contiguous white spaces
     */
    public function WS(){

        while(ctype_space($this->c)){
            $this->consume();
        }
    }

    /**
     * Returns the last character readed by this lexer
     * @return string
     */
    public function lastCharacter(): string {
        return $this->lastChar;
    }

    /**
     * Return the last token founded
     * @return Token
     */
    public function lastToken(): Token {

        return $this->lastToken;
    }

    /**
     * Generate a new Token and sets the last token of this lexer as the generated token to be returned by nextToken() method
     * @param $tokenType
     * @param $tokenValue
     * @return Token
     */
    protected function returnToken($tokenType, $tokenValue): Token {

        $token = new Token($tokenType, $tokenValue);
        $this->lastToken = $token;
        return $token;
    }

    /**
     * Returns the next token identified by this lexer
     * @return Token
     */
    public abstract function nextToken(): Token;

    /**
     * Given a token type returns its token name
     * @param $tokenType
     * @return string
     */
    public abstract function getTokenName($tokenType): string ;
}