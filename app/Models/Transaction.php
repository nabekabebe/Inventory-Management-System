<?php

namespace App\Models;

use App\Traits\ApiFilter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory, ApiFilter;
    public $timestamps = false;
    protected $guarded = [];
    protected $hidden = ['owner_token', 'inventory_id', 'warehouse_id'];

    public const REFUNDED = 'refunded';
    public const SOLD = 'sold';
    public function sage()
    {
        return $this->quantity * 100;
    }
    public function inventory()
    {
        return $this->hasOne(Inventory::class, 'id', 'inventory_id');
    }
    public function warehouse()
    {
        return $this->hasOne(Warehouse::class, 'id', 'warehouse_id');
    }
    public function committedBy()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
}
