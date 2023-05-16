<?php

namespace CliForms\MenuBox;

use Data\String\BackgroundColors;
use Data\String\ForegroundColors;
use GetterSetter\GetterSetter;

/**
 * @property bool $Selectable (always FALSE) Element cannot be selected because it's not clickable
 */
class Label extends MenuBoxControl
{
    use GetterSetter;

    public string $ItemForegroundColor = ForegroundColors::WHITE;
    public string $ItemBackgroundColor = BackgroundColors::AUTO;

    protected function _gs_Selectable(): array
    {return [
        Get => function() : bool
        {
            return false;
        },
        Set => function(bool $newValue) : void
        {

        }
    ];}

    /**
     * MenuBox label. Hint is not using here.
     *
     * @param string $name
     * @param string $hint
     */
    public function __construct(string $name, string $hint = "")
    {
        parent::__construct($name, $hint);
    }
}