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
     * @var bool Is radiobutton checked. If set to TRUE, another selected radiobutton from this group will automatically be set to FALSE
     */
    public bool $Checked = false;

    /**
     * @var string Group name of the radiobutton
     */
    public string $GroupName = "";
}