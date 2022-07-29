<?php

namespace Threading;

use Threading\Exceptions\SuperGlobalArray\InvalidOperatorException;
use Threading\Exceptions\SuperGlobalArray\InvalidValueTypeException;
use Threading\Exceptions\SuperGlobalArray\ItemIsNotArrayException;
use Threading\Exceptions\SuperGlobalArray\KeyNotFoundException;
use Threading\Exceptions\SuperGlobalArray\UnknownErrorException;
use Threading\Exceptions\SystemMethodCallException;

/**
 * SuperGlobalArray — это массив, доступный из любого потока и не имеющий родительского потока. Суперглобальный массив не поддерживает resources и Closure.
 *
 * Суперглобальный массив не требует синхронизации перед выполнением каких-либо действий. Однако, если один поток обрабатывает данные суперглобального массива в цикле, другие потоки могут работать медленно в любых действиях с суперглобальным массивом.
 *
 * Во избежание ошибок рекомендуется не работать с большими объемами данных. Также рекомендуется не читать весь суперглобальный массив только для того, чтобы получить один элемент массива. Такие действия при использовании суперглобального массива в нескольких потоках одновременно, если в массиве большой объем данных, то поведение массива может стать непредсказуемым.
 *
 * Первым аргументом любого метода SuperGlobalArray (кроме GetInstance(), который не имеет аргументов вообще) является путь к массиву. Итак, если вы хотите провернуть действия, как например такие:
 * $foo = $arr["subarray"]["subsubarray"]["bar"];
 *
 * вы должны сделать следующее:
 * $arr = SuperGlobalArray::GetInstance();
 * $foo = $arr->Get(["subarray", "subsubarray", "bar"]);
 */

final class SuperGlobalArray
{
    /**
     * Возвращает инстанцию суперглобального массива
     *
     * @return SuperGlobalArray
     */
    public static function GetInstance() : ?SuperGlobalArray
    {}

    /**
     * Возвращает значение параметра массива
     *
     * @param array<string> $keys Путь к элементу массива. Например, если вы хотите сделать что-то подобное: $array["hello"]["world"]["foo"]["bar"], используйте это: ["hello", "world", "foo", "bar"]
     * @return mixed
     * @throws ItemIsNotArrayException
     * @throws KeyNotFoundException
     * @throws UnknownErrorException
     */
    public function Get(array $keys)
    {}

    /**
     * Устанавливает новое значение параметра массива
     *
     * @param array<string> $keys Путь к элементу массива. Например, если вы хотите сделать что-то подобное: $array["hello"]["world"]["foo"]["bar"], используйте это: ["hello", "world", "foo", "bar"]
     * @param $value mixed Значение
     * @throws ItemIsNotArrayException
     * @throws KeyNotFoundException
     * @throws UnknownErrorException
     */
    public function Set(array $keys, $value) : void
    {}

    /**
     * Добавляет новый элемент массива с числовыми индексами
     *
     * @param array<string> $keys Путь к элементу массива. Например, если вы хотите сделать что-то подобное: $array["hello"]["world"]["foo"]["bar"], используйте это: ["hello", "world", "foo", "bar"]
     * @param $value mixed Значение
     * @throws ItemIsNotArrayException
     * @throws KeyNotFoundException
     * @throws UnknownErrorException
     */
    public function Add(array $keys, $value) : void
    {}

    /**
     * Возвращает TRUE, если элемент с таким ключом существует
     *
     * @param array<string> $keys Путь к элементу
     * @return bool
     * @throws ItemIsNotArrayException
     * @throws KeyNotFoundException
     * @throws UnknownErrorException
     */
    public function IsSet(array $keys) : bool
    {}

    /**
     * Удаляет элемент массива. Работает так же, как стандартная функция `unset()`
     *
     * @param array<string> $keys Путь к массиву
     * @throws ItemIsNotArrayException
     * @throws UnknownErrorException
     */
    public function Unset(array $keys) : void
    {}

    /**
     * Выполняет оператор с указанным элементом массива
     *
     * @param array<string> $keys Путь к элементу
     * @param string $operator Необходимый оператор. Доступные операторы: ".=", "+=", "-=", "*=", "/=", "++", "--"
     * @param mixed $value Значение оператора. Не используется с операторами "++" и "--"
     * @throws InvalidOperatorException
     * @throws InvalidValueTypeException
     * @throws ItemIsNotArrayException
     * @throws KeyNotFoundException
     */
    public function Operator(array $keys, string $operator, $value = "")
    {}
}