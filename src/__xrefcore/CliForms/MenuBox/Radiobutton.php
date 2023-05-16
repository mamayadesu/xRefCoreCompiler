<?php

namespace CliForms\MenuBox;

/**
 * Creates radiobutton for MenuBox.
 * You should use "GroupName" method to group your radiobuttons.
 * All radiobuttons of one group have to have the same group name.
 *
 * @property bool $Checked Is radiobutton checked. If set to TRUE, another selected radiobutton from this group will automatically be set to FALSE
 * @property string $GroupName Group name of the radiobutton
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
     * @ignore
     */
    protected function _gs_Checked(): array
    {return [
        Get => function() : bool
        {
            return $this->radioButtonChecked;
        },
        Set => function(bool $newValue) : void
        {
            $this->radioButtonChecked = $newValue;
            if ($newValue === true && $this->GetMenuBox() !== null)
            {
                foreach ($this->GetMenuBox()->GetSortedItems() as $item)
                {if(!$item instanceof MenuBoxControl)continue;
                    if (!$item instanceof Radiobutton)
                    {
                        continue;
                    }

                    if ($item->GroupName == $this->GroupName && $item !== $this)
                    {
                        $item->Checked = false;
                    }
                }
                $this->GetMenuBox()->Refresh();
            }
        }
    ];}

    /**
     * @ignore
     */
    protected function _gs_GroupName() : array
    {return [
        Get => function() : string
        {
            return $this->groupName;
        },
        Set => function(string $newValue) : void
        {
            $this->groupName = $newValue;
            if ($this->GetMenuBox() !== null)
            {
                $this->CallOnSelect($this->GetMenuBox());
                $this->GetMenuBox()->Refresh();
            }
        }
    ];}
}