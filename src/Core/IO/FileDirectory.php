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
     * @param string $path Path to target
     */
    public static function Delete(string $path) : void
    {
        if (file_exists($path) && !is_dir($path))
        {
            unlink($path);
            return;
        }
        foreach (glob($path . "*", GLOB_MARK) as $filename)
        {
            if (is_dir($filename))
            {
                self::Delete($filename);
            }
            else
            {
                unlink($filename);
            }
        }
        if (is_dir($path))
        {
            rmdir($path);
        }
    }
}