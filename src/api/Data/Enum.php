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
     * @return array<string> Enum values
     */
    final public static function GetValues() : array
    {
        $class_name = get_called_class();
        self::$preventException = true;
        $obj = new $class_name();
        self::$preventException = false;
        $reflectionClass = new \ReflectionClass($obj);
        $arr = [];
        foreach ($reflectionClass->getConstants() as $key => $value)
        {
            $arr[] = $value;
        }
        $obj = null;
        unset($obj);
        return $arr;
    }

    /**
     * Returns TRUE if Enum contains specified item
     *
     * @param $item
     * @return bool
     */
    final public static function HasItem($item) : bool
    {
        return in_array($item, self::GetValues());
    }
}