<?php

namespace CliForms\Common;

use Data\String\BackgroundColors;
use Data\String\ColoredString;
use Data\String\ForegroundColors;

class ControlItem
{
    /**
     * @var string Item's title
     */
    public string $Name = "";

    /**
     * @var bool Is item visible and allowed to be selected
     */
    public bool $Selectable = true;

    /**
     * @var bool Is item disabled
     */
    public bool $Disabled = false;

    /**
     * @var int Item's ordering
     */
    public int $Ordering = 1;

    /**
     * @var string Any value here. ID is not using anywhere except your code
     */
    public string $Id = "";

    /**
     * @var ForegroundColors Text's color
     */
    public string $ItemForegroundColor = ForegroundColors::AUTO;

    /**
     * @var string Text's background color
     */
    public string $ItemBackgroundColor = BackgroundColors::AUTO;

    /**
     * @var ForegroundColors Text's color when item disabled
     */
    public string $DisabledForegroundColor = ForegroundColors::AUTO;

    /**
     * @var BackgroundColors Text's background color when item disabled
     */
    public string $DisabledBackgroundColor = BackgroundColors::AUTO;

    public function __construct(string $name = "")
    {}

    /**
     * Set style for item
     *
     * @param ForegroundColors $foregroundColor
     * @param BackgroundColors $backgroundColor
     * @return ControlItem
     */
    public function SetItemStyle(string $foregroundColor, $backgroundColor = BackgroundColors::AUTO) : ControlItem
    {}

    /**
     * @return string Rendered item
     */
    public function Render() : string
    {}
}