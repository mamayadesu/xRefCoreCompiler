<?php

namespace CliForms\Common;

use Data\String\BackgroundColors;
use Data\String\ColoredString;
use Data\String\ForegroundColors;

class ControlItem
{
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
     * Returns item title. If you pass new value, it will be changed
     *
     * @param ?string $newValue
     * @return string
     */
    public function Name(?string $newValue = null) : string
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
     * Returns TRUE if this item is allowed to select. If you pass new value, it will be changed
     *
     * @param bool|null $newValue
     * @return bool
     */
    public function Selectable(?bool $newValue = null) : bool
    {}

    /**
     * Returns TRUE if this item is allowed to be clicked. If you pass new value, it will be changed
     *
     * @param bool|null $newValue
     * @return bool
     */
    public function Disabled(?bool $newValue = null) : bool
    {}

    /**
     * Returns item's sort ordering. If you pass new value, it will be changed
     *
     * @param int|null $newValue
     * @return int
     */
    public function Ordering(?int $newValue = null) : int
    {}

    /**
     * @return string Rendered item
     */
    public function Render() : string
    {}
}