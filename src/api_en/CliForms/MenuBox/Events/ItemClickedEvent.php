<?php

namespace CliForms\MenuBox\Events;

use CliForms\MenuBox\MenuBoxControl;

class ItemClickedEvent extends MenuBoxEvent
{
    public int $ItemNumber;

    public MenuBoxControl $Item;
}