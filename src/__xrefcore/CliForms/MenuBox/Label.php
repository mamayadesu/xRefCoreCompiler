<?php

namespace CliForms\MenuBox;

use Data\String\BackgroundColors;
use Data\String\ForegroundColors;

class Label extends MenuBoxControl
{
    public string $ItemForegroundColor = ForegroundColors::WHITE;
    public string $ItemBackgroundColor = BackgroundColors::AUTO;

    /**
     * MenuBox label. Hint is not using here.
     *
     * @param string $name
     * @param string $hint
     */
    public function __construct(string $name, string $hint = "")
    {
        parent::__construct($name, $hint);
    }

    /**
     * Label is just label and cannot be selected
     *
     * @param bool|null $newValue
     * @return bool
     */
    public function Selectable(?bool $newValue = null): bool
    {
        return false;
    }
}