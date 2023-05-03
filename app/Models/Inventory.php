<?php

namespace App\Models;

use App\Traits\ApiFilter;
use App\Utility\FilterBuilder;
use CloudinaryLabs\CloudinaryLaravel\MediaAlly;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    use HasFactory, ApiFilter, HasUuids;
    //    use MediaAlly;

    protected $guarded = [];
    protected $hidden = ['owner_token', 'laravel_through_key'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function variation()
    {
        return $this->hasMany(Variation::class, 'inventory_id', 'id');
    }
    public function records()
    {
        return $this->hasMany(WarehouseInfo::class, 'inventory_id', 'id');
    }
    public function scopeFilterBy($query, $filters)
    {
        $namespace = 'App\Utility\Inventory';
        $filter = new FilterBuilder($query, $filters, $namespace);

        return $filter->apply();
    }
    public function warehouse()
    {
        return $this->hasManyThrough(
            Warehouse::class,
            WarehouseInfo::class,
            'inventory_id',
            'id',
            'id',
            'warehouse_id'
        );
    }
}
