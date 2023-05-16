<?php

namespace CliForms\MenuBox;

use Data\String\BackgroundColors;
use Data\String\ForegroundColors;

class Label extends MenuBoxControl
{
    /**
     * @var bool (всегда FALSE) Элемент некликабельный
     * @property-read
     */
    public bool $Selectable = false;

    public string $ItemForegroundColor = ForegroundColors::WHITE;
    public string $ItemBackgroundColor = BackgroundColors::AUTO;

    /**
     * Лэйбл для MenuBox. Подсказки здесь не используются.
     *
     * @param string $name
     * @param string $hint
     */
    public function __construct(string $name, string $hint = "")
    {}
}