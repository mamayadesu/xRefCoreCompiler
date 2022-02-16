<?php

namespace Program;

use Application\Application;
use Data\String\ColoredString;
use Data\String\ForegroundColors;
use IO\Console;
use IO\FileDirectory;

class Utils
{
    public static function Configure() : void
    {
        $is_windows = (strtoupper(substr(PHP_OS, 0, 3)) == "WIN");

        if ($is_windows)
        {
            if (is_dir($_SERVER["HOMEPATH"] . "\\.xRefCoreCompiler\\Core"))
            {
                FileDirectory::Delete($_SERVER["HOMEPATH"] . "\\.xRefCoreCompiler\\Core");
            }
            FileDirectory::Copy("phar://" . Application::GetExecutableFileName() . "/Core", $_SERVER["HOMEPATH"] . "\\.xRefCoreCompiler\\Core");
        }
        else
        {
            if (is_dir($_SERVER["HOME"] . "/.xRefCoreCompiler/Core"))
            {
                FileDirectory::Delete($_SERVER["HOME"] . "/.xRefCoreCompiler/Core");
            }
            FileDirectory::Copy("phar://" . Application::GetExecutableFileName() . "/Core", $_SERVER["HOME"] . "/.xRefCoreCompiler/Core");
        }
    }

    public static function PrepareProject() : void
    {
        $is_windows = (strtoupper(substr(PHP_OS, 0, 3)) == "WIN");
        $proj_name = basename(getcwd());
        if ($is_windows)
        {
            $target = $_SERVER["HOMEPATH"] . "\\.xRefCoreCompiler\\Core";
            $link = getcwd() . "\Core";
        }
        else
        {
            $target = $_SERVER["HOME"] . "/.xRefCoreCompiler/Core";
            $link = getcwd() . "/Core";
        }
        $result = @symlink($target, $link);
        if (!$result)
        {
            $w = "";
            if ($is_windows)
            {
                $w = "Please, set enable symlinks for non-admin users. ";
            }
            Console::WriteLine("Failed to create symlink. " . $w . " Delete 'Core' folder in your project if it's exists.", ForegroundColors::RED);
            exit;
        }

        @mkdir(getcwd() . DIRECTORY_SEPARATOR . $proj_name);
        @mkdir(getcwd() . DIRECTORY_SEPARATOR . $proj_name . DIRECTORY_SEPARATOR . "Program");
        $main = getcwd() . DIRECTORY_SEPARATOR . $proj_name . DIRECTORY_SEPARATOR . "Program" . DIRECTORY_SEPARATOR . "Main.php";
        $main_code = "PD9waHAKCm5hbWVzcGFjZSBQcm9ncmFtOwoKdXNlIElPXENvbnNvbGU7CnVzZSBBcHBsaWNhdGlvblxBcHBsaWNhdGlvbjsKCmNsYXNzIE1haW4KewogICAgcHVibGljIGZ1bmN0aW9uIF9fY29uc3RydWN0KGFycmF5ICRhcmdzKQogICAgewogICAgICAgIENvbnNvbGU6OldyaXRlTGluZSgiSGVsbG8gV29ybGQhIik7CiAgICB9Cn0=";
        if (!file_exists($main))
        {
            $f = fopen($main, "w");
            fwrite($f, base64_decode($main_code));
            fclose($f);
        }
        Console::WriteLine("Done! IMPORTANT: Execute " . ColoredString::Get("cd " . $proj_name, ForegroundColors::YELLOW) . " for moving to your project code.\nUse " . ColoredString::Get("xrefcore-compiler", ForegroundColors::YELLOW) . ColoredString::Get(" --skip 1", ForegroundColors::BROWN) . " to compile application.");
    }

    public static function Version() : void
    {
        Console::WriteLine("http://xrefcore.ru\nhttps://github.com/mamayadesu/xRefCoreCompiler\n\nxRefCoreCompiler v" . Application::GetFrameworkVersion());
    }
}