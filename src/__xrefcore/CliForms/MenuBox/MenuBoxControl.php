<?php

namespace CliForms\MenuBox;

use CliForms\Common\ControlItem;
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
        return $this->attachedTo;
    }

    /**
     * @ignore
     */
    public function __setattached(?MenuBox $menu) : void
    {
        $this->attachedTo = $menu;
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
            $this->GetMenuBox()->Refresh();
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
        if ($this->GetMenuBox() !== null && $newValue !== null)
            $this->GetMenuBox()->Refresh();

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
            $this->GetMenuBox()->Refresh();
        return $result;
    }
}