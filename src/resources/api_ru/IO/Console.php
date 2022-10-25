<?php

namespace IO;

use Data\String\BackgroundColors;
use Data\String\ColoredString;
use Data\String\ForegroundColors;
use IO\Console\Exceptions\ReadInterruptedException;

/**
 * Содержит инструменты для ввода/вывода
 */

class Console
{
    /**
     * Прерывает уже вызванные и ещё незавершённые или в будущем вызванные  Console::ReadLine() и Console::ReadKey(). Можно использовать в асинхронных задачах для остановки ожидания ввода
     *
     * @return void
     */
    public static function InterruptRead() : void
    {}

    /**
     * Читает строку из стандартного потока ввода данных после того, как пользователь нажмёт ENTER
     *
     * @param bool $hideInput Скрывает вводимые пользователем символы
     * @param bool $interruptible Может ли этот метод быть прерванным методом Console::InterruptRead()
     * @return string Данные из потока ввода
     * @throws ReadInterruptedException Метод был прерван методом Console::InterruptRead()
     */
    public static function ReadLine(bool $hideInput = false, bool $interruptible = true) : string
    {}

    /**
     * Ожидает, когда пользователь нажмёт какую-либо кнопку на клавиатуре и возвращает название кнопки
     *
     * @param bool $interruptible Может ли этот метод быть прерванным методом Console::InterruptRead()
     * @return string Нажатая клавиша
     * @throws ReadInterruptedException Метод был прерван методом Console::InterruptRead()
     */
    public static function ReadKey(bool $interruptible = true) : string
    {}

    /**
     * Записывает данные в стандартный поток вывода и устанавливает каретку на новую строку
     *
     * @param string $text Выводимый текст
     * @param ForegroundColors $foregroundColor Цвет текста
     * @param BackgroundColors $backgroundColor Цвет фона текста
     */
    public static function WriteLine(string $text, string $foregroundColor = ForegroundColors::AUTO, string $backgroundColor = BackgroundColors::AUTO) : void
    {}

    /**
     * Записывает данные в стандартный поток вывода
     *
     * @param string $text Выводимый текст
     * @param ForegroundColors $foregroundColor Цвет текста
     * @param BackgroundColors $backgroundColor Цвет фона текста
     */
    public static function Write(string $text, string $foregroundColor = ForegroundColors::AUTO, string $backgroundColor = BackgroundColors::AUTO) : void
    {}

    /**
     * Удаляет текст с последней строки
     *
     * @param string $text Заменяет последнюю строку на новый текст
     */
    public static function ClearLine(string $text = "") : void
    {}

    /**
     * Полностью очищает содержимое окна
     *
     * @param string $replacement Новое содержимое
     * @return void
     */
    public static function ClearWindow(string $replacement = "") : void
    {}
    
    /**
     * Перемещает курсор вверх
     *
     * @param int $rows Количество строк для перемещения вверх
     * @return void
     */
    public static function MoveCursorUp(int $rows = 1) : void
    {}

    /**
     * Перемещает курсор вниз
     *
     * @param int $rows Количество строк для перемещения вниз
     * @return void
     */
    public static function MoveCursorDown(int $rows = 1) : void
    {}

    /**
     * Перемещает курсор влево
     *
     * @param int $columns Количество строк для перемещения влево
     * @return void
     */
    public static function MoveCursorLeft(int $columns = 1) : void
    {}

    /**
     * Перемещает курсор вправо
     *
     * @param int $columns Количество строк для перемещения вправо
     * @return void
     */
    public static function MoveCursorRight(int $columns = 1) : void
    {}

    /**
     * Перемещает курсор для перемещения на следующую строку
     *
     * @param int $lines
     * @return void
     */
    public static function MoveCursorToNextLine(int $lines = 1) : void
    {}

    /**
     * Перемещает курсор на предыдущую строку
     *
     * @param int $lines
     * @return void
     */
    public static function MoveCursorToPreviousLine(int $lines = 1) : void
    {}

    /**
     * Скрывает курсор
     *
     * @return void
     */
    public static function HideCursor() : void
    {}

    /**
     * Показывает курсор
     *
     * @return void
     */
    public static function ShowCursor() : void
    {}
}