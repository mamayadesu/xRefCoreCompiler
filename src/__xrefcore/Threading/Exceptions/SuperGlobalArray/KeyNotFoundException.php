<?php
declare(ticks = 1);

namespace Threading\Exceptions\SuperGlobalArray;

/**
 * Class KeyNotFoundException
 * @package Threading\Exceptions\SuperGlobalArray
 */

class KeyNotFoundException extends \Exception
{
    public $Key;
    public array $PassedKeys;
}