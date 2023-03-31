<?php

namespace App\Traits;

trait TransactionAuditor
{
    public function recordTransaction()
    {
    }
}
// tId Type[inventory, warehouse, category, transfer] time comment
//eg. 1     inventory   today   item_sold
//eg  2     warehouse   today
// TODO: implement system wide logging service
