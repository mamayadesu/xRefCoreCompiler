<?php

namespace Data\String;

/**
 * Makes your string colored
 * @package ColoredString
 */

class ColoredString
{
    /**
     * @param string $str Your string
     * @param ForegroundColors $foregroundColor
     * @param BackgroundColors $backgroundColor
     * @return string Colored string
     */
    public static function Get(string $str, string $foregroundColor = "auto", string $backgroundColor = "auto") : string
    {
        return "\033[" . $foregroundColor . "m" . ($backgroundColor != BackgroundColors::AUTO ? "\033[" . $backgroundColor . "m" : "") . $str . "\033[0m";
    }
}