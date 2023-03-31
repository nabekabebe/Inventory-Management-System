<?php

namespace App\Models;

use App\Traits\ApiFilter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    use HasFactory, ApiFilter;

    //    use HasUuids;
    public $timestamps = false;
    protected $guarded = [];

    protected $hidden = ['owner_token'];
    public function inventories()
    {
        return $this->hasMany(WarehouseInfo::class, 'warehouse_id');
    }
}
