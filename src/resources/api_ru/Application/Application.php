<?php

namespace Application;

final class Application
{

    /**
     * Возвращает минимальную поддерживаемую приложением версию PHP
     *
     * @return string Необходимая версия PHP
     */
    public static function GetRequiredPhpVersion() : string
    {}

    /**
     * Возвращает название приложения
     *
     * @return string Название приложения
     */
    public static function GetName() : string
    {}

    /**
     * Возвращает описание приложения
     *
     * @return string Описание приложения
     */
    public static function GetDescription() : string
    {}

    /**
     * Возвращает имя автора приложения
     *
     * @return string Имя автора приложения
     */
    public static function GetAuthor() : string
    {}

    /**
     * Возвращает версию приложения
     *
     * @return string Версия приложения
     */
    public static function GetVersion() : string
    {}

    /**
     * Возвращает полный путь к исполняемому файлу
     *
     * @return string Полный путь к исполняемому файлу
     */
    public static function GetExecutableFileName() : string
    {}

    /**
     * Возвращает полный путь к рабочему каталогу приложения
     *
     * @return string Полный путь к рабочему каталогу
     */
    public static function GetExecutableDirectory() : string
    {}

    /**
     * Возвращает текущую версию xRefCore
     *
     * @return string Версия фреймворка
     */
    public static function GetFrameworkVersion() : string
    {}

    /**
     * Устанавливает имя процесса (В Windows - заголовок окна)
     *
     * @param string $title Имя процесса
     */
    public static function SetTitle(string $title) : void
    {}

    /**
     * Возвращает имя пользователя системы
     *
     * @return string
     */
    public static function GetUsername() : string
    {}

    /**
     * Возвращает путь к домашнему каталогу пользователя (В Windows это C:\Users\your_username, В *unix-системах это /home/your_username или /root)
     *
     * @return string
     */
    public static function GetHomeDirectory() : string
    {}

    /**
     * Возвращает TRUE, если приложение запущено с правами администратора.
     *
     * @return bool
     */
    public static function AmIRunningAsSuperuser() : bool
    {}

    /**
     * Парсит аргументы на значения с ключами, ключи без значений и значения без ключей
     *
     * @param array<int, string> $args Список аргументов
     * @param string $propertyNameDelimiter Разделитель аргументов (например "-" или "--")
     * @param bool $skipFirstElement Если true, пропускает первый элемент $args. Обычно это всегда путь к исполняемому файлу
     * @return array = [
     *  'arguments' => (array<string, string>) Значения с ключами
     *  'unnamed_values' => (array<int, string>) Значения без ключей
     *  'uninitialized_keys' => (array<int, string>) Ключи без значений
     * ]
     */
    public static function ParseArguments(array $args, string $propertyNameDelimiter, bool $skipFirstElement = true) : array
    {}
    
    /**
     * Возвращает размер окна консоли с ключами "columns" и "rows"
     *
     * @return array{columns: int, rows: int}
     */
    public static function GetWindowSize() : array
    {}
}