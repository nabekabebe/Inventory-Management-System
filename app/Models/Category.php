<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;
    use HasUuids;

    protected $guarded = [];
    protected $hidden = ['owner_token'];
    public function inventories()
    {
        return $this->hasMany(Inventory::class);
    }
}
