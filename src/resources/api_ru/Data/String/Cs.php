<?php

namespace Data\String;

/**
 * Укороченный алиас для класса `ColoredString`
 */
class Cs
{
    /**
     * @param string $str Строка
     * @param ForegroundColors $foregroundColor
     * @param BackgroundColors $backgroundColor
     * @return string Цветная строка
     */
    public static function g(string $str, string $foregroundColor = ForegroundColors::AUTO, string $backgroundColor = BackgroundColors::AUTO) : string
    {}
}