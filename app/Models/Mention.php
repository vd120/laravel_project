<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Mention extends Model
{
    protected $fillable = [
        'mentioner_id',
        'mentioned_id',
        'mentionable_type',
        'mentionable_id'
    ];

    /**
     * Get the user who made the mention.
     */
    public function mentioner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mentioner_id');
    }

    /**
     * Get the user who was mentioned.
     */
    public function mentioned(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mentioned_id');
    }

    /**
     * Get the parent model (Post or Comment) that contains the mention.
     */
    public function mentionable(): MorphTo
    {
        return $this->morphTo();
    }
}
