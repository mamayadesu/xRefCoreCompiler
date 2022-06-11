<?php
declare(ticks = 1);

namespace Threading\Exceptions\SuperGlobalArray;

/**
 * Class ItemIsNotArrayException
 * @package Threading\Exceptions\SuperGlobalArray
 */

class ItemIsNotArrayException extends \Exception
{
    public $Key;
    public array $PassedKeys;
    public string $Type;
}