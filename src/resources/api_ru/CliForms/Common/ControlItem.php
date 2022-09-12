<?php

namespace CliForms\Common;

use Data\String\BackgroundColors;
use Data\String\ColoredString;
use Data\String\ForegroundColors;

class ControlItem
{
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
     * Возвращает название элемента. Если задать новое значение, оно будет изменено
     *
     * @param ?string $newValue
     * @return string
     */
    public function Name(?string $newValue = null) : string
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
     * Возвращает TRUE, если элемент доступен для выбора. Если задать новое значение, оно будет изменено
     *
     * @param bool|null $newValue
     * @return bool
     */
    public function Selectable(?bool $newValue = null) : bool
    {}

    /**
     * Возвращает TRUE, если этот элемент заблокирован. Если задать новое значение, оно будет изменено
     *
     * @param bool|null $newValue
     * @return bool
     */
    public function Disabled(?bool $newValue = null) : bool
    {}

    /**
     * Возвращает порядок сортировки элемента внутри контейнера, в котором он находится. Если задать новое значение, оно будет изменено
     *
     * @param int|null $newValue
     * @return int
     */
    public function Ordering(?int $newValue = null) : int
    {}

    /**
     * @return string Отрендеренный элемент
     */
    public function Render() : string
    {}
}