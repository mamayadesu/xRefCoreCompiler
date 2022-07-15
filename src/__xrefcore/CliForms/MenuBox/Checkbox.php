<?php

namespace CliForms\MenuBox;

use Data\String\ColoredString;
use Data\String\ForegroundColors;

/**
 * Checkbox for MenuBox
 */

class Checkbox extends MenuBoxItem
{
    /**
     * @ignore
     */
    private bool $checkboxChecked = false;

    public string $IconForegroundColor = ForegroundColors::BLUE, $DisabledForegroundColor = ForegroundColors::DARK_GRAY;

    /**
     * @ignore
     */
    protected string $LeftBorder = "[", $Character = "X", $NoCharacter = " ", $RightBorder = "] ";

    /**
     * Returns TRUE if checkbox is checked. If you pass new value, it will be changed
     *
     * @param bool|null $newValue
     * @return bool
     */
    public function Checked(?bool $newValue = null) : bool
    {
        if ($newValue === null)
        {
            return $this->checkboxChecked;
        }
        $this->checkboxChecked = $newValue;
        if ($this->GetMenuBox() !== null)
        {
            $this->CallOnSelect($this->GetMenuBox());
            $this->GetMenuBox()->Refresh();
        }
        return $newValue;
    }

    public function Render(bool $selected = false) : string
    {
        $icon = $this->Checked() ? $this->Character : $this->NoCharacter;
        $foregroundColor = $this->ItemForegroundColor;
        $backgroundColor = $this->ItemBackgroundColor;
        if ($selected)
        {
            $foregroundColor = $this->ItemSelectedForegroundColor;
            $backgroundColor = $this->ItemSelectedBackgroundColor;
        }

        if ($this->Disabled())
        {
            if ($selected)
            {
                $foregroundColor = $this->ItemSelectedDisabledForegroundColor;
                $backgroundColor = $this->ItemSelectedDisabledBackgroundColor;
            }
            else
            {
                $foregroundColor = $this->ItemDisabledForegroundColor;
                $backgroundColor = $this->ItemDisabledBackgroundColor;
            }
            $icon = ColoredString::Get($this->LeftBorder, ForegroundColors::DARK_GRAY, $backgroundColor) . ColoredString::Get($icon, $this->DisabledForegroundColor, $backgroundColor) . ColoredString::Get($this->RightBorder, ForegroundColors::DARK_GRAY, $backgroundColor);
        }
        else
            $icon = ColoredString::Get($this->LeftBorder, ForegroundColors::GRAY, $backgroundColor) . ColoredString::Get($icon, $this->IconForegroundColor, $backgroundColor) . ColoredString::Get($this->RightBorder, ForegroundColors::GRAY, $backgroundColor);

        return $icon . ColoredString::Get($this->Name(), $foregroundColor, $backgroundColor);
    }
}