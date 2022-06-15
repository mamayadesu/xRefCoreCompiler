<?php

namespace IO;

use Data\String\BackgroundColors;
use Data\String\ColoredString;
use Data\String\ForegroundColors;

/**
 * Contains tools for CLI I/O
 */

class Console
{
    /**
     * Reads a line from input stream after user pressed ENTER
     *
     * @param bool $hideInput Hides characters which user is typing
     * @return string Data from input stream
     */
    public static function ReadLine(bool $hideInput = false) : string
    {}

    /**
     * Waits when user press keyboard key and returns character or key name
     *
     * @return string Pressed character or key name
     */
    public static function ReadKey() : string
    {}

    /**
     * Writes data to stream output and sets pointer to new line
     *
     * @param string $text Input text
     * @param ForegroundColors $foregroundColor Text color
     * @param BackgroundColors $backgroundColor Background color
     */
    public static function WriteLine(string $text, string $foregroundColor = ForegroundColors::AUTO, string $backgroundColor = BackgroundColors::AUTO) : void
    {}

    /**
     * Writes data to stream output
     *
     * @param string $text Input text
     * @param ForegroundColors $foregroundColor Text color
     * @param BackgroundColors $backgroundColor Background color
     */
    public static function Write(string $text, string $foregroundColor = ForegroundColors::AUTO, string $backgroundColor = BackgroundColors::AUTO) : void
    {}

    /**
     * Removes text from the last line
     *
     * @param string $text Replace whole text on current line to new text
     */
    public static function ClearLine(string $text = "") : void
    {}

    /**
     * Clears all output in window
     */
    public static function ClearWindow() : void
    {}
}