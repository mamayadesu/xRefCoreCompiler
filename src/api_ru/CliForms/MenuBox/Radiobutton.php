<?php

namespace CliForms\MenuBox;

/**
 * Радио-кнопки для MenuBox
 * Вы должны установить название группы радио-кнопки через метод "GroupName".
 * Все радио-кнопки одной группы должны иметь одинаковое название группы
 */
class Radiobutton extends Checkbox
{
    /**
     * Возвращает название группы радио-кнопок. Если задать новое значение, оно будет изменено.
     *
     * @param string|null $newValue
     * @return string
     */
    public function GroupName(?string $newValue = null) : string
    {}

    /**
     * Возвращает TRUE, если текущая радио-кнопка нажата. Если задать новое значение, оно будет изменено.
     * Если вы установите TRUE, данный параметр автоматически применит FALSE для всех остальных элементов группы.
     *
     * @param bool|null $newValue
     * @return bool
     */
    public function Checked(?bool $newValue = null) : bool
    {}
}