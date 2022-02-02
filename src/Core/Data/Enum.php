<?php

namespace Data;

use \Data\Exceptions\CreateEnumInstanceException;

/**
 * Creating Enum objects
 * @package Data
 */

abstract class Enum
{
    /**
     * @ignore
     */
    final public function __construct()
    {
        throw new CreateEnumInstanceException("Cannot create instance of Enum");
    }

    /**
     * Get all values of Enum
     * @return array<string> Enum values
     */
    final public static function GetValues() : array
    {
        $class_name = get_called_class();
        $obj = new $class_name();
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

    final public static function HasItem($item) : bool
    {
        return in_array($item, self::GetValues());
    }
}