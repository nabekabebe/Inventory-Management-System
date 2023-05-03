<?php

namespace App\Models;

use App\Traits\ApiFilter;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transfer extends Model
{
    use HasFactory, ApiFilter;

    use HasUuids;
    protected $hidden = ['owner_token'];
    protected $guarded = [];
    public function inventories()
    {
        return $this->hasMany(Inventory::class, 'id', 'inventory_id');
    }

    public function from()
    {
        return $this->belongsTo(Warehouse::class, 'source_id', 'id');
    }

    public function to()
    {
        return $this->belongsTo(Warehouse::class, 'destination_id', 'id');
    }
}
