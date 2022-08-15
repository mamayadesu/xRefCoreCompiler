<?php

namespace CliForms\MenuBox\Events;

class OffsetChangedEvent extends MenuBoxEvent
{
    public int $Offset, $PreviousOffset;
}