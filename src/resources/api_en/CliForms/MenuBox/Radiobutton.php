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
     * Returns radio button group name. If you pass new value, it will be changed.
     *
     * @param string|null $newValue
     * @return string
     */
    public function GroupName(?string $newValue = null) : string
    {}

    /**
     * Returns TRUE if radiobutton is checked. If you pass new value, it will be changed.
     * If you set checked, other radio buttons will be unchecked.
     *
     * @param bool|null $newValue
     * @return bool
     */
    public function Checked(?bool $newValue = null) : bool
    {}
}