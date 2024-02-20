<?php

declare(ticks=1);

namespace CliForms\FileSystemDialog;

use Application\Application;
use CliForms\Common\RowHeaderType;
use CliForms\FileSystemDialog\Exceptions\DialogAlreadyRunningException;
use CliForms\MenuBox\Events\ItemClickedEvent;
use CliForms\MenuBox\Events\KeyPressEvent;
use CliForms\MenuBox\Events\MenuBoxCloseEvent;
use CliForms\MenuBox\Events\MenuBoxOpenEvent;
use CliForms\MenuBox\Label;
use CliForms\MenuBox\MenuBox;
use CliForms\MenuBox\MenuBoxItem;
use Data\String\ForegroundColors;
use IO\Console;
use IO\FileDirectory;

/**
 * Launches a CLI file manager to select a file to open, save or open a folder
 */
class Dialog
{
    public static bool $HideDotFiles = true, $ExcludeWindowsSystemObjects = true;
    public static int $TableColumnsPadding = 32;

    public static string
        $LangSaveAs = "Save as...",
        $LangSaveAsLabel = "Save as... (input file name or leave line empty and press ENTER):",
        $LangIsDirectoryError = "%s0 is directory!",
        $LangClose = "Close",
        $LangOpenFile = "Open file",
        $LangSaveFile = "Save file",
        $LangOpenFolder = "Open folder",
        $LangOk = "OK",
        $LangError = "Error",
        $LangYes = "Yes",
        $LangNo = "No",
        $LangDirectoryNotFound = "Could not find directory %s0",
        $LangIsFileError = "%s0 is file! Please, open directory.",
        $LangInputOnlyFileName = "Please, input only file name!",
        $LangFileNotFound = "Could not find file %s0",
        $LangObjectNotFound = "No such file or directory: %s0",
        $LangLoadingDirectoryFailed = "Failed to open directory %s0 (%s1)",
        $LangToParentDirectory = "To parent directory",
        $LangInputPathToObject = "Input path to file or directory: ",
        $LangHelp = "Show help",
        $LangHideHelp = "Hide this text",
        $LangInputPath = "Input path to file or directory",
        $LangSelectDirectory = "Select this directory",
        $LangBack = "Back",
        $LangSelectDirectoryNote = "Also you can select any file to choose folder where you are.",
        $LangOverwriteConfirmation = "Do you really want to overwrite file %s0?";

    /**
     * @ignore
     */
    private static ?MenuBox $menu = null;

    /**
     * @ignore
     */
    private static bool $help = false, $confirmation = false;

    /**
     * @ignore
     */
    private static string $dialogType = "", $currentDirectory = "";

    /**
     * @ignore
     */
    private static array $generatedContent = [], $keyBinds = array(), $savedChoices = array(), $history = array();

    /**
     * @ignore
     */
    private static ?string $result = null;

    /**
     * Returns a MenuBox's object of current running Dialog. If Dialog is not running, this method will return NULL
     *
     * @return MenuBox|null
     */
    public static function GetMenuBox() : ?MenuBox
    {
        return self::$menu;
    }

    /**
     * Changes pseudo-GUI locale
     *
     * @param array $locale Use one of constants of the class `CliForms\FileSystemDialog\DialogLocales`
     * @return void
     */
    public static function SetLocale(array $locale) : void
    {
        foreach ($locale as $key => $value)
        {
            if (substr($key, 0, 4) != "Lang")
            {
                continue;
            }

            self::$$key = $value;
        }
    }

    /**
     * Launches CLI file manager to select file to open
     *
     * @param string|null $defaultPath File manager will start from this directory or from user's home path
     * @param string $defaultFileName File manager will start from this file if file exists
     * @return string|null Full path to selected file or NULL if no file selected
     * @throws DialogAlreadyRunningException
     */
    public static function OpenFile(?string $defaultPath = null, string $defaultFileName = "") : ?string
    {
        if (self::$menu !== null)
        {
            $e = new DialogAlreadyRunningException("Some dialog is already running! You can't start more than one dialog.");
            $e->__xrefcoreexception = true;
            throw $e;
        }
        if ($defaultPath !== null)
            $defaultPath = FileDirectory::FormatDirectoryPath($defaultPath);
        else
            $defaultPath = Application::GetHomeDirectory();

        self::$dialogType = "openfile";
        self::$currentDirectory = $defaultPath;

        $defaultFileName = str_replace(["/", "\\"], ["", ""], $defaultFileName);
        $defaultFileName = IS_WINDOWS ? strtolower($defaultFileName) : $defaultFileName;

        $menu = self::GenerateForm();
        if ($menu === null)
        {
            return null;
        }
        self::$keyBinds["f1"] = function(KeyPressEvent $event) : void
        {
            self::$help = !self::$help;

            $help  = "F1 - " . self::$LangHideHelp . "\n";
            $help .= "Q - " . self::$LangInputPath . "\n";
            $help .= "Escape - " . self::$LangBack . "\n";
            $help .= "X - " . self::$LangClose;

            $no_help = "F1 - " . self::$LangHelp;

            self::$menu->SetDescription(self::$help ? $help : $no_help);
        };
        if ($defaultFileName != "")
        {
            foreach (self::$generatedContent as $key => $obj)
            {
                if ($obj == null)
                    continue;

                $obj["name"] = IS_WINDOWS ? strtolower($obj["name"]) : $obj["name"];

                if ($obj["type"] == "file" && $obj["name"] == $defaultFileName)
                {
                    $menu->SetSelectedItemNumber($key);
                }
            }
        }

        $menu->Render();
        return self::$result;
    }

    /**
     * Launches CLI file manager to select file to overwrite or create a new file
     *
     * @param string|null $defaultPath File manager will start from this directory or from user's home path
     * @param string $defaultFileName File manager will start from this file if file exists
     * @return string|null Full path to saving file or NULL if no file selected
     * @throws DialogAlreadyRunningException
     */
    public static function SaveFile(?string $defaultPath = null, string $defaultFileName = "") : ?string
    {
        if (self::$menu !== null)
        {
            $e = new DialogAlreadyRunningException("Some dialog is already running! You can't start more than one dialog.");
            $e->__xrefcoreexception = true;
            throw $e;
        }
        if ($defaultPath !== null)
            $defaultPath = FileDirectory::FormatDirectoryPath($defaultPath);
        else
            $defaultPath = Application::GetHomeDirectory();

        self::$dialogType = "savefile";
        self::$currentDirectory = $defaultPath;

        $defaultFileName = str_replace(["/", "\\"], ["", ""], $defaultFileName);
        $defaultFileName = IS_WINDOWS ? strtolower($defaultFileName) : $defaultFileName;

        $menu = self::GenerateForm();
        if ($menu === null)
        {
            return null;
        }
        self::$keyBinds["f1"] = function(KeyPressEvent $event) : void
        {
            self::$help = !self::$help;

            $help  = "F1 - " . self::$LangHideHelp . "\n";
            $help .= "S - " . self::$LangSaveAs . "\n";
            $help .= "Q - " . self::$LangInputPath . "\n";
            $help .= "Escape - " . self::$LangBack . "\n";
            $help .= "X - " . self::$LangClose;

            $no_help = "F1 - " . self::$LangHelp;

            self::$menu->SetDescription(self::$help ? $help : $no_help);
        };

        self::$keyBinds["s"] = function(KeyPressEvent $event) : void
        {
            self::SaveAs();
        };

        if ($defaultFileName != "")
        {
            foreach (self::$generatedContent as $key => $obj)
            {
                if ($obj == null)
                    continue;

                $obj["name"] = IS_WINDOWS ? strtolower($obj["name"]) : $obj["name"];

                if ($obj["type"] == "file" && $obj["name"] == $defaultFileName)
                {
                    $menu->SetSelectedItemNumber($key);
                }
            }
        }

        $menu->Render();
        return self::$result;
    }

    /**
     * Launches CLI file manager to select a directory
     *
     * @param string|null $defaultPath File manager will start from this directory or from user's home path
     * @return string|null Full path to selected directory or NULL if no directory selected
     */
    public static function OpenFolder(?string $defaultPath = null) : ?string
    {
        if (self::$menu !== null)
        {
            $e = new DialogAlreadyRunningException("Some dialog is already running! You can't start more than one dialog.");
            $e->__xrefcoreexception = true;
            throw $e;
        }
        if ($defaultPath !== null)
            $defaultPath = FileDirectory::FormatDirectoryPath($defaultPath);
        else
            $defaultPath = Application::GetHomeDirectory();

        self::$dialogType = "openfolder";
        self::$currentDirectory = $defaultPath;

        $menu = self::GenerateForm();
        if ($menu === null)
        {
            return null;
        }
        self::$keyBinds["f1"] = function(KeyPressEvent $event) : void
        {
            self::$help = !self::$help;

            $help  = "F1 - " . self::$LangHideHelp . "\n";
            $help .= "S - " . self::$LangSelectDirectory . "\n";
            $help .= "Q - " . self::$LangInputPath . "\n";
            $help .= "Escape - " . self::$LangBack . "\n";
            $help .= "X - " . self::$LangClose . "\n";
            $help .= self::$LangSelectDirectoryNote;

            $no_help = "F1 - " . self::$LangHelp;

            self::$menu->SetDescription(self::$help ? $help : $no_help);
        };

        self::$keyBinds["s"] = function(KeyPressEvent $event) : void
        {
            self::$result = self::$currentDirectory;
            self::$menu->Close();
            self::$menu->Dispose();
            self::$menu = null;
        };

        $menu->Render();
        return self::$result;
    }

    /**
     * @ignore
     */
    private static function GetExcludedObjects() : array
    {
        if (IS_WINDOWS && self::$ExcludeWindowsSystemObjects)
        {
            // location:
            // drive - root of drive
            // * - anywhere
            // home - user's home directory
            return [
                array(
                    "object" => "Recent",
                    "location" => "home"
                ),
                array(
                    "object" => "desktop.ini",
                    "location" => "*"
                ),
                array(
                    "object" => "ntuser.dat*",
                    "location" => "home"
                ),
                array(
                    "object" => "ntuser.ini*",
                    "location" => "home"
                ),
                array(
                    "object" => "\$RECYCLE.BIN",
                    "location" => "drive"
                ),
                array(
                    "object" => "pagefile.sys",
                    "location" => "drive"
                ),
                array(
                    "object" => "swapfile.sys",
                    "location" => "drive"
                ),
                array(
                    "object" => "BOOTNXT",
                    "location" => "drive"
                ),
                array(
                    "object" => "BOOTSECT.BAK",
                    "location" => "drive"
                ),
                array(
                    "object" => "bootmgr",
                    "location" => "drive"
                ),
                array(
                    "object" => "System Volume Information",
                    "location" => "drive"
                )
            ];
        }
        else
        {
            return [];
        }
    }

    /**
     * @ignore
     */
    private static function GetTitle() : string
    {
        $title = "";

        switch (self::$dialogType)
        {
            case "openfile":
                $title = self::$LangOpenFile;
                break;

            case "savefile":
                $title = self::$LangSaveFile;
                break;

            case "openfolder":
                $title = self::$LangOpenFolder;
                break;
        }
        $title .= " | " . self::$currentDirectory;
        return $title;
    }

    /**
     * @ignore
     */
    private static function SaveAs() : void
    {
        Console::ClearWindow(self::$LangSaveAsLabel);
        $path = Console::ReadLine();
        if (str_replace(" ", "", $path) == "")
        {
            return;
        }

        if (strpos($path, "/") || strpos($path, "\\"))
        {
            $text = self::$LangInputOnlyFileName;
            self::GenerateError($text)->Render();
            return;
        }

        $file = FileDirectory::FormatDirectoryPath(self::$currentDirectory . $path);
        $exists = file_exists($file);
        if ($exists && !is_file($file))
        {
            $text = self::$LangIsDirectoryError;
            $text = str_replace("%s0", $file, $text);
            self::GenerateError($text)->Render();
            return;
        }
        $confirm = true;
        if ($exists)
        {
            $confirmation = self::GenerateConfirmation(str_replace("%s0", $file, self::$LangOverwriteConfirmation));
            $confirmation->Render();
            $confirm = self::$confirmation;
        }
        if ($confirm)
        {
            self::$result = $file;
            self::$menu->Close();
            self::$menu->Dispose();
            self::$menu = null;
        }
    }

    /**
     * @ignore
     */
    private static function InputPath() : void
    {
        Console::ClearWindow(self::$LangInputPathToObject);
        $path = Console::ReadLine();
        if (str_replace(" ", "", $path) == "")
        {
            return;
        }
        if (substr($path, 0, 1) != DIRECTORY_SEPARATOR && ((IS_WINDOWS && strpos($path, ":") === false) || !IS_WINDOWS))
        {
            $path = FileDirectory::FormatDirectoryPath(self::$currentDirectory . $path);
        }
        $exists = file_exists($path);
        $is_dir = is_dir($path);

        if ($exists)
        {
            if ($is_dir)
            {
                $old_path = self::$currentDirectory;
                self::$currentDirectory = FileDirectory::FormatDirectoryPath($path);
                self::$menu->Title = self::GetTitle();
                $items = self::GenerateDirectoryContentItems();
                if ($items === null)
                {
                    self::$currentDirectory = $old_path;
                    self::$menu->Title = self::GetTitle();
                    return;
                }
                self::$history[count(self::$history) - 1]["saved_choice"] = null;
                self::$history[] = [
                    "path" => self::$currentDirectory,
                    "saved_choice" => null
                ];
                self::$menu->PreventNextRefresh();
                self::$menu->ClearItems();
                foreach ($items as $item)
                {
                    self::$menu->PreventNextRefresh();
                    self::$menu->AddItem($item);
                }
                self::$menu->SetSelectedItemNumber(1);
            }
            else
            {
                if (self::$dialogType == "openfile")
                {
                    self::$result = FileDirectory::FormatDirectoryPath($path);
                    self::$menu->Close();
                    self::$menu->Dispose();
                    self::$menu = null;
                }
                else if (self::$dialogType == "savefile" || self::$dialogType == "openfolder")
                {
                    $text = self::$LangIsFileError;
                    $text = str_replace("%s0", $path, $text);
                    self::GenerateError($text)->Render();
                }
                /*else if (self::$dialogType == "openfolder")
                {
                    // ToDo
                }*/
            }
        }
        else
        {
            $text = self::$LangObjectNotFound;
            $text = str_replace("%s0", $path, $text);
            self::GenerateError($text)->Render();
        }
    }

    /**
     * @ignore
     */
    private static function GenerateForm() : ?MenuBox
    {
        self::$help = false;
        self::$result = null;
        self::$savedChoices = array();
        self::$history = array();
        $title = self::GetTitle();
        self::$history[] = [
            "path" => self::$currentDirectory,
            "saved_choice" => null
        ];
        if (!file_exists(self::$currentDirectory) || !is_dir(self::$currentDirectory))
        {
            $text = self::$LangDirectoryNotFound;
            $text = str_replace("%s0", self::$currentDirectory, $text);
            return self::GenerateError($text);
        }

        self::$menu = new MenuBox($title, new self);

        $items = self::GenerateDirectoryContentItems();

        if ($items === null)
        {
            return null;
        }

        foreach ($items as $item)
        {
            self::$menu->AddItem($item);
        }
        self::$menu->ItemsContainerHeight = 15;
        self::$menu->AutoSizeEnabled = true;
        self::$menu->SetRowsHeaderType(RowHeaderType::ARROW3);
        self::$menu->SetRowHeaderItemDelimiter(" ");
        self::$menu->SetDescription("F1 - " . self::$LangHelp);
        self::$keyBinds = array();
        self::$menu->KeyPressEvent = function(KeyPressEvent $event) : void
        {
            if (isset(self::$keyBinds[$event->Key]))
            {
                self::$keyBinds[$event->Key]($event);
            }
        };
        self::$menu->OpenEvent = function(MenuBoxOpenEvent $event) : void
        {
            Console::HideCursor();
        };
        self::$menu->CloseEvent = function(MenuBoxCloseEvent $event) : void
        {
            self::$savedChoices = array();
            self::$history = array();
            Console::ShowCursor();
        };
        self::$keyBinds["x"] = function(KeyPressEvent $event) : void
        {
            self::$result = null;
            self::$menu->Close();
            self::$menu->Dispose();
            self::$menu = null;
        };
        self::$keyBinds["q"] = function(KeyPressEvent $event) : void
        {
            self::InputPath();
        };
        self::$keyBinds["escape"] = function(KeyPressEvent $event) : void
        {
            if (count(self::$history) == 1)
            {
                return;
            }
            $new_path = self::$history[count(self::$history) - 2]["path"];
            $old_path = self::$currentDirectory;
            if (!file_exists($new_path) || !is_dir($new_path))
            {
                $text = self::$LangDirectoryNotFound;
                $text = str_replace("%s0", $new_path, $text);
                self::GenerateError($text)->Render();
                return;
            }

            unset(self::$savedChoices[self::$currentDirectory]);

            self::$currentDirectory = $new_path;
            array_pop(self::$history);
            self::$menu->Title = self::GetTitle();
            $items = self::GenerateDirectoryContentItems();

            if ($items === null)
            {
                self::$currentDirectory = $old_path;
                self::$menu->Title = self::GetTitle();
                return;
            }

            self::$history = array_values(self::$history);
            $history_count = count(self::$history);
            self::$menu->ClearItems();
            foreach ($items as $item)
            {
                self::$menu->AddItem($item);
            }

            $itemNumber = 1;
            if (self::$history[$history_count - 1]["saved_choice"] !== null)
            {
                foreach (self::$generatedContent as $key => $object)
                {
                    if ($object !== null && $object["name"] == self::$history[$history_count - 1]["saved_choice"])
                    {
                        $itemNumber = $key;
                        break;
                    }
                }
            }
            self::$history[$history_count - 1]["saved_choice"] = null;
            self::$menu->SetSelectedItemNumber($itemNumber);
        };
        return self::$menu;
    }

    /**
     * @ignore
     */
    private static function GenerateError(string $text) : MenuBox
    {
        $window = new MenuBox(self::$LangError, new self);
        $window->SetTitleColor(ForegroundColors::RED);
        $text = new Label($text);
        $text->SetItemStyle(ForegroundColors::WHITE);
        $button = new MenuBoxItem(self::$LangOk . "   ", "", function(ItemClickedEvent $event) : void
        {
            $event->MenuBox->Close();
            $event->MenuBox->Dispose();
        });
        $window->SetRowsHeaderType(RowHeaderType::NONE);
        $window->SetRowHeaderItemDelimiter("   ");

        $window->AddItem($text)->AddItem($button);

        return $window;
    }

    /**
     * @ignore
     */
    private static function GenerateConfirmation(string $text) : MenuBox
    {
        $window = new MenuBox("", new self);
        $text = new Label($text);
        $text->SetItemStyle(ForegroundColors::YELLOW);
        $yes = new MenuBoxItem(self::$LangYes . "   ", "", function(ItemClickedEvent $event) : void
        {
            self::$confirmation = true;
            $event->MenuBox->Close();
            $event->MenuBox->Dispose();
        });
        $no = new MenuBoxItem(self::$LangNo . "   ", "", function(ItemClickedEvent $event) : void
        {
            self::$confirmation = false;
            $event->MenuBox->Close();
            $event->MenuBox->Dispose();
        });
        $window->SetRowsHeaderType(RowHeaderType::NONE);
        $window->SetRowHeaderItemDelimiter("   ");

        $window->AddItem($text)->AddItem($yes)->AddItem($no);

        return $window;
    }

    /**
     * @ignore
     */
    private static function GenerateDirectoryContentItems() : ?array
    {
        $path = self::$currentDirectory;

        $result = [];
        $files = [];
        $directories = [];

        set_error_handler(function($errno, $errstr, $errfile, $errline)
        {
            throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
        });

        try
        {
            $objects = scandir($path);
        }
        catch (\ErrorException $e)
        {
            $message = $e->getMessage();
            $message = str_replace("scandir(" . $path . "): ", "", $message);
            $message = str_replace("failed to open dir: ", "", $message);
            $text = self::$LangLoadingDirectoryFailed;
            $text = str_replace("%s0", $path, $text);
            $text = str_replace("%s1", $message, $text);
            self::GenerateError($text)->Render();
            return null;
        }

        restore_error_handler();

        foreach ($objects as $object)
        {
            if ($object == "." || $object == "..")
                continue;

            if (self::$HideDotFiles && substr($object, 0, 1) == ".")
                continue;
            $is_drive = substr($path, 1) == ":\\";
            $is_home = (IS_WINDOWS && strtolower(Application::GetHomeDirectory()) == strtolower($path)) || (!IS_WINDOWS && Application::GetHomeDirectory() == $path);
            foreach (self::GetExcludedObjects() as $template)
            {
                $a = explode('*', $template["object"])[0];
                $b = substr($object, 0, strlen($a));
                if (IS_WINDOWS)
                {
                    $a = strtolower($a);
                    $b = strtolower($b);
                }
                if (
                    (
                        (
                            $template["location"] == "drive" && $is_drive
                        ) ||
                        (
                            $template["location"] == "home" && $is_home
                        ) ||
                        (
                            $template["location"] == "*"
                        )
                    ) && $a == $b)
                {
                    continue 2;
                }
            }

            $type = is_file($path . $object) ? "file" : "directory";
            $data = array(
                "name" => $object,
                "type" => $type,
                "size" => $type == "file" ? @filesize($path . $object) : 0,
                "modified" => @filemtime($path . $object)
            );
            if (is_file($path . $object))
            {
                $files[] = $data;
            }
            else
            {
                $directories[] = $data;
            }
        }

        $content = $directories;
        self::$generatedContent = array_merge([null, null], array_merge($content, $files));

        $result[] = new MenuBoxItem(self::$LangToParentDirectory, "", function(ItemClickedEvent $event) : void
        {
            $new_path = FileDirectory::FormatDirectoryPath(self::$currentDirectory . "..");
            $old_path = self::$currentDirectory;
            if (!file_exists($new_path) || !is_dir($new_path))
            {
                $text = self::$LangDirectoryNotFound;
                $text = str_replace("%s0", $new_path, $text);
                self::GenerateError($text)->Render();
                return;
            }

            unset(self::$savedChoices[self::$currentDirectory]);

            self::$currentDirectory = $new_path;
            self::$menu->Title = self::GetTitle();
            $items = self::GenerateDirectoryContentItems();

            if ($items === null)
            {
                self::$currentDirectory = $old_path;
                self::$menu->Title = self::GetTitle();
                return;
            }
            self::$history[count(self::$history) - 1]["saved_choice"] = null;
            self::$history[] = [
                "path" => $new_path,
                "saved_choice" => null
            ];
            self::$menu->ClearItems();
            foreach ($items as $item)
            {
                self::$menu->AddItem($item);
            }

            $itemNumber = 1;
            if (isset(self::$savedChoices[self::$currentDirectory]))
            {
                foreach (self::$generatedContent as $key => $object)
                {
                    if ($object !== null && $object["name"] == self::$savedChoices[self::$currentDirectory])
                    {
                        $itemNumber = $key;
                        break;
                    }
                }
            }

            self::$menu->SetSelectedItemNumber($itemNumber);
        });

        foreach (self::$generatedContent as $item)
        {
            if ($item === null)
                continue;
            $object_name = $item["name"];
            if ($item["type"] == "directory")
            {
                $object_name .= DIRECTORY_SEPARATOR;
            }
            $object_name_length = mb_strlen($object_name);
            if ($object_name_length > self::$TableColumnsPadding)
            {
                $object_name = mb_substr($object_name, 0, self::$TableColumnsPadding - 1) . "…";
            }
            else
            {
                $object_name .= str_repeat(" ", self::$TableColumnsPadding - $object_name_length);
            }

            $size_str = "";

            if ($item["type"] == "file")
            {
                if ($item["size"] < 1024)
                {
                    $size_str = $item["size"] . " B";
                }
                else if ($item["size"] < 1024 * 1024)
                {
                    $size_str = round($item["size"] / 1024, 2) . " KB";
                }
                else if ($item["size"] < 1024 * 1024 * 1024)
                {
                    $size_str = round($item["size"] / 1024 / 1024, 2) . " MB";
                }
                else if ($item["size"] < 1024 * 1024 * 1024 * 1024)
                {
                    $size_str = round($item["size"] / 1024 / 1024 / 1024, 2) . " GB";
                }
                else if ($item["size"] < 1024 * 1024 * 1024 * 1024 * 1024)
                {
                    $size_str = round($item["size"] / 1024 / 1024 / 1024 / 1024, 2) . " TB";
                }

                $size_str_length = strlen($size_str);
                if ($size_str_length > self::$TableColumnsPadding)
                {
                    $size_str = substr($size_str, 0, self::$TableColumnsPadding - 1) . "…";
                }
                else
                {
                    $size_str .= str_repeat(" ", self::$TableColumnsPadding - $size_str_length);
                }
            }

            $modified_str = date("d.m.Y H:i:s", $item["modified"]);

            $result[] = new MenuBoxItem($object_name . $size_str . $modified_str, "", function(ItemClickedEvent $event) : void
            {
                $index = $event->ItemNumber;
                $object = self::$generatedContent[$index];
                $object_name = $object["name"];
                $object_type = $object["type"];

                if ($object_type == "directory")
                {
                    $new_path = FileDirectory::FormatDirectoryPath(self::$currentDirectory . $object_name);
                    $old_path = self::$currentDirectory;
                    if (!file_exists($new_path) || !is_dir($new_path))
                    {
                        $text = self::$LangDirectoryNotFound;
                        $text = str_replace("%s0", $new_path, $text);
                        self::GenerateError($text)->Render();
                        return;
                    }

                    self::$savedChoices[self::$currentDirectory] = $object_name;
                    self::$currentDirectory = $new_path;

                    self::$menu->Title = self::GetTitle();
                    $items = self::GenerateDirectoryContentItems();

                    if ($items === null)
                    {
                        self::$currentDirectory = $old_path;
                        self::$menu->Title = self::GetTitle();
                        return;
                    }
                    self::$history[count(self::$history) - 1]["saved_choice"] = $object_name;
                    self::$history[] = [
                        "path" => $new_path,
                        "saved_choice" => null
                    ];
                    self::$menu->ClearItems();
                    foreach ($items as $item)
                    {
                        self::$menu->AddItem($item);
                    }

                    self::$menu->SetSelectedItemNumber(1);
                }
                else
                {
                    if (self::$dialogType == "openfile")
                    {
                        $file = FileDirectory::FormatDirectoryPath(self::$currentDirectory . $object_name);
                        if (!file_exists($file) || !is_file($file))
                        {
                            $text = self::$LangFileNotFound;
                            $text = str_replace("%s0", $file, $text);
                            self::GenerateError($text)->Render();
                            return;
                        }
                        self::$result = $file;
                        self::$menu->Close();
                        self::$menu->Dispose();
                        self::$menu = null;
                    }
                    else if (self::$dialogType == "savefile")
                    {
                        $file = FileDirectory::FormatDirectoryPath(self::$currentDirectory . $object_name);
                        $exists = file_exists($file);
                        if ($exists && !is_file($file))
                        {
                            $text = self::$LangIsDirectoryError;
                            $text = str_replace("%s0", $file, $text);
                            self::GenerateError($text)->Render();
                            return;
                        }
                        $confirm = true;
                        if ($exists)
                        {
                            $confirmation = self::GenerateConfirmation(str_replace("%s0", $file, self::$LangOverwriteConfirmation));
                            $confirmation->Render();
                            $confirm = self::$confirmation;
                        }
                        if ($confirm)
                        {
                            self::$result = $file;
                            self::$menu->Close();
                            self::$menu->Dispose();
                            self::$menu = null;
                        }
                    }
                    else if (self::$dialogType == "openfolder")
                    {
                        self::$result = self::$currentDirectory;
                        self::$menu->Close();
                        self::$menu->Dispose();
                        self::$menu = null;
                    }
                }
            });
        }
        return $result;
    }
}