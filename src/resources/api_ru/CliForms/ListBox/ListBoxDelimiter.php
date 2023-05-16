<?php

namespace CliForms\ListBox;

use CliForms\MenuBox\MenuBoxControl;
use Data\String\ColoredString;
use Data\String\ForegroundColors;

/**
 * Используется для разделения элементов ListBox
 */
class ListBoxDelimiter extends MenuBoxControl
{
    /**
     * @var bool (всегда FALSE) Элемент некликабельный
     * @property-read
     */
    public bool $Selectable = false;

    /**
     * @var string Символ разделителя
     */
    public string $Character = "=";

    /**
     * @var int Длина разделителя
     */
    public int $Length = 40;

    public string $ItemForegroundColor = ForegroundColors::DARK_GRAY;

    public function __construct(string $name = "", string $hint = "")
    {
        parent::__construct($name, $hint);
    }

    /**
     * @return string Отрендеренный разделитель
     */
    public function Render() : string
    {}
}