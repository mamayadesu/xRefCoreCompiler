<?php

namespace CliForms\MenuBox;

use Data\String\ColoredString;
use Data\String\ForegroundColors;
use GetterSetter\GetterSetter;

/**
 * Checkbox for MenuBox
 *
 * @property bool $Checked Is checkbox checked
 */
class Checkbox extends MenuBoxItem
{
    use GetterSetter;

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
     * @ignore
     */
    protected function _gs_Checked() : array
    {return [
        Get => function() : bool
        {
            return $this->checkboxChecked;
        },
        Set => function(bool $newValue) : void
        {
            $this->checkboxChecked = $newValue;
            if ($this->GetMenuBox() !== null)
            {
                $this->CallOnSelect($this->GetMenuBox());
                $this->GetMenuBox()->Refresh();
            }
        }
    ];}

    public function Render(bool $selected = false) : string
    {
        $icon = $this->Checked ? $this->Character : $this->NoCharacter;
        $foregroundColor = $this->ItemForegroundColor;
        $backgroundColor = $this->ItemBackgroundColor;
        if ($selected)
        {
            $foregroundColor = $this->ItemSelectedForegroundColor;
            $backgroundColor = $this->ItemSelectedBackgroundColor;
        }

        if ($this->Disabled)
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

        return $icon . ColoredString::Get($this->Name, $foregroundColor, $backgroundColor);
    }
}