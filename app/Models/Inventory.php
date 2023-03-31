<?php

namespace App\Models;

use App\Traits\ApiFilter;
use App\Utility\FilterBuilder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    use HasFactory, ApiFilter;

    //    use HasUuids;

    public $timestamps = false;

    protected $guarded = [];
    protected $hidden = ['owner_token'];

    //    protected $appends = ['low_on_stock_count'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    //get total quantity by adding the each at all warehouses
    public function stores()
    {
        return $this->hasMany(WarehouseInfo::class, 'inventory_id', 'id');
    }

    public function warehouses()
    {
        return $this->hasManyThrough(
            Warehouse::class,
            WarehouseInfo::class,
            'warehouse_id',
            'id'
        );
    }
    public function scopeFilterBy($query, $filters)
    {
        $namespace = 'App\Utility\Inventory';
        $filter = new FilterBuilder($query, $filters, $namespace);

        return $filter->apply();
    }
}
