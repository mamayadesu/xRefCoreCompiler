<?php

namespace CliForms\ListBox;

use CliForms\MenuBox\MenuBoxControl;
use Data\String\ColoredString;
use Data\String\ForegroundColors;
use GetterSetter\GetterSetter;

/**
 * Using to delimit items of ListBox
 *
 * @property bool $Selectable (always FALSE) Element cannot be selected because it's not clickable
 * @property string $Character Character of the delimiter
 * @property int $Length Length of the delimiter
 */
class ListBoxDelimiter extends MenuBoxControl
{
    use GetterSetter;

    public string $ItemForegroundColor = ForegroundColors::DARK_GRAY;

    /**
     * @ignore
     */
    protected string $delimiterCharacter = "=";

    /**
     * @ignore
     */
    protected function _gs_Selectable() : array
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
     * @ignore
     */
    protected function _gs_Character() : array
    {return [
        Get => function() : string
        {
            return $this->delimiterCharacter;
        },
        Set => function(string $newValue) : void
        {
            $this->delimiterCharacter = $newValue;
        }
    ];}

    /**
     * @ignore
     */
    protected function _gs_Length() : array
    {return [
        Get => function() : int
        {
            return $this->delimiterLength;
        },
        Set => function(int $newValue) : void
        {
            $this->delimiterLength = $newValue;
        }
    ];}

    /**
     * @ignore
     */
    protected int $delimiterLength = 40;

    public function __construct(string $name = "", string $hint = "")
    {
        parent::__construct($name, $hint);
    }

    /**
     * @return string Rendered delimiter
     */
    public function Render() : string
    {
        return ColoredString::Get(str_repeat($this->delimiterCharacter, $this->Length), $this->ItemForegroundColor, $this->ItemBackgroundColor);
    }
}