<?php

namespace Threading;

use Threading\Exceptions\SuperGlobalArray\InvalidOperatorException;
use Threading\Exceptions\SuperGlobalArray\InvalidValueTypeException;
use Threading\Exceptions\SuperGlobalArray\ItemIsNotArrayException;
use Threading\Exceptions\SuperGlobalArray\KeyNotFoundException;
use Threading\Exceptions\SuperGlobalArray\UnknownErrorException;
use Threading\Exceptions\SystemMethodCallException;

/**
 * SuperGlobalArray is threaded array which is reachable from any thread, and it has no a parent thread. Super global array does not support resources and Closure.
 *
 * Super global array does not require synchronization before to do any actions. However, if one thread is handling super global array data in a loop, another threads may work slowly in any actions with super global array.
 *
 * To avoid errors, it is recommended do not work with large amounts of data. It is also recommended not to read the entire super global array just to get one element of the array. Such actions when using the super global array with several threads in one time, if the array has a large amount of data, the behavior of the array can become unpredictable.
 *
 * The first argument of any SuperGlobalArray method (except GetInstance() which doesn't have any arguments) is array path. So, if you want to do some actions like that:
 * $foo = $arr["subarray"]["subsubarray"]["bar"];
 *
 * you have to use this:
 * $arr = SuperGlobalArray::GetInstance();
 * $foo = $arr->Get(["subarray", "subsubarray", "bar"]);
 */

final class SuperGlobalArray
{
    /**
     * Returns instance of super global array
     *
     * @return SuperGlobalArray
     */
    public static function GetInstance() : ?SuperGlobalArray
    {}

    /**
     * Returns value of array key
     *
     * @param array<string> $keys Path to value. For example, if you want to make thing like this: $array["hello"]["world"]["foo"]["bar"], use this: ["hello", "world", "foo", "bar"]
     * @return mixed
     * @throws ItemIsNotArrayException
     * @throws KeyNotFoundException
     * @throws UnknownErrorException
     */
    public function Get(array $keys)
    {}

    /**
     * Sets new value for key
     *
     * @param array<string> $keys Path to value. For example, if you want to make thing like this: $array["hello"]["world"]["foo"]["bar"], use this: ["hello", "world", "foo", "bar"]
     * @param $value mixed Value
     * @throws ItemIsNotArrayException
     * @throws KeyNotFoundException
     * @throws UnknownErrorException
     */
    public function Set(array $keys, $value) : void
    {}

    /**
     * Addes value to array with numeric index
     *
     * @param array<string> $keys Path to value. For example, if you want to make thing like this: $array["hello"]["world"][] = $value, use this: Add(["hello", "world"], $value)
     * @param $value mixed Value
     * @throws ItemIsNotArrayException
     * @throws KeyNotFoundException
     * @throws UnknownErrorException
     */
    public function Add(array $keys, $value) : void
    {}

    /**
     * Returns TRUE if item with same key exists
     *
     * @param array<string> $keys Path to value
     * @return bool
     * @throws ItemIsNotArrayException
     * @throws KeyNotFoundException
     * @throws UnknownErrorException
     */
    public function IsSet(array $keys) : bool
    {}

    /**
     * Deletes item in array, like native PHP `unset()` function
     *
     * @param array<string> $keys Path to value
     * @throws ItemIsNotArrayException
     * @throws UnknownErrorException
     */
    public function Unset(array $keys) : void
    {}

    /**
     * Executes operator with array item
     *
     * @param array<string> $keys Path to value
     * @param string $operator Required operator. Available operators: ".=", "+=", "-=", "*=", "/=", "++", "--"
     * @param mixed $value Value for operator. Isn't using with "++" and "--" operators
     * @throws InvalidOperatorException
     * @throws InvalidValueTypeException
     * @throws ItemIsNotArrayException
     * @throws KeyNotFoundException
     */
    public function Operator(array $keys, string $operator, $value = "")
    {}
}