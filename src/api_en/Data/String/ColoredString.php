<?php

namespace Data\String;

/**
 * Makes your string colored
 */

class ColoredString
{
    /**
     * @param string $str Your string
     * @param ForegroundColors $foregroundColor
     * @param BackgroundColors $backgroundColor
     * @return string Colored string
     */
    public static function Get(string $str, string $foregroundColor = ForegroundColors::AUTO, string $backgroundColor = BackgroundColors::AUTO) : string
    {}
}