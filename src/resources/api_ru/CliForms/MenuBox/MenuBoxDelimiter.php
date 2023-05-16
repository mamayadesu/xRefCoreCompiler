<?php

namespace CliForms\MenuBox;

use CliForms\ListBox\ListBoxDelimiter;

/**
 * Используется для разделения элементов MenuBox
 */
class MenuBoxDelimiter extends ListBoxDelimiter
{
    /**
     * @var bool (всегда FALSE) Элемент некликабельный
     * @property-read
     */
    public bool $Selectable = false;

    public function __construct(string $name = "", string $hint = "")
    {}
}