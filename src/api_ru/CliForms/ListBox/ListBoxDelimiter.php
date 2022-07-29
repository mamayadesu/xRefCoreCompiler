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
    public string $ItemForegroundColor = ForegroundColors::DARK_GRAY;

    public function __construct(string $name = "", string $hint = "")
    {
        parent::__construct($name, $hint);
    }

    /**
     * Этот элемент в принципе никогда не может быть выбран и всегда возвращает FALSE
     *
     * @param bool|null $newValue
     * @return bool
     */
    public function Selectable(?bool $newValue = false) : bool
    {}

    /**
     * Возвращает символ разделителя. Если задать новое значение, оно будет изменено
     *
     * @param string|null $newValue
     * @return string
     */
    public function Character(?string $newValue = null) : string
    {}

    /**
     * Возвращает длину разделителя. Если задать новое значение, оно будет изменено
     *
     * @param int|null $newValue
     * @return int
     */
    public function Length(?int $newValue = null) : int
    {}

    /**
     * @return string Отрендеренный разделитель
     */
    public function Render() : string
    {}
}