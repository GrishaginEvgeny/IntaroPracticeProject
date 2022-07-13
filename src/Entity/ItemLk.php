<?php

namespace App\Entity;

class ItemLk
{
    public $name;
    public $size;
    public $color;
    public $quantity;
    public $summ;
    public $picture;

    function __construct()
    {
        $this->size = "-";
        $this->color = "-";
    }
}