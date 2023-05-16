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
     * @var bool Стоит ли флажок на радио-кнопке. Если установить на элементе значение TRUE, все остальные радио-кнопки данной группы применят значение FALSE
     */
    public bool $Checked = false;

    /**
     * @var string Группа радио-кнопки
     */
    public string $GroupName = "";
}