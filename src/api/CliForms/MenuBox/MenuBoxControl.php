<?php

namespace CliForms\MenuBox;

use CliForms\Common\ControlItem;
use CliForms\ListBox\ListBoxControl;
use Data\String\BackgroundColors;
use Data\String\ForegroundColors;

class MenuBoxControl extends ListBoxControl
{

    /**
     * @var ForegroundColors Hint foreground color
     */
    public string $HintForegroundColor = ForegroundColors::DARK_RED;

    /**
     * @var BackgroundColors Hint background color
     */
    public string $HintBackgroundColor = BackgroundColors::AUTO;

    public function __construct(string $name, string $hint)
    {}

    /**
     * @return MenuBox|null Returns MenuBox that this item is in
     */
    public function GetMenuBox() : ?MenuBox
    {}
    
    /**
     * Removes element from current MenuBox
     * 
     * @return void
     */
    public function Remove() : void
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
     * Returns TRUE if this item is allowed to select and if it's visible. If you pass new value, it will be changed
     *
     * @param bool|null $newValue
     * @return bool
     */
    public function Selectable(?bool $newValue = null) : bool
    {}

    /**
     * Set style for item
     *
     * @param ForegroundColors $foregroundColor
     * @param BackgroundColors $backgroundColor
     * @return MenuBoxControl
     */
    public function SetItemStyle(string $foregroundColor, $backgroundColor = BackgroundColors::AUTO) : MenuBoxControl
    {}

    /**
     * Return item hint. Hint displays right after item name and when item selected. If you pass new value, it will be changed
     *
     * @param string|null $newValue
     * @return string
     */
    public function Hint(?string $newValue = null) : string
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
     * Returns TRUE if item is visible. If you pass new value, it will be changed
     *
     * @param bool|null $newValue
     * @return bool
     */
    public function Visible(?bool $newValue = null) : bool
    {}

    /**
     * Returns item's sort ordering. If you pass new value, it will be changed
     *
     * @param int|null $newValue
     * @return int
     */
    public function Ordering(?int $newValue = null) : int
    {}
}