<?php

namespace Data;

use \Data\Exceptions\CreateEnumInstanceException;

/**
 * Creating Enum objects
 */

abstract class Enum
{

    /**
     * Get all values of Enum
     * @return array<string, mixed> Enum values
     */
    final public static function GetValues() : array
    {}

    /**
     * Returns TRUE if Enum contains specified item
     *
     * @param $item
     * @return bool
     */
    final public static function HasItem($item) : bool
    {}
}