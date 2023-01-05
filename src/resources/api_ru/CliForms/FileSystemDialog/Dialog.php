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
    public static int $TableColumnsPadding = 22;

    public static string
        $LangSaveAs = "Сохранить как...",
        $LangSaveAsLabel = "Сохранить как... (введите имя файла или оставьте строку ввода пустым и нажмите ENTER):",
        $LangIsDirectoryError = "%s0 является каталогом!",
        $LangClose = "Закрыть",
        $LangOpenFile = "Открыть файл",
        $LangSaveFile = "Сохранить файл",
        $LangOpenFolder = "Открыть папку",
        $LangOk = "OK",
        $LangError = "Ошибка",
        $LangYes = "Да",
        $LangNo = "Нет",
        $LangDirectoryNotFound = "Не удалось найти папку %s0",
        $LangIsFileError = "%s0 является файлом! Пожалуйста, выберите папку.",
        $LangInputOnlyFileName = "Пожалуйста, введите только имя файла!",
        $LangFileNotFound = "Не удалось найти файл %s0",
        $LangObjectNotFound = "Системе не удаётся найти указанный путь: %s0",
        $LangLoadingDirectoryFailed = "Произошла ошибка при открытии каталога %s0 (%s1)",
        $LangToParentDirectory = "Наверх",
        $LangInputPathToObject = "Введите путь к файлу или каталогу: ",
        $LangHelp = "Помощь",
        $LangHideHelp = "Скрыть",
        $LangInputPath = "Ввести путь к папке или файлу",
        $LangSelectDirectory = "Выбрать эту папку",
        $LangSelectDirectoryNote = "Вы также можете выбрать любой файл в этой папке.",
        $LangOverwriteConfirmation = "Файл %s0 уже существует. Сохранить его?";

    /**
     * Возвращает объект MenuBox текущего запущенного диалогового окна. Если диалоговое окно не запущено, метод вернёт NULL
     *
     * @return MenuBox|null
     */
    public static function GetMenuBox() : ?MenuBox
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