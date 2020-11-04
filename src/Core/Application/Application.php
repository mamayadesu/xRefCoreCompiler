<?php

namespace Application;

class Application
{

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
        return $r;
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
     * Returns a framework version
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
        try
        {
            cli_set_process_title($title);
        }
        catch (\Exception $e)
        {

        }
    }

    /**
     * Parses arguments to values with keys, free-key values and uninitialized keys
     *
     * @param array<int, string> $args List of arguments
     * @param string $propertyNameDelimiter Delimiter for key name (for example "-" or "--")
     * @param bool $skipFirstElement If true, skips first element of $args
     * @return array = [
     *  'arguments' => (array<string, string>) Values with keys
     *  'unnamed_values' => (array<int, string>) Values without keys
     *  'uninitialized_keys' => (array<int, string>) Keys which don't have value
     * ]
     */
    public static function ParseArguments(array $args, string $propertyNameDelimiter, bool $skipFirstElement = true) : array
    {
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
        for ($i = 1; $i < count($args); $i++)
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
}