<?php

namespace IO;

use Data\String\BackgroundColors;
use Data\String\ColoredString;
use Data\String\ForegroundColors;
use IO\Console\Exceptions\ReadInterruptedException;

/**
 * Contains tools for CLI I/O
 */

class Console
{
    /**
     * Interrupts already called and still not finished or will be called Console::ReadLine() and Console::ReadKey(). Can be used in asynchronous tasks to stop reading
     *
     * @return void
     */
    public static function InterruptRead() : void
    {}

    /**
     * Reads a line from input stream after user pressed ENTER
     *
     * @param bool $hideInput Hides characters which user is typing
     * @param bool $interruptible Is method can be interrupted by Console::InterruptRead()
     * @return string Data from input stream
     * @throws ReadInterruptedException Method was interrupted by Console::InterruptRead()
     */
    public static function ReadLine(bool $hideInput = false, bool $interruptible = true) : string
    {}

    /**
     * Waits when user press keyboard key and returns character or key name
     *
     * @param bool $interruptible Is method can be interrupted by Console::InterruptRead()
     * @return string Pressed character or key name
     * @throws ReadInterruptedException Throws when method was interrupted manually
     */
    public static function ReadKey(bool $interruptible = true) : string
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
     *
     * @param string $replacement New screen's content
     * @return void
     */
    public static function ClearWindow(string $replacement = "") : void
    {}
}