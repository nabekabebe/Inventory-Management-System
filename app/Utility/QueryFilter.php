<?php

namespace App\Utility;

abstract class QueryFilter
{
    protected $query;
    public function __construct($query)
    {
        $this->query = $query;
    }
}
