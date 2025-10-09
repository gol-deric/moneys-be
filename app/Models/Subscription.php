<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subscription extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'icon_url',
        'price',
        'currency_code',
        'start_date',
        'billing_cycle_count',
        'billing_cycle_period',
        'category',
        'notes',
        'is_cancelled',
        'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'price' => 'decimal:2',
            'is_cancelled' => 'boolean',
            'cancelled_at' => 'datetime',
        ];
    }

    /**
     * Get the user that owns the subscription.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Calculate the next billing date.
     */
    protected function nextBillingDate(): Attribute
    {
        return Attribute::make(
            get: function () {
                $startDate = Carbon::parse($this->start_date);
                $now = Carbon::now();

                if ($now->lt($startDate)) {
                    return $startDate;
                }

                $interval = match ($this->billing_cycle_period) {
                    'day' => 'days',
                    'month' => 'months',
                    'quarter' => 'months',
                    'year' => 'years',
                    default => 'months',
                };

                $multiplier = $this->billing_cycle_period === 'quarter'
                    ? $this->billing_cycle_count * 3
                    : $this->billing_cycle_count;

                $nextDate = clone $startDate;

                while ($nextDate->lte($now)) {
                    $nextDate->add($multiplier, $interval);
                }

                return $nextDate;
            }
        );
    }

    /**
     * Scope to get subscriptions due in a specific number of days.
     */
    public function scopeDueInDays(Builder $query, int $days): Builder
    {
        $targetDate = Carbon::now()->addDays($days)->toDateString();

        return $query->where('is_cancelled', false)
            ->get()
            ->filter(function ($subscription) use ($targetDate) {
                return $subscription->next_billing_date->toDateString() === $targetDate;
            })
            ->pluck('id')
            ->pipe(function ($ids) use ($query) {
                return $query->whereIn('id', $ids);
            });
    }
}
