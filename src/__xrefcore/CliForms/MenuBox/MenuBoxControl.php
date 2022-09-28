<?php

namespace CliForms\MenuBox;

use CliForms\Common\ControlItem;
use CliForms\Exceptions\MenuBoxDisposedException;
use CliForms\ListBox\ListBoxControl;
use Data\String\BackgroundColors;
use Data\String\ForegroundColors;

class MenuBoxControl extends ListBoxControl
{
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
     * Returns TRUE if this item is allowed to be clicked. If you pass new value, it will be changed
     *
     * @param bool|null $newValue
     * @return bool
     */
    public function Disabled(?bool $newValue = null) : bool
    {
        $result = parent::Disabled($newValue);
        if ($this->GetMenuBox() !== null && $newValue !== null)
            $this->GetMenuBox()->Refresh();
        return $result;
    }

    /**
     * Returns TRUE if this item is allowed to select and if it's visible. If you pass new value, it will be changed
     *
     * @param bool|null $newValue
     * @return bool
     */
    public function Selectable(?bool $newValue = null) : bool
    {
        if (!$this->Visible())
            return false;
        $result = parent::Selectable($newValue);
        if ($this->GetMenuBox() !== null && $newValue !== null)
        {
            $this->GetMenuBox()->__updateallowedcache = true;
            $this->GetMenuBox()->Refresh();
        }
        return $result;
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

    /**
     * Return item hint. Hint displays right after item name and when item selected. If you pass new value, it will be changed
     *
     * @param string|null $newValue
     * @return string
     */
    public function Hint(?string $newValue = null) : string
    {
        if ($newValue === null)
        {
            return $this->hint;
        }
        $this->hint = $newValue;
        if ($this->GetMenuBox() !== null)
            $this->GetMenuBox()->Refresh();
        return $newValue;
    }

    /**
     * Returns item title. If you pass new value, it will be changed
     *
     * @param ?string $newValue
     * @return string
     */
    public function Name(?string $newValue = null) : string
    {
        $result = parent::Name($newValue);
        if ($this->GetMenuBox() !== null && $newValue !== null)
            $this->GetMenuBox()->Refresh();
        return $result;
    }

    /**
     * Returns TRUE if item is visible. If you pass new value, it will be changed
     *
     * @param bool|null $newValue
     * @return bool
     */
    public function Visible(?bool $newValue = null) : bool
    {
        $menu = $this->GetMenuBox();
        if ($menu !== null && $newValue !== null)
        {
            $menu->Refresh();
            $menu->__updateallowedcache = true;
            $menu->__updatemaxoffsetvaluecache = true;
        }

        if ($newValue !== null)
            $this->visible = $newValue;
        return $this->visible;
    }

    /**
     * Returns item's sort ordering. If you pass new value, it will be changed
     *
     * @param int|null $newValue
     * @return int
     */
    public function Ordering(?int $newValue = null) : int
    {
        $result = parent::Ordering($newValue);
        if ($this->GetMenuBox() !== null && $newValue !== null)
        {
            $this->GetMenuBox()->__updatesortedcache = true;
            $this->GetMenuBox()->Refresh();
        }
        return $result;
    }
}