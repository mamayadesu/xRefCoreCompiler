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
        $home = Application::GetHomeDirectory();
        if (IS_WINDOWS)
        {
            if (is_dir($home . "\\.xRefCoreCompiler\\Core"))
            {
                FileDirectory::Delete($home . "\\.xRefCoreCompiler\\Core");
            }
            FileDirectory::Copy("phar://" . Application::GetExecutableFileName() . "/api", $home . "\\.xRefCoreCompiler\\Core");
        }
        else
        {
            if (!Application::AmIRunningAsSuperuser())
            {
                Console::WriteLine("Please run this command as root or through sudo.", ForegroundColors::YELLOW);
                return;
            }
            $share = "/usr/share/xRefCoreCompiler/Core";
            if (is_dir($share))
            {
                FileDirectory::Delete($share);
            }
            @mkdir("/usr/share/xRefCoreCompiler");
            @mkdir($share);
            FileDirectory::Copy("phar://" . Application::GetExecutableFileName() . "/api", $share);
            FileDirectory::RecursiveChmod(755, $share . "/..");
        }
    }

    public static function PrepareProject() : void
    {
        $proj_name = basename(getcwd());
        $home = Application::GetHomeDirectory();
        if (IS_WINDOWS)
        {
            $target = $home . "\\.xRefCoreCompiler\\Core";
            $link = getcwd() . "\Core";
        }
        else
        {
            $target = "/usr/share/xRefCoreCompiler/Core";
            $link = getcwd() . "/Core";
        }
        $result = @symlink($target, $link);
        if (!$result)
        {
            $w = "";
            if (IS_WINDOWS)
            {
                $w = "Please, enable symlinks for non-admin users. ";
            }
            Console::WriteLine("Failed to create symlink. " . $w . " Delete 'Core' folder in your project if it's exists.", ForegroundColors::RED);
            return;
        }

        @mkdir(getcwd() . DIRECTORY_SEPARATOR . $proj_name);
        @mkdir(getcwd() . DIRECTORY_SEPARATOR . $proj_name . DIRECTORY_SEPARATOR . "Program");
        $main = getcwd() . DIRECTORY_SEPARATOR . $proj_name . DIRECTORY_SEPARATOR . "Program" . DIRECTORY_SEPARATOR . "Main.php";
        $main_code = "PD9waHAKZGVjbGFyZSh0aWNrcyA9IDEpOwoKbmFtZXNwYWNlIFByb2dyYW07Cgp1c2UgSU9cQ29uc29sZTsKdXNlIEFwcGxpY2F0aW9uXEFwcGxpY2F0aW9uOwoKY2xhc3MgTWFpbgp7CiAgICBwdWJsaWMgZnVuY3Rpb24gX19jb25zdHJ1Y3QoYXJyYXkgJGFyZ3MpCiAgICB7CiAgICAgICAgQ29uc29sZTo6V3JpdGVMaW5lKCJIZWxsbyBXb3JsZCEiKTsKICAgIH0KfQ==";
        if (!file_exists($main))
        {
            $f = fopen($main, "w");
            fwrite($f, base64_decode($main_code));
            fclose($f);
        }
        Console::WriteLine("Done! IMPORTANT: Execute " . ColoredString::Get("cd " . $proj_name, ForegroundColors::YELLOW) . " to moving to your project code.\nUse " . ColoredString::Get("xrefcore-compiler", ForegroundColors::YELLOW) . ColoredString::Get(" --build", ForegroundColors::BROWN) . " to compile application.");
    }

    public static function Version() : void
    {
        Console::WriteLine("http://xrefcore.ru\nhttps://github.com/mamayadesu/xRefCoreCompiler\nhttps://vk.com/id155272407\nmamayadesu@gmail.com\n\nxRefCoreCompiler v" . Application::GetFrameworkVersion());
    }

    public static function Help() : void
    {
        $usage = ColoredString::Get("xrefcore-compiler", ForegroundColors::WHITE) . "\n    ";
        $usage .= ColoredString::Get("-h", ForegroundColors::YELLOW) . " " . ColoredString::Get("OR", ForegroundColors::GRAY) . " " . ColoredString::Get("--help", ForegroundColors::YELLOW) . " " . ColoredString::Get(" - Displays this help text", ForegroundColors::PURPLE) . "\n    ";
        $usage .= ColoredString::Get("-v", ForegroundColors::YELLOW) . " " . ColoredString::Get("OR", ForegroundColors::GRAY) . " " . ColoredString::Get("--version", ForegroundColors::YELLOW) . " " . ColoredString::Get(" - Check xRefCoreCompiler version", ForegroundColors::PURPLE) . "\n    ";
        $usage .= ColoredString::Get("-c", ForegroundColors::YELLOW) . " " . ColoredString::Get("OR", ForegroundColors::GRAY) . " " . ColoredString::Get("--configure", ForegroundColors::YELLOW) . " " . ColoredString::Get(" - Configure xRefCoreCompiler (use it when it's just installed or updated)", ForegroundColors::PURPLE) . "\n    ";
        $usage .= ColoredString::Get("-p", ForegroundColors::YELLOW) . " " . ColoredString::Get("OR", ForegroundColors::GRAY) . " " . ColoredString::Get("--prepare-project", ForegroundColors::YELLOW) . " " . ColoredString::Get(" - Generates the main class and symlink in your PhpStorm project. Use it when project just created.", ForegroundColors::PURPLE) . "\n    ";
        $usage .= ColoredString::Get("-b", ForegroundColors::YELLOW) . " " . ColoredString::Get("OR", ForegroundColors::GRAY) . " " . ColoredString::Get("--build", ForegroundColors::YELLOW) . " " . ColoredString::Get(" - Builds application", ForegroundColors::PURPLE);
        //$usage .= ColoredString::Get("-b", ForegroundColors::YELLOW) . " " . ColoredString::Get("OR", ForegroundColors::GRAY) . " " . ColoredString::Get("--build", ForegroundColors::YELLOW) . " " . ColoredString::Get(" - Builds application\n    ", ForegroundColors::PURPLE);
        //$usage .= ColoredString::Get("-d", ForegroundColors::YELLOW) . " " . ColoredString::Get("OR", ForegroundColors::GRAY) . " " . ColoredString::Get("--debug", ForegroundColors::YELLOW) . " " . ColoredString::Get(" - Enables debug mode for compiling application. Use this option with --build", ForegroundColors::PURPLE);
        Console::WriteLine($usage);
    }
}