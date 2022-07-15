<?php

namespace CliForms\MenuBox;

/**
 * Creates radiobutton for MenuBox.
 * You should use "GroupName" method to group your radiobuttons.
 * All radiobuttons of one group have to have the same group name.
 */
class Radiobutton extends Checkbox
{
    /**
     * @ignore
     */
    protected string $LeftBorder = "(", $Character = "O", $NoCharacter = " ", $RightBorder = ") ";

    /**
     * @ignore
     */
    private string $groupName = "";

    /**
     * @ignore
     */
    private bool $radioButtonChecked = false;

    /**
     * Returns radio button group name. If you pass new value, it will be changed.
     *
     * @param string|null $newValue
     * @return string
     */
    public function GroupName(?string $newValue = null) : string
    {
        if ($newValue === null)
        {
            return $this->groupName;
        }
        $this->groupName = $newValue;
        if ($this->GetMenuBox() !== null)
        {
            $this->CallOnSelect($this->GetMenuBox());
            $this->GetMenuBox()->Refresh();
        }
        return $newValue;
    }

    /**
     * Returns TRUE if radiobutton is checked. If you pass new value, it will be changed.
     * If you set checked, other radio buttons will be unchecked.
     *
     * @param bool|null $newValue
     * @return bool
     */
    public function Checked(?bool $newValue = null) : bool
    {
        if ($newValue === null)
        {
            return $this->radioButtonChecked;
        }
        $this->radioButtonChecked = $newValue;
        if ($newValue === true && $this->GetMenuBox() !== null)
        {
            foreach ($this->GetMenuBox()->GetSortedItems() as $item)
            {if(!$item instanceof MenuBoxControl)continue;
                if (!$item instanceof Radiobutton)
                {
                    continue;
                }

                if ($item->GroupName() == $this->GroupName() && $item !== $this)
                {
                    $item->Checked(false);
                }
            }
            $this->GetMenuBox()->Refresh();
        }
        return $newValue;
    }
}