<?php

namespace CliForms\Common;

use Data\String\BackgroundColors;
use Data\String\ColoredString;
use Data\String\ForegroundColors;

class ControlItem
{
    /**
     * @var string Название элемента
     */
    public string $Name = "";

    /**
     * @var bool Элемент виден и доступен для выбора/наводки
     */
    public bool $Selectable = true;

    /**
     * @var bool Элемент отключён
     */
    public bool $Disabled = false;

    /**
     * @var int Сортировка элемента
     */
    public int $Ordering = 1;

    /**
     * @var string Любое значение. ID нигде не используется, кроме вашего кода
     */
    public string $Id = "";

    /**
     * @var ForegroundColors Цвет текста
     */
    public string $ItemForegroundColor = ForegroundColors::AUTO;

    /**
     * @var string Цвет фона текста
     */
    public string $ItemBackgroundColor = BackgroundColors::AUTO;

    /**
     * @var ForegroundColors Цвет текста, когда он заблокирован
     */
    public string $DisabledForegroundColor = ForegroundColors::AUTO;

    /**
     * @var BackgroundColors Цвет фона текста, когда он заблокирован
     */
    public string $DisabledBackgroundColor = BackgroundColors::AUTO;

    public function __construct(string $name = "")
    {}

    /**
     * Задаёт стили для элемента
     *
     * @param ForegroundColors $foregroundColor
     * @param BackgroundColors $backgroundColor
     * @return ControlItem
     */
    public function SetItemStyle(string $foregroundColor, $backgroundColor = BackgroundColors::AUTO) : ControlItem
    {}

    /**
     * @return string Отрендеренный элемент
     */
    public function Render() : string
    {}
}