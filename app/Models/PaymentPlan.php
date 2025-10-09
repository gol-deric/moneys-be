<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class PaymentPlan extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',
        'price',
        'billing_period',
        'features',
        'max_subscriptions',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'features' => 'array',
            'is_active' => 'boolean',
        ];
    }
}
