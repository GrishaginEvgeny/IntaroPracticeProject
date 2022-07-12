<?php

namespace App\Entity;

class OrderLk
{
    public $number;
    public $date;
    public $status;
    public $typePayment;
    public $summ;
    public $items = [];

    function __construct()
    {
        $this->typePayment = "не оплачено";
        $this->status = "не оплачено";
    }
}