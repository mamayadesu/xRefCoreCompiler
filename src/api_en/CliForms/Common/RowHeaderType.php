<?php

namespace CliForms\Common;

use Data\Enum;

/**
 * Types of item headers
 */

class RowHeaderType extends Enum
{
    public const NONE = "none";
    public const NUMERIC = "numeric";
    public const STARS = "stars";
    public const DOT1 = "dot1"; // •
    public const DOT2 = "dot2"; // ○
    public const ARROW1 = "arrow1"; // >
    public const ARROW2 = "arrow2"; // ->
    public const ARROW3 = "arrow3"; // →
}