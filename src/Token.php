<?php


namespace didix16\Grammar;

/**
 * Represents a parser token used in grammars
 * Class Token
 * @package didix16\Grammar
 */
class Token implements TokenInterface
{
    const NULL_TOKEN_TYPE = 0;
    const NULL_TOKEN_VALUE = NULL;

    protected $value;

    protected $type;

    /**
     * Token constructor.
     * @param $type
     * @param $value
     */
    public function __construct($type, $value)
    {
        $this->value = $value;
        $this->type = $type;
    }

    public function __toString(): string
    {
        return "<$this->type, $this->value>";
    }

    public function getType() {

        return $this->type;
    }

    public function getValue(){

        return $this->value;
    }
}