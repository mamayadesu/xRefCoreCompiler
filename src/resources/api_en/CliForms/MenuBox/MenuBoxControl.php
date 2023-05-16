<?php

namespace CliForms\MenuBox;

use CliForms\Common\ControlItem;
use CliForms\ListBox\ListBoxControl;
use Data\String\BackgroundColors;
use Data\String\ForegroundColors;

class MenuBoxControl extends ListBoxControl
{
    /**
     * @var string Item's title
     */
    public string $Name = "";

    /**
     * @var string Item's hint. Display when item is selected
     */
    public string $Hint = "";

    /**
     * @var bool Is item visible and allowed to be selected
     */
    public bool $Selectable = true;

    /**
     * @var bool Is item disabled
     */
    public bool $Disabled = false;

    /**
     * @var bool Is item visible
     */
    public bool $Visible = true;

    /**
     * @var int Item's ordering
     */
    public int $Ordering = 1;

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
     * Set style for item
     *
     * @param ForegroundColors $foregroundColor
     * @param BackgroundColors $backgroundColor
     * @return MenuBoxControl
     */
    public function SetItemStyle(string $foregroundColor, $backgroundColor = BackgroundColors::AUTO) : MenuBoxControl
    {}
}