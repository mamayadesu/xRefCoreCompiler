<?php

namespace Data;

use \Data\Exceptions\CreateEnumInstanceException;

/**
 * Создание перечислений
 */

abstract class Enum
{

    /**
     * Получить все элементы перечисления
     * @return array<string, mixed> Элементы перечисления
     */
    final public static function GetValues() : array
    {}

    /**
     * Возвращает TRUE, если перечисление содержит этот элемент
     *
     * @param $item
     * @return bool
     */
    final public static function HasItem($item) : bool
    {}
}