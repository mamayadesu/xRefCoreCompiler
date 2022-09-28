<?php
declare(ticks = 1);

namespace IO;

/**
 * File system tools
 */

class FileDirectory
{

    /**
     * Copies file or folders to target directory
     *
     * @param string $source File or directory
     * @param string $target Target directory
     */
    public static function Copy(string $source, string $target) : void
    {
        $ds = DIRECTORY_SEPARATOR;
        if (is_dir($source))
        {
            @mkdir($target);
            $d = dir($source);
            while (($entry = $d->read()) != false)
            {
                if ($entry == "." || $entry == "..")
                {
                    continue;
                }
                self::Copy($source . $ds . $entry, $target . $ds . $entry);
            }
            $d->close();
        }
        else
        {
            copy($source, $target);
        }
    }

    /**
     * Deletes file or directory (even if directory is not empty)
     *
     * @param string $path Path to target file or directory
     * @return bool Returns TRUE if target was deleted successfully. Returns FALSE if an error occurred.
     */
    public static function Delete(string $path) : bool
    {
        $_result = true;
        if (file_exists($path) && !is_dir($path))
        {
            $result = @unlink($path);
            return $result;
        }
        foreach (glob($path . "*", GLOB_MARK) as $filename)
        {
            if (is_dir($filename))
            {
                $result = self::Delete($filename);
                if (!$result)
                {
                    $_result = false;
                }
            }
            else
            {
                $result = @unlink($filename);
                if (!$result)
                {
                    $_result = false;
                }
            }
        }
        if (is_dir($path))
        {
            $result = rmdir($path);
            if (!$result)
            {
                $_result = false;
            }
        }
        return $_result;
    }

    /**
     * Sets permission mode for target directory and all its content (Linux systems only)
     *
     * @param int $mode
     * @param string $target
     * @return void
     */
    public static function RecursiveChmod(int $mode, string $target) : void
    {
        if (IS_WINDOWS)
            return;

        if (is_file($target))
        {
            @exec("chmod " . $mode . " " . $target, $output, $result);
            return;
        }

        $t = str_split($target);
        $l = $t[count($t) - 1];

        $star = false;
        if ($l != "/" && $l != "*")
        {
            $target .= "/";
            $star = true;
        }
        @exec("chmod " . $mode . " " . $target, $output, $result);
        @exec("chmod -R " . $mode . " " . $target, $output, $result);

        if ($star)
            $target .= "*";
        @exec("chmod -R " . $mode . " " . $target, $output, $result);
    }

    /**
     * Formats path to target, including returning to parent directories
     *
     * @param string $path Non-formatted path (for example "/var/www/../log")
     * @return string Example: "/var/log/"
     */
    public static function FormatDirectoryPath(string $path) : string
    {
        if (IS_WINDOWS)
            $path = str_replace("/", "\\", $path);
        else
            $path = str_replace("\\", "/", $path);

        $disk = DIRECTORY_SEPARATOR;

        if (IS_WINDOWS)
        {
            if (strlen($path) >= 2)
            {
                $first_two = substr($path, 0, 2);
                $local_drive = substr($first_two, 1) == ":";
                if ($local_drive || $first_two == "\\\\")
                {
                    $disk = $first_two;
                    if ($local_drive)
                        $disk .= DIRECTORY_SEPARATOR;
                    $path = substr($path, 3);
                }
                else
                {
                    $first_two = substr($path, 0, 2);
                    if ($first_two == "\\\\")
                    {
                        $disk = $first_two;
                        $path = substr($path, 2);
                    }
                }
            }
        }

        $split = explode(DIRECTORY_SEPARATOR, $path);

        $arr = [];
        foreach ($split as $directory)
        {
            if ($directory == "")
            {
                continue;
            }

            if ($directory == "..")
            {
                @array_pop($arr);
            }
            else if ($directory != ".")
            {
                $arr[] = $directory;
            }
        }

        $c = count($arr);
        $path = $disk . implode(DIRECTORY_SEPARATOR, $arr);
        $end = "";
        if ($c > 0 && file_exists($path) && is_dir($path))
        {
            $end = DIRECTORY_SEPARATOR;
        }
        $path = $path . $end;
        return $path;
    }
}