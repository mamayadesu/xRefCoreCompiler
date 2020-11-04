<?php

namespace IO;

/**
 * Console tools
 * @package IO
 */

class Console
{

    /**
     * Reads line from default stream input. Attention! In child-threads this method works only in Windows but doesn't work in *Unix systems
     *
     * @return string Data from stream input
     */
    public static function ReadLine() : string
    {
        $result = fgets(STDIN);
        $result = str_replace("\n", "", $result);
        $result = str_replace("\r", "", $result);
        return $result;
    }

    /**
     * Writes text to default stream output and sets pointer to new line
     *
     * @param string $text Input text
     */
    public static function WriteLine(string $text) : void
    {
        self::Write($text . "\n");
    }

    /**
     * Writes text to default stream output
     *
     * @param string $text Input text
     */
    public static function Write(string $text) : void
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) == "WIN" && !strpos(php_uname(), "Windows 10"))
        {
            $text = iconv("UTF-8", "CP866", $text);
        }
        echo $text;
    }

    /**
     * Clears a whole line
     *
     * @param string $text Replace whole text on current line to new text
     */
    public static function ClearLine(string $text = "") : void
    {
        self::Write("\r" . $text);
    }
}