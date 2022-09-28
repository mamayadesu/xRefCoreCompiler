<?php
declare(ticks = 1);

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
     * @ignore
     */
    private static int $parentpid = 0;

    /**
     * @ignore
     */
    private static int $win_reader_pid = 0;

    /**
     * @ignore
     */
    private static int $win_reader_port = 0;

    /**
     * @ignore
     */
    private static int $win_a2r_port = 0;

    /**
     * @ignore
     */
    private static $win_a2r_socket;

    /**
     * @ignore
     */
    private static bool $readInterrupted = false;

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
     * Interrupts already called and still not finished or will be called Console::ReadLine() and Console::ReadKey(). Can be used in asynchronous tasks to stop reading
     * 
     * @return void
     */
    public static function InterruptRead() : void
    {
        self::$readInterrupted = true;
    }

    /**
     * Reads a line from input stream after user pressed ENTER
     *
     * @param bool $hideInput Hides characters which user is typing
     * @param bool $interruptible Is method can be interrupted by Console::InterruptRead()
     * @return string Data from input stream
     * @throws ReadInterruptedException Method was interrupted by Console::InterruptRead()
     */
    public static function ReadLine(bool $hideInput = false, bool $interruptible = true) : string
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
            $result = self::windows_read_line($hideInput, $interruptible);
            if ($result === null)
            {
                self::$readInterrupted = false;
                $e = new ReadInterruptedException("ReadLine was interrupted manually.");
                $e->__xrefcoreexception = true;
                throw $e;
            }
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
                    if (self::$readInterrupted && $interruptible)
                    {
                        self::$readInterrupted = false;
                        $e = new ReadInterruptedException("ReadLine was interrupted manually.");
                        $e->__xrefcoreexception = true;
                        throw $e;
                    }
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
                    if ($read == "\010" || $read == "\177")
                        $result = substr($result, 0, -1);
                    else
                        $result .= $read;
                    if (self::$readInterrupted && $interruptible)
                    {
                        self::$readInterrupted = false;
                        $e = new ReadInterruptedException("ReadLine was interrupted manually.");
                        $e->__xrefcoreexception = true;
                        throw $e;
                    }
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
    private static function windows_run_reader() : void
    {
        $exe = __CHECK_READKEY();

        self::$win_a2r_socket = socket_create(AF_INET, SOCK_DGRAM, 0);
        do
        {
            self::$win_a2r_port = rand(5000, 49151);
        }
        while (!@socket_bind(self::$win_a2r_socket, "127.0.0.1", self::$win_a2r_port));

        $check = socket_create(AF_INET, SOCK_DGRAM, 0);
        do
        {
            self::$win_reader_port = rand(5000, 49151);
        }
        while (!@socket_bind($check, "127.0.0.1", self::$win_reader_port));
        socket_close($check);

        $cmd = "start /B /I " . $exe . " " . self::$win_a2r_port . " " . self::$win_reader_port . " 1>&2";
        $proc = proc_open($cmd, [], $pipes);
        proc_close($proc);
        socket_recvfrom(self::$win_a2r_socket, $buf, 16, 0, $remote_ip, $remote_port);

        self::$win_reader_pid = intval($buf);
        time_nanosleep(0, 5000000);
    }

    /**
     * @ignore
     */
    public static function __windows_kill_reader() : void
    {
        if (self::$win_reader_pid != 0)
            pclose(popen("taskkill /F /PID " . self::$win_reader_pid, "r"));
    }

    /**
     * @ignore
     */
    private static function windows_read_line(bool $hideInput, bool $interruptible) : ?string
    {
        if (self::$win_reader_pid == 0 || self::$win_reader_port == 0)
            self::windows_run_reader();

        $data = self::$win_a2r_port . " " . ($hideInput ? 2 : 3);

        socket_sendto(self::$win_a2r_socket, $data, strlen($data), 0, "127.0.0.1", self::$win_reader_port);
        socket_set_option(self::$win_a2r_socket, SOL_SOCKET, SO_RCVTIMEO, array("sec" => 0, "usec" => 5000));
        do
        {
            $r = @socket_recvfrom(self::$win_a2r_socket, $buf, 8192, 0, $remote_ip, $remote_port);
            if (self::$readInterrupted && $interruptible)
            {
                return null;
            }
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
     * @param bool $interruptible Is method can be interrupted by Console::InterruptRead()
     * @return string Pressed character or key name
     * @throws ReadInterruptedException Throws when method was interrupted manually
     */
    public static function ReadKey(bool $interruptible = true) : string
    {
        if (IS_WINDOWS)
        {
            if (self::$win_reader_pid == 0 || self::$win_reader_port == 0)
                self::windows_run_reader();

            $data = self::$win_a2r_port . " 1";
            socket_sendto(self::$win_a2r_socket, $data, strlen($data), 0, "127.0.0.1", self::$win_reader_port);
            socket_set_option(self::$win_a2r_socket, SOL_SOCKET, SO_RCVTIMEO, array("sec" => 0, "usec" => 5000));
            do
            {
                $r = @socket_recvfrom(self::$win_a2r_socket, $buf, 32, 0, $remote_ip, $remote_port);
                if (self::$readInterrupted && $interruptible)
                {
                    self::$readInterrupted = false;
                    $e = new ReadInterruptedException("ReadKey was interrupted manually.");
                    $e->__xrefcoreexception = true;
                    throw $e;
                }
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
            if (self::$readInterrupted && $interruptible)
            {
                self::$readInterrupted = false;
                $e = new ReadInterruptedException("ReadKey was interrupted manually.");
                $e->__xrefcoreexception = true;
                throw $e;
            }
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

            case "\x1BOP":
            case "\x1B[11~":
                $keypress = "f1";
                break;

            case "\x1BOQ":
            case "\x1B[12~":
                $keypress = "f2";
                break;

            case "\x1BOR":
            case "\x1B[13~":
                $keypress = "f3";
                break;

            case "\x1BOS":
            case "\x1B[14~":
                $keypress = "f4";
                break;

            case "\x1B[15~":
                $keypress = "f5";
                break;

            case "\x1B[17~":
                $keypress = "f6";
                break;

            case "\x1B[18~":
                $keypress = "f7";
                break;

            case "\x1B[19~":
                $keypress = "f8";
                break;

            case "\x1B[20~":
                $keypress = "f9";
                break;

            case "\x1B[21~":
                $keypress = "f10";
                break;

            case "\x1B[23~\x1B":
            case "\x1B[23~":
                $keypress = "f11";
                break;

            case "\x1B[24~\b":
            case "\x1B[24~":
                $keypress = "f12";
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
     *
     * @param string $replacement New screen's content
     * @return void
     */
    public static function ClearWindow(string $replacement = "") : void
    {
        if (IS_WINDOWS)
        {
            pclose(popen('cls','w'));
        }
        else system("clear");
        echo $replacement;
    }
}