<?php
declare(ticks = 1);

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
     * Reads a line from input stream after user pressed ENTER
     *
     * @param bool $hideInput Hides characters which user is typing
     * @return string Data from input stream
     */
    public static function ReadLine(bool $hideInput = false) : string
    {
        if (!MAIN_THREAD && !IS_WINDOWS)
        {
            if ($hideInput)
                system("stty cbreak -echo");
            $stream = fopen("/proc/" . self::$parentpid . "/fd/0", "r");
            $result = fgets($stream);
            $result = str_replace("\n", "", $result);
            $result = str_replace("\r", "", $result);
            if ($hideInput)
                system("stty -cbreak echo");
            return $result;
        }
        if (IS_WINDOWS)
        {
            $result = self::windows_read_line($hideInput);
        }
        else
        {
            if ($hideInput)
                system("stty cbreak -echo");

            $result = "";
            if (MAIN_THREAD)
                $stdin = fopen("php://stdin", "r");
            else
                $stdin = fopen("/proc/" . self::$parentpid . "/fd/0", "r");
            stream_set_blocking($stdin, false);
            if (!$hideInput)
            {
                while (!($result = fgets($stdin)))
                {
                    time_nanosleep(0, 10 * 1000000);
                }
            }
            else
            {
                $read = "";
                while (true)
                {
                    $read = fread($stdin, 64);
                    if ($read == "\n" || $read == "\r")
                        break;
                    $result .= $read;
                    time_nanosleep(0, 5 * 1000000);
                }
                echo "\n";
            }
            if ($hideInput)
                system("stty -cbreak echo");
        }
        $result = str_replace("\n", "", $result);
        $result = str_replace("\r", "", $result);
        return $result;
    }

    /**
     * @ignore
     */
    private static function windows_read_line(bool $hideInput) : string
    {
        $exe = __CHECK_READKEY();

        $socket = socket_create(AF_INET, SOCK_DGRAM, 0);
        do
        {
            $port = rand(5000, 49151);
        }
        while (!@socket_bind($socket, "127.0.0.1", $port));
        $cmd = "start /B /I " . $exe . " " . $port . " " . ($hideInput ? "2" : "3") . " 1>&2";
        $proc = proc_open($cmd, [], $pipes);
        proc_close($proc);
        socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, array("sec" => 0, "usec" => 5000));
        do
        {
            $r = @socket_recvfrom($socket, $buf, 8192, 0, $remote_ip, $remote_port);
        }
        while ($r === false);
        if ($hideInput) echo "\n";
        if (!$buf)
        {
            return "";
        }
        //$buf = base64_decode($buf);
        return $buf;
    }

    /**
     * Waits when user press keyboard key and returns character or key name
     *
     * @return string Pressed character or key name
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
            while (!@socket_bind($socket, "127.0.0.1", $port));
            $cmd = "start /B /I " . $exe . " " . $port . " 1 1>&2";
            $proc = proc_open($cmd, [], $pipes);
            proc_close($proc);
            socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, array("sec" => 0, "usec" => 5000));
            do
            {
                $r = @socket_recvfrom($socket, $buf, 32, 0, $remote_ip, $remote_port);
            }
            while ($r === false);
            if (!$buf)
            {
                return "0";
            }
            //$buf = base64_decode($buf);
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
        while (($keypress = fread($stdin, 64)) == "")
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
     * Writes data to stream output and sets pointer to new line
     *
     * @param string $text Input text
     * @param ForegroundColors $foregroundColor Text color
     * @param BackgroundColors $backgroundColor Background color
     */
    public static function WriteLine(string $text, string $foregroundColor = ForegroundColors::AUTO, string $backgroundColor = BackgroundColors::AUTO) : void
    {
        self::Write($text . "\n", $foregroundColor, $backgroundColor);
    }

    /**
     * Writes data to stream output
     *
     * @param string $text Input text
     * @param ForegroundColors $foregroundColor Text color
     * @param BackgroundColors $backgroundColor Background color
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
     * Removes text from the last line
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