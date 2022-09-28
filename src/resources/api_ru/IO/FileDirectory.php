<?php

namespace IO;

/**
 * Инструменты для работы с файловой системой
 */

class FileDirectory
{

    /**
     * Копирует файлы и папки в новую директорию
     *
     * @param string $source Файл или папка
     * @param string $target Целевая директория
     */
    public static function Copy(string $source, string $target) : void
    {}

    /**
     * Удаляет файл или папку, даже если она не пустая
     *
     * @param string $path Целевой файл или папка
     * @return bool Возвращает TRUE, если объект успешно удалён. FALSE при возникновении какой-либо ошибки.
     */
    public static function Delete(string $path) : bool
    {}

    /**
     * Устанавливает права доступа к файлу или рекурсивно устанавливает права доступа к папке и всему содержимому (Только для Linux-систем)
     *
     * @param int $mode
     * @param string $target
     * @return void
     */
    public static function RecursiveChmod(int $mode, string $target) : void
    {}

    /**
     * Форматирует путь к папке или файлу, учитывая при этом точки-указатели
     *
     * @param string $path Неформатированный путь (например "/var/www/../log")
     * @return string Пример: "/var/log/"
     */
    public static function FormatDirectoryPath(string $path) : string
    {}
}