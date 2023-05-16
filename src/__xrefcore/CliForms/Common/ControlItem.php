<?php

namespace CliForms\Common;

use Data\String\BackgroundColors;
use Data\String\ColoredString;
use Data\String\ForegroundColors;
use GetterSetter\GetterSetter;

/**
 * @property string $Name Item's title
 * @property bool $Selectable Is item visible and allowed to be selected
 * @property bool $Disabled Is item disabled
 * @property int $Ordering Item's ordering
 */
class ControlItem
{
    use GetterSetter;

    /**
     * @var string Any value here. ID is not using anywhere except your code
     */
    public string $Id = "";

    /**
     * @ignore
     */
    protected string $name = "";

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
    protected function _gs_Name() : array
    {return [
        Get => function() : string
        {
            return $this->name;
        },
        Set => function(string $newValue) : void
        {
            $this->name = $newValue;
        }
    ];}

    /**
     * @ignore
     */
    protected function _gs_Selectable() : array
    {return [
        Get => function() : bool
        {
            return $this->itemSelectable;
        },
        Set => function(bool $newValue) : void
        {
            $this->itemSelectable = $newValue;
        }
    ];}

    /**
     * @ignore
     */
    protected function _gs_Disabled() : array
    {return [
        Get => function() : bool
        {
            return $this->itemDisabled;
        },
        Set => function(bool $newValue) : void
        {
            $this->itemDisabled = $newValue;
        }
    ];}

    /**
     * @ignore
     */
    protected function _gs_Ordering() : array
    {return [
        Get => function() : int
        {
            return $this->ordering;
        },
        Set => function(int $newValue) : void
        {
            $this->ordering = $newValue;
        }
    ];}

    /**
     * @ignore
     */
    public int $ordering = 1;

    /**
     * @ignore
     */
    protected bool $itemSelectable = true, $itemDisabled = false;

    public function __construct(string $name = "")
    {
        $this->Name = $name;
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
     * @return string Rendered item
     */
    public function Render() : string
    {
        if ($this->Disabled)
        {
            $foregroundColor = $this->DisabledForegroundColor;
            $backgroundColor = $this->DisabledBackgroundColor;
        }
        else
        {
            $foregroundColor = $this->ItemForegroundColor;
            $backgroundColor = $this->ItemBackgroundColor;
        }
        return ColoredString::Get($this->Name, $foregroundColor, $backgroundColor);
    }
}