<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ProFeature extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',
        'key',
        'description',
        'is_enabled',
        'price',
        'sort_order',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'price' => 'decimal:2',
    ];
}
