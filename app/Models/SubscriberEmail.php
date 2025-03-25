<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubscriberEmail extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'email',
        'name',
        'is_active',
        'verified_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'verified_at' => 'datetime',
    ];

    /**
     * Scope a query to only include active subscribers.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include verified subscribers.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeVerified($query)
    {
        return $query->whereNotNull('verified_at');
    }

    /**
     * Check if the subscriber is verified.
     *
     * @return bool
     */
    public function isVerified()
    {
        return $this->verified_at !== null;
    }

    /**
     * Verify the subscriber.
     *
     * @return bool
     */
    public function verify()
    {
        return $this->update(['verified_at' => now()]);
    }

    /**
     * Activate the subscriber.
     *
     * @return bool
     */
    public function activate()
    {
        return $this->update(['is_active' => true]);
    }

    /**
     * Deactivate the subscriber.
     *
     * @return bool
     */
    public function deactivate()
    {
        return $this->update(['is_active' => false]);
    }
}
