<?php

namespace Program;

use \IO\Console;
use \IO\FileDirectory;
use \Application\Application;
use \Phar;
use pocketmine\level\sound\EndermanTeleportSound;
use \RecursiveIteratorIterator;
use \Exception;

class Main
{
    private array $appPropertyToName;
    private bool $debugMode;

    public function __construct(array $args)
    {
        Console::WriteLine("********** xRefCoreCompiler **********");
        Console::WriteLine("Version: " . Application::GetFrameworkVersion());
        $ds = DIRECTORY_SEPARATOR;
        $this->InitAppPropertyToName();
        $projectDir = Application::GetExecutableDirectory();
        $pargs = Application::ParseArguments($args, "--");
        $projectDirSetManually = false;
        if (isset($pargs["arguments"]["projectdir"]))
        {
            if (is_dir($pargs["arguments"]["projectdir"]))
            {
                $projectDir = $pargs["arguments"]["projectdir"];
                if (str_split($projectDir)[strlen($projectDir) - 1] != $ds)
                {
                    $projectDir .= $ds;
                }
                $projectDirSetManually = true;
            }
            else
            {
                Console::WriteLine("Folder " . $pargs["arguments"]["projectdir"] . " not found");
            }
        }
        $this->debugMode = false;
        if (isset($pargs["arguments"]["debug"]))
        {
            $this->debugMode = ($pargs["arguments"]["debug"] == "1");
        }
        $skip = false;
        if (isset($pargs["arguments"]["skip"]))
        {
            $skip = ($pargs["arguments"]["skip"] == "1" ? true : false);
        }
        if (!$skip)
        {
            sleep(2);
        }
        $appJson = __GET__APP();
        $appJsonString = json_encode($appJson, JSON_PRETTY_PRINT);
        foreach ($appJson as $key => $value)
        {
            if (is_string($value))
            {
                $appJson[$key] = "";
            }
            if (is_int($value))
            {
                $appJson[$key] = 0;
            }
            if (is_array($value))
            {
                $appJson[$key] = array();
            }
            if (is_bool($value))
            {
                $appJson[$key] = false;
            }
        }
        $a = "";
        if (file_exists($projectDir . "Program" . $ds . "Main.php"))
        {
            if (!$skip)
            {
                Console::WriteLine("File '" . $ds . "Program" . $ds . "Main.php' was found in this folder");
                Console::Write("Do you want to build application from this folder? (y - yes; n - no): ");
                $a = strtolower(Console::ReadLine());
                if ($a != "y")
                {
                    $projectDir = $this->ProjectDir();
                }
            }
        }
        else if ($projectDirSetManually)
        {
            if (!is_dir($projectDir))
            {
                Console::WriteLine("Folder '" . $projectDir . "' not found");
            }
            if (!file_exists($projectDir . "Program" . $ds . "Main.php"))
            {
                Console::WriteLine("File '" . $ds . "Program" . $ds . "Main.php' not found in '" . $projectDir . "'");
            }
            $projectDir = $this->ProjectDir();
        }
        else
        {
            $projectDir = $this->ProjectDir();
        }
        sleep(1);
        $appJsonError = false;
        $a = "n";
        if (file_exists($projectDir . "app.json"))
        {
            if (!$skip)
            {
                Console::Write("File 'app.json' was found in this folder. Do you want to use it as app config? (y - yes; n - no): ");
                $a = strtolower(Console::ReadLine());
                sleep(1);
            }
            if ($a == "y" || $skip)
            {
                $appJson = json_decode(file_get_contents($projectDir . "app.json"), true);
                if ($appJson == null)
                {
                    Console::WriteLine("Incorrect 'app.json'");
                    $appJson = $this->AppJson();
                }
                else
                {
                    $notContainsProperties = [];
                    foreach ($this->appPropertyToName as $key => $value)
                    {
                        if (!isset($appJson[$key]))
                        {
                            $notContainsProperties[] = $value . " (" . $key . ")";
                        }
                    }
                    if (count($notContainsProperties) > 0)
                    {
                        Console::WriteLine("Error! 'app.json' doesn't contains next fields: " . implode(', ', $notContainsProperties));
                        Console::WriteLine("Fill application data manually.\n");
                        $appJson = $this->AppJson();
                    }
                }
                $appJsonString = json_encode($appJson, JSON_PRETTY_PRINT);
            }
        }
        else
        {
            $appJsonError = true;
        }
        if ($appJsonError || ($a != "y" && !$skip))
        {
            Console::WriteLine("");
            $appJson = $this->AppJson();
            sleep(1);
            Console::Write("Do you want to save application config to project directory? (y - yes; n - no): ");
            $a = strtolower(Console::ReadLine());
            sleep(1);
            $appJsonString = json_encode($appJson, JSON_PRETTY_PRINT);
            if ($a == "y")
            {
                $f = fopen($projectDir . "app.json", "w");
                fwrite($f, $appJsonString);
            }
        }
        if (!$skip)
        {
            Console::Write("Enable debug mode? (y - yes; n - no): ");
            $this->debugMode = (Console::ReadLine() == "y");
        }
        $appJsonString = json_encode($appJson, JSON_PRETTY_PRINT);
        $tempDir = Application::GetExecutableDirectory() . $ds . md5($appJsonString) . $ds;
        while (is_dir($tempDir))
        {
            Console::WriteLine("Temporary folder " . $tempDir . " already exists. Please, delete it and then press Enter to try again.");
            Console::ReadLine();
        }
        $otherProjectFolders = [];
        foreach (scandir($projectDir) as $dirname)
        {
            if ($dirname == "." || $dirname == ".." || !is_dir($projectDir . $dirname) || $dirname == "Core")
            {
                continue;
            }
            $otherProjectFolders[] = $dirname;
        }
        $appFileOutput = basename($projectDir) . ".phar";
        $appFileOutput = $projectDir . $appFileOutput;
        Console::WriteLine("Copying files...");
        $this->CopyFiles($projectDir, $tempDir, $otherProjectFolders);
        $f = fopen($tempDir . "app.json", "w");
        fwrite($f, $appJsonString);
        fclose($f);
        Console::WriteLine("Compiling...");
        $isSuccess = $this->MakeApp($tempDir, $appFileOutput);
        Console::WriteLine("Clearing...");
        FileDirectory::Delete($tempDir);

        $text = "";
        if ($isSuccess)
        {
            $text = "Done! Application was saved as " . $appFileOutput . ". ";
        }
        $text .= ($skip ? "Press ENTER to close" : "");
        Console::WriteLine($text);
        if (!$skip)
        {
            Console::ReadLine();
        }
    }

    public function MakeApp(string $dir, string $filename) : bool
    {
        try
        {
            $phar = new \Phar($filename);
        }
        catch (Exception $e)
        {
            Console::WriteLine("Failed to pack (C2). " . $e->getMessage());
            @rmdir($dir);
            return false;
        }
        $phar->setSignatureAlgorithm(\Phar::SHA512);
        $phar->startBuffering();
        try
        {
            $phar->buildFromDirectory($dir);
        }
        catch (Exception $e)
        {
            Console::WriteLine("Failed to pack (C3). " . $e->getMessage());
            @rmdir($dir);
            return false;
        }
        $phar->compressFiles(\Phar::GZ);
        $phar->stopBuffering();
        $phar->setStub("<?php Phar::mapPhar(); include 'phar://' . __FILE__ . '/autoload.php'; __HALT_COMPILER();");
        FileDirectory::Delete($dir);
        return true;
    }

    public function CopyFiles(string $projectDir, string $tempDir, array $otherProjectFolders)
    {
        @mkdir($tempDir);
        $ds = DIRECTORY_SEPARATOR;
        $p = null;
        $ptp = "phar://" . str_replace("\\", "/", Application::GetExecutableFileName()) . "/";
        $pathInPhar = "";
        $fullPathInPhar = "";
        $inRootFolder = "";
        $sPathInPhar = [];
        $folderToMake = "";
        $fileContents = "";
        $f = null;
        try
        {
            $p = new Phar(Application::GetExecutableFileName());
            foreach (new RecursiveIteratorIterator($p) as $file)
            {
                $fullPathInPhar = str_replace("\\", "/", $file->getPathName());
                $pathInPhar = str_replace($ptp, "", $fullPathInPhar);
                $sPathInPhar = explode('/', $pathInPhar);
                if ($pathInPhar == "app.json" || $sPathInPhar[0] == "Program")
                {
                    continue;
                }
                if (($pathInPhar == "autoload.php" && $this->debugMode) || ($pathInPhar == "autoload_dev.php" && !$this->debugMode))
                {
                    continue;
                }
                $folderToMake = $tempDir;
                for ($i = 0; $i < count($sPathInPhar) - 1; $i++)
                {
                    $folderToMake .= $sPathInPhar[$i] . $ds;
                    @mkdir($folderToMake);
                }
                $fileContents = file_get_contents($fullPathInPhar);
                if ($pathInPhar == "autoload_dev.php")
                {
                    $pathInPhar = "autoload.php";
                }
                $f = fopen($tempDir . str_replace("/", $ds, $pathInPhar), "w");
                fwrite($f, $fileContents);
                fclose($f);
            }
        }
        catch (Exception $e)
        {
            Console::WriteLine("An error occured while packing (C1): " . $e->getMessage());
            exit;
        }
        foreach ($otherProjectFolders as $folder)
        {
            FileDirectory::Copy($projectDir . $folder, $tempDir . $folder);
        }
    }

    public function InitAppPropertyToName() : void
    {
        $this->appPropertyToName = array
        (
            "php_version" => "PHP-version",
            "app_name" => "Application name",
            "app_version" => "Version",
            "app_author" => "Author",
            "app_description" => "Description",
            "namespaces" => "Using namespaces",
            "priorities" => "Priority classes"
        );
    }

    public function AppProperty(string $propertyName) : string
    {
        if (!isset($this->appPropertyToName))
        {
            return $propertyName;
        }
        return $this->appPropertyToName[$propertyName];
    }

    public function ProjectDir() : string
    {
        $projectDir = "";
        $ds = DIRECTORY_SEPARATOR;
        while ($projectDir == "")
        {
            Console::WriteLine("Your project must contains '" . $ds . "Program" . $ds . "Main.php'. It will be used as main class of application");
            Console::Write("Input path to project directory: ");
            $projectDir = Console::ReadLine();
            if ($projectDir == "")
            {
                continue;
            }
            if ($ds != "\\")
            {
                $projectDir = str_replace("/", "\\", $projectDir);
            }
            if (str_split($projectDir)[strlen($projectDir) - 1] != $ds)
            {
                $projectDir .= $ds;
            }
            if (!is_dir($projectDir))
            {
                Console::WriteLine("Directory not found");
                $projectDir = "";
            }
            if (!file_exists($projectDir . "Program" . $ds . "Main.php"))
            {
                Console::WriteLine("File '" . $ds . "Program" . $ds . "Main.php' not found in project directory");
                $projectDir = "";
            }
        }
        return $projectDir;
    }

    public function AppJson() : array
    {
        $a = "";
        $appJson = array();
        foreach ($this->appPropertyToName as $propRealName => $title)
        {
            if ($propRealName == "namespaces" || $propRealName == "priorities")
            {
                if (!isset($appJson[$propRealName]))
                {
                    $appJson[$propRealName] = [];
                }
                $a = "";
                Console::WriteLine($title . ":");
                Console::WriteLine("Input value on each line. In the end write #stop");
                while ($a != "#stop" || $a == "")
                {
                    $a = Console::ReadLine();
                    if ($a == "")
                    {
                        continue;
                    }
                    if ($a == "Program")
                    {
                        Console::WriteLine("Do not put 'Program', because it is a default namespace.");
                        continue;
                    }
                    if (strpos("\\", $a) !== false || strpos("/", $a) !== false)
                    {
                        Console::WriteLine("Input ONLY roots of namespaces.");
                        continue;
                    }
                    if ($a == "#stop")
                    {
                        break;
                    }
                    $appJson[$propRealName][] = $a;
                }
            }
            else
            {
                $a = "";
                while ($a == "")
                {
                    Console::Write($title . ": ");
                    $a = Console::ReadLine();
                    $appJson[$propRealName] = $a;
                    if ($propRealName == "app_description")
                    {
                        break;
                    }
                }
            }
        }
        return $appJson;
    }
}