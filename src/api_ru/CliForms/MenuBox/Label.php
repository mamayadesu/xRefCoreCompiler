<?php

namespace CliForms\MenuBox;

use Data\String\BackgroundColors;
use Data\String\ForegroundColors;

class Label extends MenuBoxControl
{
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

    /**
     * Лэйбл - это просто текст. Он не может быть выбран.
     *
     * @param bool|null $newValue
     * @return bool
     */
    public function Selectable(?bool $newValue = null): bool
    {}
}