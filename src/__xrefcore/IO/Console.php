<?php

namespace IO;

use Data\String\BackgroundColors;
use Data\String\ColoredString;
use Data\String\ForegroundColors;

/**
 * Console tools
 * @package IO
 */

class Console
{
    /**
     * @ignore
     */
    private static int $parentpid = 0;

    /**
     * @ignore
     */
    public static function __setparentpid(int $pid) : void
    {
        if (self::$parentpid != 0)
            return;

        self::$parentpid = $pid;
    }

    /**
     * Reads line from default input stream. Attention! In child-threads this method works only in Windows but doesn't work in *Unix systems
     *
     * @return string Data from stream input
     */
    public static function ReadLine() : string
    {
        if (!MAIN_THREAD && !IS_WINDOWS)
        {
            $stream = fopen("/proc/" . self::$parentpid . "/fd/0", "r");
            $result = fgets($stream);
            $result = str_replace("\n", "", $result);
            $result = str_replace("\r", "", $result);
            return $result;
        }
        if (IS_WINDOWS)
        {
            $result = readline(); // Due to Cyrillic support issues in Windows, the native readline method is used
        }
        else
        {
            $result = fgets(STDIN);
        }
        $result = str_replace("\n", "", $result);
        $result = str_replace("\r", "", $result);
        return $result;
    }

    /**
     * Reads pressed key in input stream
     *
     * @return string
     */
    public static function ReadKey() : string
    {
        if (IS_WINDOWS)
        {
            $exe = __CHECK_READKEY();

            $socket = socket_create(AF_INET, SOCK_DGRAM, 0);
            do
            {
                $port = rand(100, 49151);
            }
            while (!@socket_bind($socket, "127.0.0.2", $port));
            $cmd = "start /B /I " . $exe . " " . $port . " 1>&2";
            $proc = proc_open($cmd, [], $pipes);
            proc_close($proc);
            $r = @socket_recvfrom($socket, $buf, 16, 0, $remote_ip, $remote_port);
            if (!$buf)
            {
                return "";
            }
            $translate = array(
                "D1" => "1",
                "D2" => "2",
                "D3" => "3",
                "D4" => "4",
                "D5" => "5",
                "D6" => "6",
                "D7" => "7",
                "D8" => "8",
                "D9" => "9",
                "D0" => "0",
                "OemMinus" => "-",
                "OemPlus" => "=",
                "Oem3" => "`",
                "Oem4" => "[",
                "Oem6" => "]",
                "Oem1" => ";",
                "Oem7" => "'",
                "Oem5" => "\\",
                "OemComma" => ",",
                "OemPeriod" => ".",
                "Oem2" => "\/",
                "Divide" => "\/",
                "Multiply" => "*",
                "Subtract" => "-",
                "Add" => "+",
                "Decimal" => "."
            );
            if (isset($translate[$buf]))
            {
                $buf = $translate[$buf];
            }
            $result = strtolower($buf);
            return $result;
        }
        if (MAIN_THREAD)
            $stdin = fopen("php://stdin", "r");
        else
            $stdin = fopen("/proc/" . self::$parentpid . "/fd/0", "r");

        stream_set_blocking($stdin, false);
        system("stty cbreak -echo");
        $t = 1000000;
        while (!($keypress = fgets($stdin)))
        {
            time_nanosleep(0, 5 * $t);
        }
        $keypress_lower = strtolower($keypress);
        stream_set_blocking($stdin, true);
        $translate = array(
            "й" => "q",
            "ц" => "w",
            "у" => "e",
            "к" => "r",
            "е" => "t",
            "н" => "y",
            "г" => "u",
            "ш" => "i",
            "щ" => "o",
            "з" => "p",
            "х" => "[",
            "ъ" => "]",
            "ф" => "a",
            "ы" => "s",
            "в" => "d",
            "а" => "f",
            "п" => "g",
            "р" => "h",
            "о" => "j",
            "л" => "k",
            "д" => "l",
            "ж" => ";",
            "э" => "'",
            "я" => "z",
            "ч" => "x",
            "с" => "c",
            "м" => "v",
            "и" => "b",
            "т" => "n",
            "ь" => "m",
            "б" => ",",
            "ю" => ".",
            "." => "/",
            "ё" => "`"
        );
        if (isset($translate[$keypress_lower]))
        {
            $keypress = $translate[$keypress_lower];
        }
        switch ($keypress) {
            case "\033[A":
                $keypress = "UpArrow";
                break;

            case "\033[B":
                $keypress = "DownArrow";
                break;

            case "\033[C":
                $keypress = "RightArrow";
                break;

            case "\033[D":
                $keypress = "LeftArrow";
                break;

            case "\n":
                $keypress = "Enter";
                break;

            case " ":
                $keypress = "Spacebar";
                break;

            case "\010":
            case "\177":
                $keypress = "Backspace";
                break;

            case "\t":
                $keypress = "Tab";
                break;

            case "\e":
                $keypress = "Escace";
                break;
        }
        system("stty -cbreak echo");
        return strtolower($keypress);
    }

    /**
     * Writes text to default stream output and sets pointer to new line
     *
     * @param string $text Input text
     * @param ForegroundColors $foregroundColor
     * @param BackgroundColors $backgroundColor
     */
    public static function WriteLine(string $text, string $foregroundColor = ForegroundColors::AUTO, string $backgroundColor = BackgroundColors::AUTO) : void
    {
        self::Write($text . "\n", $foregroundColor, $backgroundColor);
    }

    /**
     * Writes text to default stream output
     *
     * @param string $text Input text
     * @param ForegroundColors $foregroundColor
     * @param BackgroundColors $backgroundColor
     */
    public static function Write(string $text, string $foregroundColor = ForegroundColors::AUTO, string $backgroundColor = BackgroundColors::AUTO) : void
    {
        $text = ColoredString::Get($text, $foregroundColor, $backgroundColor);
        if (IS_WINDOWS && !strpos(php_uname(), "Windows 10"))
        {
            $text = iconv("UTF-8", "CP866", $text);
        }
        echo $text;
    }

    /**
     * Clears a line
     *
     * @param string $text Replace whole text on current line to new text
     */
    public static function ClearLine(string $text = "") : void
    {
        self::Write("\r" . $text);
    }

    /**
     * Clears all output in window
     */
    public static function ClearWindow() : void
    {
        if (IS_WINDOWS)
        {
            echo chr(27) . chr(91) . 'H' . chr(27) . chr(91) . 'J';
        }
        else system("clear");
    }
}