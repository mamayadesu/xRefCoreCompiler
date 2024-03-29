<?php
declare(ticks = 1);

namespace Application;

use IO\FileDirectory;

final class Application
{
    /**
     * @ignore
     */
    private static int $win_winsize_pid = 0;

    /**
     * @ignore
     */
    private static int $win_winsize_port = 0;

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
    private static function windows_run_winsize() : void
    {
        $exe = __CHECK_WINSIZE();

        self::$win_a2r_socket = socket_create(AF_INET, SOCK_DGRAM, 0);
        do
        {
            self::$win_a2r_port = rand(5000, 49151);
        }
        while (!@socket_bind(self::$win_a2r_socket, "127.0.0.1", self::$win_a2r_port));
        $check = socket_create(AF_INET, SOCK_DGRAM, 0);
        do
        {
            self::$win_winsize_port = rand(5000, 49151);
        }
        while (!@socket_bind($check, "127.0.0.1", self::$win_winsize_port));
        socket_close($check);
        $cmd = "start /B /I " . $exe . " " . self::$win_a2r_port . " " . self::$win_winsize_port . " 1>&2";
        $proc = proc_open($cmd, [], $pipes);
        proc_close($proc);
        socket_recvfrom(self::$win_a2r_socket, $buf, 16, 0, $remote_ip, $remote_port);
        self::$win_winsize_pid = intval($buf);
        time_nanosleep(0, 5000000);
    }

    /**
     * Returns required PHP version
     *
     * @return string Required PHP version
     */
    public static function GetRequiredPhpVersion() : string
    {
        return __GET__APP()["php_version"];
    }

    /**
     * Returns a name of application
     *
     * @return string Name of application
     */
    public static function GetName() : string
    {
        return __GET__APP()["app_name"];
    }

    /**
     * Returns a description of application
     *
     * @return string Description of application
     */
    public static function GetDescription() : string
    {
        return __GET__APP()["app_description"];
    }

    /**
     * Returns an author of application
     *
     * @return string Author of application
     */
    public static function GetAuthor() : string
    {
        return __GET__APP()["app_author"];
    }

    /**
     * Returns a version of application
     *
     * @return string Version of application
     */
    public static function GetVersion() : string
    {
        return __GET__APP()["app_version"];
    }

    /**
     * Returns a full path to executable file
     *
     * @return string Full path to executable file
     */
    public static function GetExecutableFileName() : string
    {
        $v1 = \Phar::running(false);
        $v2 = __GET__FILE__();
        $r = (!empty($v1) ? $v1 : $v2);
        return str_replace("/", DIRECTORY_SEPARATOR, $r);
    }

    /**
     * Returns a full path to work directory
     *
     * @return string Full path to work directory
     */
    public static function GetExecutableDirectory() : string
    {
        if (\Phar::running(false) == "")
        {
            return dirname(self::GetExecutableFileName(), 2) . DIRECTORY_SEPARATOR;
        }
        return dirname(self::GetExecutableFileName()) . DIRECTORY_SEPARATOR;
    }

    /**
     * Returns a xRefCore version
     *
     * @return string Framework version
     */
    public static function GetFrameworkVersion() : string
    {
        return __GET_FRAMEWORK_VERSION();
    }

    /**
     * Sets title of process (In Windows - title of window)
     *
     * @param string $title Title of process
     */
    public static function SetTitle(string $title) : void
    {
        if (!function_exists("cli_set_process_title"))
        {
            return;
        }
        if (defined("DEBUG_MODE"))
        {
            $title .= " (DEBUG MODE)";
        }
        if (!IS_WINDOWS)
        {
            echo "\x1b]0;" . $title . "\x07"; // display title to window name in linux
        }
        try
        {
            cli_set_process_title($title);
        }
        catch (\Throwable $e)
        {

        }
    }

    /**
     * Returns a current username
     *
     * @return string
     */
    public static function GetUsername() : string
    {
        if (IS_WINDOWS)
        {
            return $_SERVER["USERNAME"];
        }
        return $_SERVER["LOGNAME"];
    }

    /**
     * Returns a home directory of current user (in Windows is C:\Users\your_username, in *unix systems is /home/your_username or /root)
     *
     * @return string
     */
    public static function GetHomeDirectory() : string
    {
        if (IS_WINDOWS)
        {
            $path = $_SERVER["USERPROFILE"];
        }
        else
        {
            $path = $_SERVER["HOME"];
        }
        return FileDirectory::FormatDirectoryPath($path);
    }

    /**
     * Returns TRUE if your application started with Administrator's permissions.
     *
     * @return bool
     */
    public static function AmIRunningAsSuperuser() : bool
    {
        if (IS_WINDOWS)
        {
            $output = [];
            exec("net session 1>NUL 2>NUL || (echo 0)", $output);
            return (count($output) == 0);
        }
        return posix_getuid() == 0;
    }

    /**
     * Parses arguments to values with keys, free-key values and uninitialized keys
     *
     * @param array<int, string> $args List of arguments
     * @param string $propertyNameDelimiter Delimiter for key name (for example "-" or "--")
     * @param bool $skipFirstElement If true, skips the first element of $args. Usually the first element is path to your application
     * @return array = [
     *  'arguments' => (array<string, string>) Values with keys
     *  'unnamed_values' => (array<int, string>) Values without keys
     *  'uninitialized_keys' => (array<int, string>) Keys which don't have value
     * ]
     */
    public static function ParseArguments(array $args, string $propertyNameDelimiter, bool $skipFirstElement = true) : array
    {
        $c = 0;
        if ($skipFirstElement)
        {
            $c = 1; // skip first element, because it contents path to executable filename
        }
        $result = array
        (
            "arguments" => array(), // example `--foo bar` to "foo" => "bar"
            "unnamed_values" => [],
            "uninitialized_keys" => []
        );
        $pnd = strlen($propertyNameDelimiter);
        $currentPropertyName = "";
        $itemLength = 0;
        for ($i = $c; $i < count($args); $i++)
        {
            $item = $args[$i];
            $itemLength = strlen($item);
            if ($itemLength > $pnd && substr($item, 0, $pnd) == $propertyNameDelimiter)
            {
                if ($currentPropertyName == "")
                {
                    $currentPropertyName = substr($item, $pnd, $itemLength - $pnd);
                }
                else
                {
                    $result["uninitialized_keys"][] = $currentPropertyName;
                }
            }
            else
            {
                if ($currentPropertyName != "")
                {
                    $result["arguments"][$currentPropertyName] = $item;
                    $currentPropertyName = "";
                }
                else
                {
                    $result["unnamed_values"] = $item;
                }
            }
        }
        if ($currentPropertyName != "")
        {
            $result["uninitialized_keys"][] = $currentPropertyName;
        }
        return $result;
    }

    /**
     * Returns a size of window as array with "columns" and "rows" keys
     *
     * @return array{columns: int, rows: int}
     */
    public static function GetWindowSize() : array
    {
        $result = [
            "columns" => 0,
            "rows" => 0
        ];

        if (!IS_WINDOWS)
        {
            exec("tput cols; tput lines", $output);
        }
        else
        {
            $output = self::windows_winsize();
        }
        if (count($output) < 2)
            return $result;

        $result["columns"] = intval($output[0]);
        $result["rows"] = intval($output[1]);
        return $result;
    }

    /**
     * @ignore
     */
    public static function __windows_kill_winsize() : void
    {
        if (self::$win_winsize_pid != 0)
            pclose(popen("taskkill /F /PID " . self::$win_winsize_pid, "r"));
    }

    /**
     * @ignore
     */
    private static function windows_winsize() : ?array
    {
        if (self::$win_winsize_pid == 0 || self::$win_winsize_port == 0)
            self::windows_run_winsize();

        $data = self::$win_a2r_port . " 1";

        socket_sendto(self::$win_a2r_socket, $data, strlen($data), 0, "127.0.0.1", self::$win_winsize_port);
        $r = @socket_recvfrom(self::$win_a2r_socket, $buf, 8192, 0, $remote_ip, $remote_port);
        if ($r === false)
            return ["-1", "-1"];
        if (!$buf)
        {
            return ["-2", "-2"];
        }
        return explode("\n", $buf);
    }

    /**
     * Freezes application's execution. It is analog of sleep/usleep/time_nanosleep functions, but also supports asynchronous tasks.
     *
     * @param int $milliseconds Time in millisecond. One millisecond is 0.001 second, or one second is 1000 milliseconds
     * @return void
     */
    public static function Wait(int $milliseconds) : void
    {
        $expires = microtime(true) + $milliseconds / 1000;
        while ($expires > microtime(true))
        {
            usleep(1000);
        }
    }
}