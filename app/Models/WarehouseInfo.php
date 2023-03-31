<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WarehouseInfo extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $guarded = [];
    protected $fillable = ['warehouse_id', 'inventory_id', 'quantity'];

    public function inventories()
    {
        return $this->hasMany(Inventory::class, 'inventory_id', 'id');
    }
    public function warehouses()
    {
        return $this->hasMany(Warehouse::class, 'warehouse_id', 'id');
    }
}
