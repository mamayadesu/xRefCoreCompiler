<?php

namespace CliForms\MenuBox;

use Data\String\ColoredString;
use Data\String\ForegroundColors;

/**
 * Чекбокс для MenuBox
 */

class Checkbox extends MenuBoxItem
{
    /**
     * @var bool Стоит ли флаг на чекбоксе
     */
    public bool $Checked = false;

    public string $IconForegroundColor = ForegroundColors::BLUE, $DisabledForegroundColor = ForegroundColors::DARK_GRAY;

    public function Render(bool $selected = false) : string
    {}
}