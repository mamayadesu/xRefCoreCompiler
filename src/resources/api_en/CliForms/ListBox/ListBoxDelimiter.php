<?php

namespace CliForms\ListBox;

use CliForms\MenuBox\MenuBoxControl;
use Data\String\ColoredString;
use Data\String\ForegroundColors;

/**
 * Using to delimit items of ListBox
 */
class ListBoxDelimiter extends MenuBoxControl
{
    /**
     * @var bool (always FALSE) Element cannot be selected because it's not clickable
     * @property-read
     */
    public bool $Selectable = false;

    /**
     * @var string Character of the delimiter
     */
    public string $Character = "=";

    /**
     * @var int Length of the delimiter
     */
    public int $Length = 40;

    public string $ItemForegroundColor = ForegroundColors::DARK_GRAY;

    public function __construct(string $name = "", string $hint = "")
    {
        parent::__construct($name, $hint);
    }

    /**
     * @return string Rendered delimiter
     */
    public function Render() : string
    {}
}