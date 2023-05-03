<?php

namespace App\Models;

use App\Traits\ApiFilter;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    use HasFactory, ApiFilter;

    use HasUuids;
    protected $guarded = [];

    protected $hidden = ['owner_token', 'laravel_through_key'];

    public function records()
    {
        return $this->hasMany(WarehouseInfo::class, 'warehouse_id', 'id');
    }
    public function inventory()
    {
        return $this->hasManyThrough(
            Inventory::class,
            WarehouseInfo::class,
            'warehouse_id',
            'id',
            'id',
            'inventory_id'
        );
    }
}
