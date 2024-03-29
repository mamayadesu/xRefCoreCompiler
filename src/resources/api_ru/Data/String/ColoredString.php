<?php

namespace Data\String;

/**
 * Превращает вашу строку в цветную
 */

class ColoredString
{
    /**
     * @param string $str Строка
     * @param ForegroundColors $foregroundColor
     * @param BackgroundColors $backgroundColor
     * @return string Цветная строка
     */
    public static function Get(string $str, string $foregroundColor = ForegroundColors::AUTO, string $backgroundColor = BackgroundColors::AUTO) : string
    {}
}