<?php
declare(ticks = 1);

namespace GetterSetter;

use GetterSetter\Exceptions\PropertyNotFoundException;

/**
 * This trait is auxiliary. It serves to simplify the implementation of getters and setters.
 * To create a getter and setter, connect this trait to a class and declare a method inside the class with the name of your field.
 * The name of the desired field must be prefixed with `_gs_`.
 * The method must return an array with two keys: `get` and `set`, and the values must be anonymous functions.
 * The first method will return the desired value, and the second will set that value.
 * For example, you want to declare a $PropertyName field:
 *
 * ```
 * <?php
 * declare(ticks = 1);
 *
 * namespace Program;
 *
 * use GetterSetter\GetterSetter;
 * use IO\Console;
 *
 * class Main
 * {
 *     use GetterSetter;
 *
 *     private string $myprop = "";
 *
 *     public function _gs_PropertyName() : array
 *     {return [
 *         Get => function() : string
 *         {
 *             return strtoupper($this->myprop);
 *         },
 *         Set => function(string $value) : void
 *         {
 *             $this->myprop = $value;
 *         }
 *     ];}
 *
 *     public function __construct()
 *     {
 *         $this->PropertyName = "hello world";
 *         Console::WriteLine($this->PropertyName); // Result: "HELLO WORLD";
 *     }
 * }
 * ```
 */
trait GetterSetter
{}