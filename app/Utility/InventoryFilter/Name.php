<?php

namespace App\Utility\InventoryFilter;

use App\Utility\FilterContract;
use App\Utility\QueryFilter;

class Name extends QueryFilter implements FilterContract
{
    public function handle($value)
    {
        $this->query->where('name', $value);
    }
}
