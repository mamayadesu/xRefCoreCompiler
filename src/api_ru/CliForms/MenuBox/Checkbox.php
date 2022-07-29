<?php

namespace CliForms\MenuBox;

use Data\String\ColoredString;
use Data\String\ForegroundColors;

/**
 * Чекбокс для MenuBox
 */

class Checkbox extends MenuBoxItem
{
    public string $IconForegroundColor = ForegroundColors::BLUE, $DisabledForegroundColor = ForegroundColors::DARK_GRAY;

    /**
     * Возвращает TRUE, если данный чекбокс выбран. Если задать новое значение, оно будет изменено
     *
     * @param bool|null $newValue
     * @return bool
     */
    public function Checked(?bool $newValue = null) : bool
    {}

    public function Render(bool $selected = false) : string
    {}
}