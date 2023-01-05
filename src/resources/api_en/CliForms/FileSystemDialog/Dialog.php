<?php

namespace CliForms\FileSystemDialog;

use Application\Application;
use CliForms\Common\RowHeaderType;
use CliForms\FileSystemDialog\Exceptions\DialogAlreadyRunningException;
use CliForms\MenuBox\Events\ItemClickedEvent;
use CliForms\MenuBox\Events\KeyPressEvent;
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
    public static int $TableColumnsPadding = 22;

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
        $LangSelectDirectoryNote = "Also you can select any file to choose folder where you are.",
        $LangOverwriteConfirmation = "Do you really want to overwrite file %s0?";

    /**
     * Returns a MenuBox's object of current running Dialog. If Dialog is not running, this method will return NULL
     *
     * @return MenuBox|null
     */
    public static function GetMenuBox() : ?MenuBox
    {}

    /**
     * Launches CLI file manager to select file to open
     *
     * @param string|null $defaultPath File manager will start from this directory or from user's home path
     * @param string $defaultFileName File manager will start from this file if file exists
     * @return string|null Full path to selected file or NULL if no file selected
     * @throws DialogAlreadyRunningException
     */
    public static function OpenFile(?string $defaultPath = null, string $defaultFileName = "") : ?string
    {}

    /**
     * Launches CLI file manager to select file to overwrite or create a new file
     *
     * @param string|null $defaultPath File manager will start from this directory or from user's home path
     * @param string $defaultFileName File manager will start from this file if file exists
     * @return string|null Full path to saving file or NULL if no file selected
     * @throws DialogAlreadyRunningException
     */
    public static function SaveFile(?string $defaultPath = null, string $defaultFileName = "") : ?string
    {}

    /**
     * Launches CLI file manager to select a directory
     *
     * @param string|null $defaultPath File manager will start from this directory or from user's home path
     * @return string|null Full path to selected directory or NULL if no directory selected
     */
    public static function OpenFolder(?string $defaultPath = null) : ?string
    {}
}