<?php

namespace Threading\Exceptions\SuperGlobalArray;

/**
 * Class InvalidValueTypeException
 * @package Threading\Exceptions\SuperGlobalArray
 */

class InvalidValueTypeException extends \Exception
{
    public string $Operator;
    public array $Operators;
    public $Value;
    public array $ExpectedTypes;
    public string $GivenType;
}