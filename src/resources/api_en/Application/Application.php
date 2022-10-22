<?php

namespace Application;

final class Application
{

    /**
     * Returns required PHP version
     *
     * @return string Required PHP version
     */
    public static function GetRequiredPhpVersion() : string
    {}

    /**
     * Returns a name of application
     *
     * @return string Name of application
     */
    public static function GetName() : string
    {}

    /**
     * Returns a description of application
     *
     * @return string Description of application
     */
    public static function GetDescription() : string
    {}

    /**
     * Returns an author of application
     *
     * @return string Author of application
     */
    public static function GetAuthor() : string
    {}

    /**
     * Returns a version of application
     *
     * @return string Version of application
     */
    public static function GetVersion() : string
    {}

    /**
     * Returns a full path to executable file
     *
     * @return string Full path to executable file
     */
    public static function GetExecutableFileName() : string
    {}

    /**
     * Returns a full path to work directory
     *
     * @return string Full path to work directory
     */
    public static function GetExecutableDirectory() : string
    {}

    /**
     * Returns a xRefCore version
     *
     * @return string Framework version
     */
    public static function GetFrameworkVersion() : string
    {}

    /**
     * Sets title of process (In Windows - title of window)
     *
     * @param string $title Title of process
     */
    public static function SetTitle(string $title) : void
    {}

    /**
     * Returns a current username
     *
     * @return string
     */
    public static function GetUsername() : string
    {}

    /**
     * Returns a home directory of current user (in Windows is C:\Users\your_username, in *unix systems is /home/your_username or /root)
     *
     * @return string
     */
    public static function GetHomeDirectory() : string
    {}

    /**
     * Returns TRUE if your application started with Administrator's permissions.
     *
     * @return bool
     */
    public static function AmIRunningAsSuperuser() : bool
    {}

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
    {}
    
    /**
     * Returns a size of window as array with "columns" and "rows" keys
     *
     * @return array{columns: int, rows: int}
     */
    public static function GetWindowSize() : array
    {}
}