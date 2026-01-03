<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostMedia extends Model
{
    protected $fillable = [
        'post_id',
        'media_type',
        'media_path',
        'media_thumbnail',
        'sort_order'
    ];

    protected $casts = [
        'sort_order' => 'integer'
    ];

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }
}
