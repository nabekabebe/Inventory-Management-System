<?php

namespace App\Models;

use App\Traits\ApiFilter;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Variation extends Model
{
    use HasFactory, ApiFilter;
    use HasUuids;
    protected $guarded = [];
    protected $hidden = [];
}
