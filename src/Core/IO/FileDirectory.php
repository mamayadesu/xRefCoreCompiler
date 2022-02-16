<?php

namespace IO;

/**
 * File system tools
 * @package IO
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
}