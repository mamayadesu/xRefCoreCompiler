<?php

namespace Threading\Exceptions\SuperGlobalArray;

/**
 * Class InvalidOperatorException
 */

class InvalidOperatorException extends \Exception
{
    public string $Operator;
    public array $Operators;
    public $Value;
}