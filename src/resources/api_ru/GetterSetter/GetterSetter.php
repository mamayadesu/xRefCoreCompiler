<?php
declare(ticks = 1);

namespace GetterSetter;

use GetterSetter\Exceptions\PropertyNotFoundException;

/**
 * Данный трэйт служит для упрощённой реализации геттеров и сеттеров.
 * Чтобы создать геттер и сеттер, подключите данный трэйт к классу и объявите метод внутри класса с названием вашего поля.
 * Перед названием желаемого поля обязательно должен стоять префикс `_gs_`.
 * Метод должен возвращать массив с двумя ключами: `get` и `set`, а значения должны быть анонимные функции.
 * Первый метод будет возвращать желаемое значение, а второй устанавливать это значение.
 * Например, вы хотите объявить поле $PropertyName:
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
 *         Console::WriteLine($this->PropertyName); // Результат: "HELLO WORLD";
 *     }
 * }
 * ```
 */
trait GetterSetter
{}