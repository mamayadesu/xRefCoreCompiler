<?php

namespace CliForms\MenuBox;

use Data\String\ColoredString;
use Data\String\ForegroundColors;

/**
 * Checkbox for MenuBox
 */

class Checkbox extends MenuBoxItem
{
    public string $IconForegroundColor = ForegroundColors::BLUE, $DisabledForegroundColor = ForegroundColors::DARK_GRAY;

    /**
     * Returns TRUE if checkbox is checked. If you pass new value, it will be changed
     *
     * @param bool|null $newValue
     * @return bool
     */
    public function Checked(?bool $newValue = null) : bool
    {}

    public function Render(bool $selected = false) : string
    {}
}