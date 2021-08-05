<?php

namespace didix16\Grammar;

/**
 * Interface TokenInterface
 * @package didix16\Grammar
 */
interface TokenInterface {

     /**
     * Token constructor.
     * @param $type
     * @param $value
     */
    public function __construct($type, $value);

    /**
     * Returns a representation of this token
     */
    public function __toString(): string;

    /**
     * Returns the type of the token
     */
    public function getType();

    /**
     * Returns the value this token is holding
     */
    public function getValue();

}