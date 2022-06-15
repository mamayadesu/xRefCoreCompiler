<?php
declare(ticks = 1);

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