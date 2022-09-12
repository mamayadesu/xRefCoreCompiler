<?php

namespace CliForms\ListBox;

use CliForms\Common\ControlItem;
use CliForms\Exceptions\InvalidArgumentsPassed;
use \CliForms\Exceptions\InvalidHeaderTypeException;
use CliForms\Exceptions\NoItemsAddedException;
use \Data\String\BackgroundColors;
use \Data\String\ForegroundColors;
use \Data\String\ColoredString;
use \CliForms\Common\RowHeaderType;
use \IO\Console;

/**
 * Создание кастомизированных списков
 */

class ListBox
{
    /**
     * @var string Название списка
     */
    public string $Title = "";
    protected string $titleForegroundColor = ForegroundColors::PURPLE,
        $defaultItemForegroundColor = ForegroundColors::WHITE,
        $defaultDisabledForegroundColor = ForegroundColors::GRAY,
        $defaultItemHeaderForegroundColor = ForegroundColors::GRAY,
        $defaultRowHeaderItemDelimiterForegroundColor = ForegroundColors::DARK_GRAY;

    protected string $titleBackgroundColor = BackgroundColors::AUTO,
        $defaultItemBackgroundColor = BackgroundColors::AUTO,
        $defaultDisabledBackgroundColor = BackgroundColors::AUTO,
        $defaultItemHeaderBackgroundColor = BackgroundColors::AUTO,
        $defaultRowHeaderItemDelimiterBackgroundColor = BackgroundColors::AUTO;

    protected string $rowsHeaderType = RowHeaderType::NUMERIC;
    protected string $rowHeaderItemDelimiter = ". ";

    /**
     * @var array<ListBoxControl> Список всех элементов списка
     */
    protected array/*<ListBoxControl>*/ $items = array();

    /**
     * Создаёт новый контейнер ListBox
     * @param string $title
     */
    public function __construct(string $title)
    {}

    /**
     * Удаляет все элементы
     * @return ListBox
     */
    public function ClearItems() : ListBox
    {}

    /**
     * Задаёт цвет заголовка. Используйте перечисления ForegroundColors и BackgroundColors
     * @param ForegroundColors $foregroundColor
     * @param BackgroundColors $backgroundColor
     * @return ListBox
     */
    public function SetTitleColor(string $foregroundColor, string $backgroundColor = "") : ListBox
    {}

    /**
     * Задаёт тип шапок элементов
     *
     * @param RowHeaderType $headerType
     * @return ListBox
     * @throws InvalidHeaderTypeException
     */
    public function SetRowsHeaderType(string $headerType) : ListBox
    {}

    /**
     * Задаёт разделитель между шапкой и названием элемента (например: ". ")
     *
     * @param string $delimiter
     * @return ListBox
     */
    public function SetRowHeaderItemDelimiter(string $delimiter) : ListBox
    {}

    /**
     * Задаёт стиль разделителя между шапкой и названием элемента
     *
     * @param string $foregroundColor
     * @param string $backgroundColor
     * @return ListBox
     */
    public function SetRowHeaderItemDelimiterStyle(string $foregroundColor, string $backgroundColor) : ListBox
    {}

    /**
     * Добавляет элемент в ListBox
     *
     * @param ListBoxControl $item
     * @return ListBox
     * @throws InvalidArgumentsPassed
     */
    public function AddItem(ControlItem $item) : ListBox
    {}

    /**
     * Генерирует и выводит список
     * @throws NoItemsAddedException
     */
    public function Render() : void
    {}
}