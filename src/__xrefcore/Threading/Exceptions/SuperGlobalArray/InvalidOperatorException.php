<?php
declare(ticks = 1);

namespace Threading\Exceptions\SuperGlobalArray;

/**
 * Class InvalidOperatorException
 * @package Threading\Exceptions\SuperGlobalArray
 */

class InvalidOperatorException extends \Exception
{
    public string $Operator;
    public array $Operators;
    public $Value;
}