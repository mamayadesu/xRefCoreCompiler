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
    public static function Get(string $str, string $foregroundColor = ForegroundColors::AUTO, string $backgroundColor = BackgroundColors::AUTO) : string
    {
        return ($foregroundColor != ForegroundColors::AUTO ? "\033[" . $foregroundColor . "m" : "") . ($backgroundColor != BackgroundColors::AUTO ? "\033[" . $backgroundColor . "m" : "") . $str . "\033[0m";
    }
}