<?php

namespace Data\String;

/**
 * This is a short alias for `ColoredString` class
 */
class Cs
{
    /**
     * @param string $str Your string
     * @param ForegroundColors $foregroundColor
     * @param BackgroundColors $backgroundColor
     * @return string Colored string
     */
    public static function g(string $str, string $foregroundColor = ForegroundColors::AUTO, string $backgroundColor = BackgroundColors::AUTO) : string
    {
        return ColoredString::Get($str, $foregroundColor, $backgroundColor);
    }
}