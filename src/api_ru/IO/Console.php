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
}