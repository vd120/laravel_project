<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventReaction extends Model
{
    protected $fillable = ['user_id', 'event_id', 'reaction_type'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}
