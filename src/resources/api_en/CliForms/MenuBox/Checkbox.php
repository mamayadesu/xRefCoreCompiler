<?php

namespace CliForms\MenuBox;

use Data\String\ColoredString;
use Data\String\ForegroundColors;

/**
 * Checkbox for MenuBox
 */

class Checkbox extends MenuBoxItem
{
    /**
     * @var bool Is checkbox checked
     */
    public bool $Checked = false;

    public string $IconForegroundColor = ForegroundColors::BLUE, $DisabledForegroundColor = ForegroundColors::DARK_GRAY;

    public function Render(bool $selected = false) : string
    {}
}