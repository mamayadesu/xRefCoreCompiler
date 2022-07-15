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
     * @ignore
     */
    private string $name = "";

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

    /**
     * @ignore
     */
    public int $ordering = 1;

    /**
     * @ignore
     */
    private bool $itemSelectable = true, $itemDisabled = false;

    public function __construct(string $name = "")
    {
        $this->Name($name);
    }

    /**
     * Returns item title. If you pass new value, it will be changed
     *
     * @param ?string $newValue
     * @return string
     */
    public function Name(?string $newValue = null) : string
    {
        if ($newValue === null)
        {
            return $this->name;
        }
        $this->name = $newValue;
        return $newValue;
    }

    /**
     * Set style for item
     *
     * @param ForegroundColors $foregroundColor
     * @param BackgroundColors $backgroundColor
     * @return ControlItem
     */
    public function SetItemStyle(string $foregroundColor, $backgroundColor = BackgroundColors::AUTO) : ControlItem
    {
        $this->ItemForegroundColor = $foregroundColor;
        if ($backgroundColor != BackgroundColors::AUTO)
        {
            $this->ItemBackgroundColor = $backgroundColor;
        }
        return $this;
    }

    /**
     * Returns TRUE if this item is allowed to select. If you pass new value, it will be changed
     *
     * @param bool|null $newValue
     * @return bool
     */
    public function Selectable(?bool $newValue = null) : bool
    {
        if ($newValue === null)
        {
            return $this->itemSelectable;
        }

        $this->itemSelectable = $newValue;
        return $newValue;
    }

    /**
     * Returns TRUE if this item is allowed to be clicked. If you pass new value, it will be changed
     *
     * @param bool|null $newValue
     * @return bool
     */
    public function Disabled(?bool $newValue = null) : bool
    {
        if ($newValue === null)
        {
            return $this->itemDisabled;
        }

        $this->itemDisabled = $newValue;
        return $newValue;
    }

    /**
     * Returns item's sort ordering. If you pass new value, it will be changed
     *
     * @param int|null $newValue
     * @return int
     */
    public function Ordering(?int $newValue = null) : int
    {
        if ($newValue === null)
        {
            return $this->ordering;
        }

        $this->ordering = $newValue;
        return $newValue;
    }

    /**
     * @return string Rendered item
     */
    public function Render() : string
    {
        if ($this->Disabled())
        {
            $foregroundColor = $this->DisabledForegroundColor;
            $backgroundColor = $this->DisabledBackgroundColor;
        }
        else
        {
            $foregroundColor = $this->ItemForegroundColor;
            $backgroundColor = $this->ItemBackgroundColor;
        }
        return ColoredString::Get($this->Name(), $foregroundColor, $backgroundColor);
    }
}