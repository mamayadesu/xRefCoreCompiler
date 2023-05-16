<?php

namespace CliForms\MenuBox;

use CliForms\Common\ControlItem;
use CliForms\Exceptions\MenuBoxDisposedException;
use CliForms\ListBox\ListBoxControl;
use Data\String\BackgroundColors;
use Data\String\ForegroundColors;
use GetterSetter\GetterSetter;

/**
 * @property string $Name Item's title
 * @property string $Hint Item's hint. Display when item is selected
 * @property bool $Selectable Is item visible and allowed to be selected
 * @property bool $Disabled Is item disabled
 * @property bool $Visible Is item visible
 * @property int $Ordering Item's ordering
 */
class MenuBoxControl extends ListBoxControl
{
    use GetterSetter;

    /**
     * @ignore
     */
    private ?MenuBox $attachedTo = null;

    /**
     * @ignore
     */
    private bool $canBeAttached = true;

    /**
     * @ignore
     */
    private bool $visible = true;

    /**
     * @var ForegroundColors Hint foreground color
     */
    public string $HintForegroundColor = ForegroundColors::DARK_RED;

    /**
     * @var BackgroundColors Hint background color
     */
    public string $HintBackgroundColor = BackgroundColors::AUTO;

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
            if ($this->GetMenuBox() !== null)
                $this->GetMenuBox()->Refresh();
        }
    ];}

    /**
     * ignore
     */
    protected function _gs_Selectable(): array
    {return [
        Get => function() : bool
        {
            if (!$this->Visible)
                return false;

            return $this->itemSelectable;
        },
        Set => function(bool $newValue) : void
        {
            $this->itemSelectable = $newValue;
            if ($this->GetMenuBox() !== null)
            {
                $this->GetMenuBox()->__updateallowedcache = true;
                $this->GetMenuBox()->Refresh();
            }
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
            if ($this->GetMenuBox() !== null)
                $this->GetMenuBox()->Refresh();
        }
    ];}

    /**
     * @ignore
     */
    protected function _gs_Hint() : array
    {return [
        Get => function() : string
        {
            return $this->hint;
        },
        Set => function(string $newValue) : void
        {
            $this->hint = $newValue;
            if ($this->GetMenuBox() !== null)
                $this->GetMenuBox()->Refresh();
        }
    ];}

    /**
     * @ignore
     */
    protected function _gs_Visible() : array
    {return [
        Get => function() : bool
        {
            return $this->visible;
        },
        Set => function(bool $newValue) : void
        {
            $this->visible = $newValue;
            $menu = $this->GetMenuBox();
            if ($menu !== null)
            {
                $menu->__updateallowedcache = true;
                $menu->__updatemaxoffsetvaluecache = true;
                $menu->Refresh();
            }
        }
    ];}

    /**
     * @ignore
     */
    protected function _gs_Ordering(): array
    {return [
        Get => function() : int
        {
            return $this->ordering;
        },
        Set => function(int $newValue) : void
        {
            if ($this->GetMenuBox() !== null)
            {
                $this->GetMenuBox()->__updatesortedcache = true;
                $this->GetMenuBox()->__updateallowedcache = true;
                $this->GetMenuBox()->__updatemaxoffsetvaluecache = true;
                $this->GetMenuBox()->Refresh();
            }
            $this->ordering = $newValue;
        }
    ];}

    /**
     * @ignore
     */
    private string $hint = "";

    public function __construct(string $name, string $hint)
    {
        parent::__construct($name);
        $this->hint = $hint;
    }

    /**
     * @return MenuBox|null Returns MenuBox that this item is in
     */
    public function GetMenuBox() : ?MenuBox
    {
        return $this->__attachedto();
    }

    /**
     * Removes element from current MenuBox
     *
     * @return void
     */
    public function Remove() : void
    {
        $callSelectedChanged = false;
        $menuBox = $this->__attachedto();
        if ($menuBox !== null)
        {
            if ($menuBox->GetSelectedItem() === $this)
            {
                $callSelectedChanged = true;
            }
        }
        if ($menuBox !== null)
        {
            $this->__setattached(null, true);
            $menuBox->RemoveItem($this);
            $menuBox->__checkcurrentitem($callSelectedChanged);
        }
    }

    /**
     * @ignore
     */
    public function __canbeattached() : bool
    {
        return $this->canBeAttached;
    }

    /**
     * @ignore
     */
    public function __setattached(?MenuBox $menu, bool $canbeattached) : void
    {
        $this->attachedTo = $menu;
        $this->canBeAttached = ($menu === null ?: $canbeattached);
    }

    private function __attachedto() : ?MenuBox
    {
        if ($this->attachedTo !== null)
        {
            try
            {
                $attached = $this->attachedTo->HasItem($this);
            }
            catch (MenuBoxDisposedException $e)
            {
                $attached = false;
            }
            if (!$attached)
            {
                $this->attachedTo = null;
                $this->canBeAttached = true;
            }
        }
        return $this->attachedTo;
    }

    /**
     * Set style for item
     *
     * @param ForegroundColors $foregroundColor
     * @param BackgroundColors $backgroundColor
     * @return MenuBoxControl
     */
    public function SetItemStyle(string $foregroundColor, $backgroundColor = BackgroundColors::AUTO) : MenuBoxControl
    {
        $result = parent::SetItemStyle($foregroundColor, $backgroundColor);
        if ($this->GetMenuBox() !== null)
            $this->GetMenuBox()->Refresh();
        return $result;
    }
}