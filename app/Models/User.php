<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasUuids, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'email',
        'password',
        'full_name',
        'avatar_url',
        'is_guest',
        'fcm_token',
        'locale',
        'currency_code',
        'theme',
        'notifications_enabled',
        'email_notifications',
        'subscription_tier',
        'subscription_expires_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_guest' => 'boolean',
            'notifications_enabled' => 'boolean',
            'email_notifications' => 'boolean',
            'subscription_expires_at' => 'datetime',
        ];
    }

    /**
     * Get all subscriptions for the user.
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * Get active (non-cancelled) subscriptions for the user.
     */
    public function activeSubscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class)->where('is_cancelled', false);
    }

    /**
     * Get all notifications for the user.
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Get the user's name for Filament.
     * This maps full_name to name for compatibility.
     */
    public function getNameAttribute(): ?string
    {
        return $this->full_name;
    }

    /**
     * Get the user's name for Filament (alternative method).
     */
    public function getFilamentName(): string
    {
        return $this->full_name ?? $this->email ?? 'User';
    }
}
