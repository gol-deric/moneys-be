<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Purchase extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'product_id',
        'order_id',
        'purchase_token',
        'receipt_data',
        'platform',
        'purchase_type',
        'amount',
        'currency',
        'purchased_at',
        'expires_at',
        'auto_renewing',
        'status',
        'verified_at',
        'cancelled_at',
    ];

    protected $casts = [
        'purchased_at' => 'datetime',
        'expires_at' => 'datetime',
        'verified_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'auto_renewing' => 'boolean',
        'amount' => 'decimal:2',
    ];

    /**
     * Get the user that owns the purchase.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if purchase is active.
     */
    public function isActive(): bool
    {
        if ($this->status !== 'verified') {
            return false;
        }

        if (!$this->expires_at) {
            return true;
        }

        return $this->expires_at->isFuture();
    }

    /**
     * Check if purchase is expired.
     */
    public function isExpired(): bool
    {
        if (!$this->expires_at) {
            return false;
        }

        return $this->expires_at->isPast();
    }

    /**
     * Mark purchase as verified.
     */
    public function markAsVerified(): void
    {
        $this->update([
            'status' => 'verified',
            'verified_at' => now(),
        ]);
    }

    /**
     * Mark purchase as cancelled.
     */
    public function markAsCancelled(): void
    {
        $this->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'auto_renewing' => false,
        ]);
    }

    /**
     * Mark purchase as expired.
     */
    public function markAsExpired(): void
    {
        $this->update([
            'status' => 'expired',
        ]);
    }
}
