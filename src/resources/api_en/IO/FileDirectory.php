<?php

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
    {}

    /**
     * Deletes file or directory (even if directory is not empty)
     *
     * @param string $path Path to target file or directory
     * @return bool Returns TRUE if target was deleted successfully. Returns FALSE if an error occurred.
     */
    public static function Delete(string $path) : bool
    {}

    /**
     * Sets permission mode for target directory and all its content (Linux systems only)
     *
     * @param int $mode
     * @param string $target
     * @return void
     */
    public static function RecursiveChmod(int $mode, string $target) : void
    {}
}