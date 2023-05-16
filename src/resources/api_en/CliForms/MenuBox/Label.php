<?php

namespace CliForms\MenuBox;

use Data\String\BackgroundColors;
use Data\String\ForegroundColors;

class Label extends MenuBoxControl
{
    /**
     * @var bool (always FALSE) Element cannot be selected because it's not clickable
     * @property-read
     */
    public bool $Selectable = false;

    public string $ItemForegroundColor = ForegroundColors::WHITE;
    public string $ItemBackgroundColor = BackgroundColors::AUTO;

    /**
     * MenuBox label. Hint is not using here.
     *
     * @param string $name
     * @param string $hint
     */
    public function __construct(string $name, string $hint = "")
    {}
}