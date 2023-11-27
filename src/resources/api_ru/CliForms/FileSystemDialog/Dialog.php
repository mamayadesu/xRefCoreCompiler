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
 * Запускает консольный файловый менеджер для выбора файла для открытия, сохранения или открытия папки
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
     * Возвращает объект MenuBox текущего запущенного диалогового окна. Если диалоговое окно не запущено, метод вернёт NULL
     *
     * @return MenuBox|null
     */
    public static function GetMenuBox() : ?MenuBox
    {}

    /**
     * Изменяет язык псевдо-GUI
     *
     * @param array $locale Используйте один из констант класса `CliForms\FileSystemDialog\DialogLocales`
     * @return void
     */
    public static function SetLocale(array $locale) : void
    {}

    /**
     * Запускает консольный файловый менеджер для открытия файла
     *
     * @param string|null $defaultPath Файловый менеджер запустится с этой папки или домашнего каталога пользователя
     * @param string $defaultFileName Файловый менеджер начнёт с этого файла, если он существует
     * @return string|null Полный путь к выбранному файлу или NULL, если файл не выбран
     * @throws DialogAlreadyRunningException
     */
    public static function OpenFile(?string $defaultPath = null, string $defaultFileName = "") : ?string
    {}

    /**
     * Запускает консольный файловый менеджер для сохранения файла
     *
     * @param string|null $defaultPath Файловый менеджер запустится с этой папки или домашнего каталога пользователя
     * @param string $defaultFileName Файловый менеджер начнёт с этого файла, если он существует
     * @return string|null Полный путь к выбранному файлу или NULL, если файл не выбран
     * @throws DialogAlreadyRunningException
     */
    public static function SaveFile(?string $defaultPath = null, string $defaultFileName = "") : ?string
    {}

    /**
     * Запускает файловый менеджер для выбора каталога
     *
     * @param string|null $defaultPath Файловый менеджер запустится с этой папки или домашнего каталога пользователя
     * @return string|null Полный путь к выбранной папке или NULL, если папка не выбрана
     */
    public static function OpenFolder(?string $defaultPath = null) : ?string
    {}
}