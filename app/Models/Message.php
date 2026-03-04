<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Message extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'conversation_id',
        'sender_id',
        'visible_to',
        'content',
        'type',
        'media_path',
        'media_thumbnail',
        'original_filename',
        'media_size',
        'read_at',
        'delivered_at',
        'notified_at',
        'deleted_for',
        'deleted_by_sender',
    ];

    protected $dates = ['read_at', 'delivered_at', 'notified_at', 'created_at', 'updated_at', 'deleted_at'];

    protected $casts = [
        'deleted_for' => 'array',
        'deleted_by_sender' => 'boolean',
    ];

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function getIsMineAttribute()
    {
        return $this->sender_id === auth()->id();
    }

    public function markAsRead()
    {
        if (!$this->read_at) {
            $this->update([
                'read_at' => now(),
            ]);
        }
    }

    public static function markConversationAsRead($conversationId, $userId)
    {
        return static::where('conversation_id', $conversationId)
            ->where('messages.sender_id', '!=', $userId)
            ->whereNull('read_at')
            ->update([
                'read_at' => now(),
            ]);
    }
}
