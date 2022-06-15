<?php
declare(ticks = 1);

namespace Threading\Exceptions\SuperGlobalArray;

/**
 * Class InvalidValueTypeException
 */

class InvalidValueTypeException extends \Exception
{
    public string $Operator;
    public array $Operators;
    public $Value;
    public array $ExpectedTypes;
    public string $GivenType;
}